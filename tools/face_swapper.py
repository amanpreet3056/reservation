"""Utilities for swapping faces in images and video frames.

The module relies purely on local processing using MediaPipe and OpenCV to
extract facial landmarks and blend faces.  No external APIs are required or
used.
"""
from __future__ import annotations

from dataclasses import dataclass
from typing import List, Optional, Sequence, Tuple

import cv2
import mediapipe as mp
import numpy as np

Point = Tuple[float, float]
Triangle = Tuple[int, int, int]


@dataclass
class DetectionResult:
    """Stores face landmarks and their convex hull."""

    points: np.ndarray
    hull_indices: np.ndarray

    @property
    def hull_points(self) -> np.ndarray:
        return self.points[self.hull_indices[:, 0]]


class FaceMeshDetector:
    """Wraps MediaPipe's FaceMesh detector to extract 2D landmarks."""

    def __init__(self, static_image_mode: bool = True) -> None:
        self._mesh = mp.solutions.face_mesh.FaceMesh(
            static_image_mode=static_image_mode,
            max_num_faces=1,
            refine_landmarks=True,
            min_detection_confidence=0.5,
            min_tracking_confidence=0.5,
        )

    def detect(self, image: np.ndarray) -> Optional[DetectionResult]:
        """Return landmarks for the most prominent face in ``image``."""

        rgb = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)
        results = self._mesh.process(rgb)
        if not results.multi_face_landmarks:
            return None

        height, width = image.shape[:2]
        # Convert normalized coordinates to pixel coordinates.
        points = np.array(
            [
                (landmark.x * width, landmark.y * height)
                for landmark in results.multi_face_landmarks[0].landmark
            ],
            dtype=np.float32,
        )
        hull_indices = cv2.convexHull(points, returnPoints=False)
        return DetectionResult(points=points, hull_indices=hull_indices)

    def close(self) -> None:
        self._mesh.close()

    def __enter__(self) -> "FaceMeshDetector":
        return self

    def __exit__(self, exc_type, exc, exc_tb) -> None:
        self.close()


def _calculate_delaunay_triangles(rect: Tuple[int, int, int, int], points: np.ndarray) -> List[Triangle]:
    subdiv = cv2.Subdiv2D(rect)
    for point in points:
        subdiv.insert((float(point[0]), float(point[1])))

    triangle_list = subdiv.getTriangleList()
    delaunay: List[Triangle] = []
    point_list = [tuple(point) for point in points]

    def find_index(pt: Point) -> Optional[int]:
        for idx, candidate in enumerate(point_list):
            if np.linalg.norm(np.subtract(candidate, pt)) < 1.0:
                return idx
        return None

    for triangle in triangle_list:
        pts = [
            (triangle[0], triangle[1]),
            (triangle[2], triangle[3]),
            (triangle[4], triangle[5]),
        ]
        if not all(
            rect[0] <= p[0] <= rect[0] + rect[2] and rect[1] <= p[1] <= rect[1] + rect[3]
            for p in pts
        ):
            continue
        indices: List[int] = []
        for pt in pts:
            index = find_index(pt)
            if index is None:
                break
            indices.append(index)
        if len(indices) == 3:
            delaunay.append((indices[0], indices[1], indices[2]))
    return delaunay


def _warp_triangle(
    src: np.ndarray,
    dst: np.ndarray,
    triangle_src: Sequence[Point],
    triangle_dst: Sequence[Point],
) -> None:
    triangle_src = np.float32(triangle_src)
    triangle_dst = np.float32(triangle_dst)

    rect_src = cv2.boundingRect(triangle_src)
    rect_dst = cv2.boundingRect(triangle_dst)

    src_cropped = src[
        rect_src[1] : rect_src[1] + rect_src[3],
        rect_src[0] : rect_src[0] + rect_src[2],
    ]

    triangle_src_rect = triangle_src - np.array([rect_src[0], rect_src[1]])
    triangle_dst_rect = triangle_dst - np.array([rect_dst[0], rect_dst[1]])

    mask = np.zeros((rect_dst[3], rect_dst[2], 3), dtype=np.float32)
    cv2.fillConvexPoly(mask, np.int32(triangle_dst_rect), (1.0, 1.0, 1.0), 16, 0)

    matrix = cv2.getAffineTransform(triangle_src_rect, triangle_dst_rect)
    warped = cv2.warpAffine(
        src_cropped,
        matrix,
        (rect_dst[2], rect_dst[3]),
        None,
        flags=cv2.INTER_LINEAR,
        borderMode=cv2.BORDER_REFLECT_101,
    )

    dst_slice = dst[
        rect_dst[1] : rect_dst[1] + rect_dst[3],
        rect_dst[0] : rect_dst[0] + rect_dst[2],
    ]
    dst_slice *= 1.0 - mask
    dst_slice += warped * mask


def _blend_faces(
    warped_src: np.ndarray,
    dst_image: np.ndarray,
    mask: np.ndarray,
    hull_points: np.ndarray,
) -> np.ndarray:
    center = tuple(np.mean(hull_points, axis=0).astype(np.int32))
    mask_uint8 = mask.astype(np.uint8)
    output = cv2.seamlessClone(
        np.uint8(warped_src), dst_image, mask_uint8, center, cv2.NORMAL_CLONE
    )
    return output


def swap_face(
    source_image: np.ndarray,
    target_image: np.ndarray,
    detector: Optional[FaceMeshDetector] = None,
    source_detection: Optional[DetectionResult] = None,
    target_detection: Optional[DetectionResult] = None,
) -> np.ndarray:
    """Swap the main face from ``source_image`` onto ``target_image``."""

    needs_detector = source_detection is None or target_detection is None
    owns_detector = detector is None and needs_detector
    if detector is None and needs_detector:
        detector = FaceMeshDetector(static_image_mode=True)

    try:
        if source_detection is None:
            assert detector is not None
            source_detection = detector.detect(source_image)
        if target_detection is None:
            assert detector is not None
            target_detection = detector.detect(target_image)
        if source_detection is None or target_detection is None:
            raise ValueError("Could not detect faces in both images.")

        points_src = source_detection.points
        points_dst = target_detection.points
        hull_indices = target_detection.hull_indices

        hull_src = points_src[hull_indices[:, 0]]
        hull_dst = points_dst[hull_indices[:, 0]]

        rect = cv2.boundingRect(hull_dst)
        triangles = _calculate_delaunay_triangles(rect, hull_dst)
        if not triangles:
            raise ValueError("Failed to build Delaunay triangulation for face hull.")

        warped_src = np.zeros_like(target_image, dtype=np.float32)
        for tri in triangles:
            t_src = hull_src[list(tri)]
            t_dst = hull_dst[list(tri)]
            _warp_triangle(source_image, warped_src, t_src, t_dst)

        mask = np.zeros(target_image.shape[:2], dtype=np.uint8)
        cv2.fillConvexPoly(mask, np.int32(hull_dst), 255)
        mask = cv2.merge([mask, mask, mask])

        output = _blend_faces(warped_src, target_image, mask, hull_dst)
        return output
    finally:
        if owns_detector and detector is not None:
            detector.close()


def swap_face_onto_frame(
    source_image: np.ndarray,
    frame: np.ndarray,
    detector: FaceMeshDetector,
    source_detection: Optional[DetectionResult] = None,
) -> np.ndarray:
    """Swap ``source_image`` face onto the provided ``frame``."""

    try:
        return swap_face(
            source_image,
            frame,
            detector=detector,
            source_detection=source_detection,
        )
    except ValueError:
        # If detection fails for a frame we gracefully return the original frame.
        return frame
