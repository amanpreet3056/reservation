"""High-level helpers for swapping faces onto entire video files."""
from __future__ import annotations

import shutil
import subprocess
from dataclasses import dataclass
from pathlib import Path
from typing import Callable, Optional, Tuple

import cv2
import numpy as np

from .face_swapper import DetectionResult, FaceMeshDetector, swap_face_onto_frame

ProgressCallback = Callable[[int, Optional[int]], None]


@dataclass
class VideoSwapSummary:
    """Summary metadata for a processed video."""

    frame_count: int
    width: int
    height: int
    fps: float
    scale_factor: float
    audio_copied: bool


def load_image(path: Path) -> np.ndarray:
    """Load an image from ``path`` raising a ``ValueError`` if it fails."""

    image = cv2.imread(str(path))
    if image is None:
        raise ValueError(f"Unable to read image: {path}")
    return image


def _determine_scale(width: int, height: int, max_dimension: Optional[int]) -> Tuple[float, int, int]:
    if max_dimension is None:
        return 1.0, width, height

    max_dim = max(width, height)
    if max_dim <= max_dimension:
        return 1.0, width, height

    scale = max_dimension / float(max_dim)
    scaled_width = max(2, int(round(width * scale)))
    scaled_height = max(2, int(round(height * scale)))

    # Ensure dimensions are even for codecs such as H.264.
    if scaled_width % 2 == 1:
        scaled_width += 1
    if scaled_height % 2 == 1:
        scaled_height += 1

    return scale, scaled_width, scaled_height


def _prepare_video_writer(
    output_path: Path, fps: float, width: int, height: int
) -> cv2.VideoWriter:
    fourcc = cv2.VideoWriter_fourcc(*"mp4v")
    output_path.parent.mkdir(parents=True, exist_ok=True)
    writer = cv2.VideoWriter(str(output_path), fourcc, fps, (width, height))
    if not writer.isOpened():
        raise RuntimeError(f"Failed to create video writer at {output_path}")
    return writer


def _merge_audio(
    original_video: Path, processed_video: Path, output_path: Path
) -> bool:
    """Attempt to copy audio from ``original_video`` into ``processed_video``."""

    if shutil.which("ffmpeg") is None:
        return False

    temp_output = output_path.with_suffix(".tmp.mp4")
    command = [
        "ffmpeg",
        "-y",
        "-i",
        str(processed_video),
        "-i",
        str(original_video),
        "-map",
        "0:v:0",
        "-map",
        "1:a:0?",
        "-c:v",
        "copy",
        "-c:a",
        "aac",
        "-shortest",
        str(temp_output),
    ]

    try:
        subprocess.run(command, check=True, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
    except (subprocess.CalledProcessError, FileNotFoundError):
        if temp_output.exists():
            temp_output.unlink()  # pragma: no cover - cleanup best effort
        return False

    shutil.move(str(temp_output), str(output_path))
    return True


def swap_video(
    source_image: np.ndarray,
    target_video_path: Path,
    output_path: Path,
    *,
    max_dimension: Optional[int] = None,
    preserve_audio: bool = False,
    progress_callback: Optional[ProgressCallback] = None,
) -> VideoSwapSummary:
    """Swap the dominant face from ``source_image`` onto ``target_video_path``."""

    cap = cv2.VideoCapture(str(target_video_path))
    if not cap.isOpened():
        raise ValueError(f"Unable to open video: {target_video_path}")

    writer: Optional[cv2.VideoWriter] = None

    try:
        fps = cap.get(cv2.CAP_PROP_FPS) or 24.0
        width = int(cap.get(cv2.CAP_PROP_FRAME_WIDTH))
        height = int(cap.get(cv2.CAP_PROP_FRAME_HEIGHT))
        total_frames_raw = cap.get(cv2.CAP_PROP_FRAME_COUNT)
        total_frames = int(total_frames_raw) if total_frames_raw > 0 else None

        scale_factor, scaled_width, scaled_height = _determine_scale(
            width, height, max_dimension
        )

        if preserve_audio:
            video_only_path = output_path.with_name(output_path.stem + ".__video__.mp4")
        else:
            video_only_path = output_path

        writer = _prepare_video_writer(video_only_path, fps, scaled_width, scaled_height)

        with FaceMeshDetector(static_image_mode=True) as source_detector:
            source_detection: Optional[DetectionResult] = source_detector.detect(
                source_image
            )
            if source_detection is None:
                raise ValueError("Could not detect a face in the source image.")

        frames_processed = 0

        with FaceMeshDetector(static_image_mode=False) as frame_detector:
            while True:
                ret, frame = cap.read()
                if not ret:
                    break

                if scale_factor != 1.0:
                    frame = cv2.resize(
                        frame,
                        (scaled_width, scaled_height),
                        interpolation=cv2.INTER_AREA,
                    )

                swapped = swap_face_onto_frame(
                    source_image,
                    frame,
                    detector=frame_detector,
                    source_detection=source_detection,
                )
                if writer is None:
                    raise RuntimeError("Video writer was not initialised")
                writer.write(swapped)

                frames_processed += 1
                if progress_callback is not None:
                    progress_callback(frames_processed, total_frames)

        if progress_callback is not None:
            progress_callback(frames_processed, total_frames)

        audio_copied = False
        if preserve_audio:
            audio_copied = _merge_audio(target_video_path, video_only_path, output_path)
            if audio_copied:
                if video_only_path.exists():
                    video_only_path.unlink()
            else:
                if output_path.exists():
                    output_path.unlink()
                if video_only_path.exists():
                    shutil.move(str(video_only_path), str(output_path))

        return VideoSwapSummary(
            frame_count=frames_processed,
            width=scaled_width,
            height=scaled_height,
            fps=fps,
            scale_factor=scale_factor,
            audio_copied=audio_copied,
        )
    finally:
        if writer is not None:
            writer.release()
        cap.release()


def swap_video_from_paths(
    source_image_path: Path,
    target_video_path: Path,
    output_path: Path,
    *,
    max_dimension: Optional[int] = None,
    preserve_audio: bool = False,
    progress_callback: Optional[ProgressCallback] = None,
) -> VideoSwapSummary:
    """Wrapper around :func:`swap_video` that reads the source image from disk."""

    source_image = load_image(source_image_path)
    return swap_video(
        source_image,
        target_video_path,
        output_path,
        max_dimension=max_dimension,
        preserve_audio=preserve_audio,
        progress_callback=progress_callback,
    )


__all__ = [
    "ProgressCallback",
    "VideoSwapSummary",
    "load_image",
    "swap_video",
    "swap_video_from_paths",
]
