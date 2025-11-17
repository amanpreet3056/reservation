"""Command-line interface for performing face swaps on images and videos."""
from __future__ import annotations

import argparse
from pathlib import Path
from typing import Optional
import sys

import cv2

if __package__ in (None, ""):
    sys.path.append(str(Path(__file__).resolve().parent))
    from face_swapper import swap_face  # type: ignore
    from video_face_swap import (  # type: ignore
        ProgressCallback,
        VideoSwapSummary,
        load_image,
        swap_video_from_paths,
    )
else:
    from .face_swapper import swap_face
    from .video_face_swap import (
        ProgressCallback,
        VideoSwapSummary,
        load_image,
        swap_video_from_paths,
    )


def swap_images(source: Path, target: Path, output: Path) -> None:
    source_img = load_image(source)
    target_img = load_image(target)

    result = swap_face(source_img, target_img)
    output.parent.mkdir(parents=True, exist_ok=True)
    cv2.imwrite(str(output), result)


def _progress_logger(interval: int) -> ProgressCallback:
    def _callback(processed: int, total: Optional[int]) -> None:
        if processed == 0:
            return
        if interval <= 0 or processed % interval == 0 or (total is not None and processed == total):
            if total:
                percent = processed / total * 100
                print(
                    f"Processed {processed}/{total} frames ({percent:.1f}%).",
                    flush=True,
                )
            else:
                print(f"Processed {processed} frames.", flush=True)

    return _callback


def swap_video_cli(
    source: Path,
    target_video: Path,
    output: Path,
    *,
    max_dimension: Optional[int],
    preserve_audio: bool,
    progress_interval: int,
) -> VideoSwapSummary:
    callback = _progress_logger(progress_interval)
    summary = swap_video_from_paths(
        source,
        target_video,
        output,
        max_dimension=max_dimension,
        preserve_audio=preserve_audio,
        progress_callback=callback,
    )
    print(
        "\nFinished processing:"
        f" {summary.frame_count} frames at {summary.fps:.2f} fps -> {output}",
        flush=True,
    )
    if summary.scale_factor != 1.0:
        print(
            f"Frames were downscaled by {summary.scale_factor:.2f}x to"
            f" {summary.width}x{summary.height} for processing.",
            flush=True,
        )
    if preserve_audio:
        if summary.audio_copied:
            print("Audio track copied from source video via ffmpeg.", flush=True)
        else:
            print(
                "Warning: ffmpeg not available, output video contains no audio.",
                flush=True,
            )
    return summary


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description=__doc__)
    subparsers = parser.add_subparsers(dest="mode", required=True)

    parser_image = subparsers.add_parser("image", help="Swap faces between two images")
    parser_image.add_argument("source", type=Path, help="Path to the source image")
    parser_image.add_argument("target", type=Path, help="Path to the target image")
    parser_image.add_argument("output", type=Path, help="Path to store the result image")

    parser_video = subparsers.add_parser(
        "video", help="Overlay the source face on each frame of the target video"
    )
    parser_video.add_argument("source", type=Path, help="Path to the source image")
    parser_video.add_argument("target", type=Path, help="Path to the target video")
    parser_video.add_argument("output", type=Path, help="Path to store the processed video")
    parser_video.add_argument(
        "--max-dimension",
        type=int,
        default=None,
        help=(
            "If provided, frames are downscaled so their longest edge does not"
            " exceed this size before processing."
        ),
    )
    parser_video.add_argument(
        "--preserve-audio",
        action="store_true",
        help="Attempt to copy the audio track using ffmpeg if it is installed.",
    )
    parser_video.add_argument(
        "--progress-interval",
        type=int,
        default=100,
        help="Emit a progress log every N frames (default: 100).",
    )

    return parser.parse_args()


def main() -> None:
    args = parse_args()
    if args.mode == "image":
        swap_images(args.source, args.target, args.output)
    else:
        swap_video_cli(
            args.source,
            args.target,
            args.output,
            max_dimension=args.max_dimension,
            preserve_audio=args.preserve_audio,
            progress_interval=args.progress_interval,
        )


if __name__ == "__main__":
    main()
