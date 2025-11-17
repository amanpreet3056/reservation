"""FastAPI service that exposes the video face swapper over HTTP."""
from __future__ import annotations

import asyncio
import os
import threading
import time
import uuid
from concurrent.futures import ThreadPoolExecutor
from dataclasses import dataclass, field
from pathlib import Path
from typing import Dict, Optional

from fastapi import FastAPI, File, Form, HTTPException, UploadFile
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import FileResponse
from pydantic import BaseModel

from .video_face_swap import ProgressCallback, swap_video_from_paths


def _default_storage_root() -> Path:
    return Path(__file__).resolve().parent.parent / "public" / "face_swaps"


STORAGE_ROOT = Path(os.getenv("FACE_SWAP_STORAGE", _default_storage_root()))
STORAGE_ROOT.mkdir(parents=True, exist_ok=True)

PUBLIC_URL = os.getenv("FACE_SWAP_PUBLIC_URL")
MAX_WORKERS = int(os.getenv("FACE_SWAP_WORKERS", "1"))

ALLOWED_IMAGE_TYPES = {"image/jpeg", "image/png"}
ALLOWED_VIDEO_TYPES = {
    "video/mp4",
    "video/quicktime",
    "video/x-matroska",
    "video/x-msvideo",
}


@dataclass
class JobRecord:
    job_id: str
    status: str = "queued"
    message: str = "Job queued"
    created_at: float = field(default_factory=time.time)
    updated_at: float = field(default_factory=time.time)
    processed_frames: int = 0
    total_frames: Optional[int] = None
    output_path: Optional[Path] = None


class JobResponse(BaseModel):
    job_id: str
    status: str
    message: str
    processed_frames: int
    total_frames: Optional[int]
    created_at: float
    updated_at: float
    output_url: Optional[str] = None


class HealthResponse(BaseModel):
    status: str
    storage_root: str
    worker_count: int


def _relative_url(path: Path) -> Optional[str]:
    if not PUBLIC_URL:
        return None
    try:
        rel = path.relative_to(STORAGE_ROOT)
    except ValueError:
        return None
    return f"{PUBLIC_URL.rstrip('/')}/{rel.as_posix()}"


class JobStore:
    def __init__(self) -> None:
        self._jobs: Dict[str, JobRecord] = {}
        self._lock = threading.Lock()

    def create(self, job_id: str) -> JobRecord:
        record = JobRecord(job_id=job_id)
        with self._lock:
            self._jobs[job_id] = record
        return record

    def update(self, job_id: str, **kwargs) -> JobRecord:
        with self._lock:
            record = self._jobs[job_id]
            for key, value in kwargs.items():
                setattr(record, key, value)
            record.updated_at = time.time()
            return record

    def get(self, job_id: str) -> JobRecord:
        with self._lock:
            if job_id not in self._jobs:
                raise KeyError(job_id)
            return self._jobs[job_id]


store = JobStore()
executor = ThreadPoolExecutor(max_workers=MAX_WORKERS)

app = FastAPI(title="Face Swap Service", version="1.0.0")
app.add_middleware(
    CORSMiddleware,
    allow_origins=os.getenv("FACE_SWAP_CORS", "*").split(","),
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)


async def _save_upload(upload: UploadFile, destination: Path) -> None:
    destination.parent.mkdir(parents=True, exist_ok=True)
    with destination.open("wb") as fh:
        while True:
            chunk = await upload.read(1024 * 1024)
            if not chunk:
                break
            fh.write(chunk)
    await upload.close()


def _job_directory(job_id: str) -> Path:
    job_dir = STORAGE_ROOT / job_id
    job_dir.mkdir(parents=True, exist_ok=True)
    return job_dir


def _progress_updater(job_id: str) -> ProgressCallback:
    last_update = time.time()

    def _callback(processed: int, total: Optional[int]) -> None:
        nonlocal last_update
        now = time.time()
        if processed == 0:
            return
        if processed == total or now - last_update >= 1:
            store.update(job_id, processed_frames=processed, total_frames=total)
            last_update = now

    return _callback


def _process_job(
    job_id: str,
    source_path: Path,
    video_path: Path,
    output_path: Path,
    *,
    max_dimension: Optional[int],
    preserve_audio: bool,
) -> None:
    store.update(job_id, status="processing", message="Processing video")

    try:
        summary = swap_video_from_paths(
            source_path,
            video_path,
            output_path,
            max_dimension=max_dimension,
            preserve_audio=preserve_audio,
            progress_callback=_progress_updater(job_id),
        )
    except Exception as exc:  # pragma: no cover - surfaces errors to API
        store.update(job_id, status="failed", message=str(exc))
        return

    message = (
        f"Processed {summary.frame_count} frames at {summary.fps:.2f} fps"
        f" ({summary.width}x{summary.height})."
    )
    if summary.scale_factor != 1.0:
        message += f" Frames were downscaled by {summary.scale_factor:.2f}x."
    if preserve_audio and not summary.audio_copied:
        message += " Audio track could not be copied (ffmpeg missing?)."

    store.update(
        job_id,
        status="completed",
        message=message,
        processed_frames=summary.frame_count,
        total_frames=summary.frame_count,
        output_path=output_path,
    )


def _validate_content_type(upload: UploadFile, allowed: set[str], field: str) -> None:
    if upload.content_type not in allowed:
        raise HTTPException(
            status_code=415,
            detail=f"Unsupported {field} type: {upload.content_type}",
        )


@app.get("/health", response_model=HealthResponse)
async def health() -> HealthResponse:
    return HealthResponse(
        status="ok",
        storage_root=str(STORAGE_ROOT),
        worker_count=MAX_WORKERS,
    )


@app.post("/swap", response_model=JobResponse, status_code=202)
async def create_job(
    source_image: UploadFile = File(...),
    target_video: UploadFile = File(...),
    max_dimension: Optional[int] = Form(default=None),
    preserve_audio: bool = Form(default=True),
) -> JobResponse:
    _validate_content_type(source_image, ALLOWED_IMAGE_TYPES, "image")
    _validate_content_type(target_video, ALLOWED_VIDEO_TYPES, "video")

    job_id = uuid.uuid4().hex
    record = store.create(job_id)

    job_dir = _job_directory(job_id)
    source_ext = Path(source_image.filename or "source.jpg").suffix or ".jpg"
    video_ext = Path(target_video.filename or "target.mp4").suffix or ".mp4"

    source_path = job_dir / f"source{source_ext}"
    video_path = job_dir / f"target{video_ext}"
    output_path = job_dir / "swapped.mp4"

    await asyncio.gather(
        _save_upload(source_image, source_path),
        _save_upload(target_video, video_path),
    )

    def _worker() -> None:
        _process_job(
            job_id,
            source_path,
            video_path,
            output_path,
            max_dimension=max_dimension,
            preserve_audio=preserve_audio,
        )

    executor.submit(_worker)

    return JobResponse(
        job_id=record.job_id,
        status=record.status,
        message=record.message,
        processed_frames=record.processed_frames,
        total_frames=record.total_frames,
        created_at=record.created_at,
        updated_at=record.updated_at,
    )


@app.get("/jobs/{job_id}", response_model=JobResponse)
async def job_status(job_id: str) -> JobResponse:
    try:
        record = store.get(job_id)
    except KeyError as exc:
        raise HTTPException(status_code=404, detail="Job not found") from exc

    return JobResponse(
        job_id=record.job_id,
        status=record.status,
        message=record.message,
        processed_frames=record.processed_frames,
        total_frames=record.total_frames,
        created_at=record.created_at,
        updated_at=record.updated_at,
        output_url=_relative_url(record.output_path) if record.output_path else None,
    )


@app.get("/jobs/{job_id}/result")
async def job_result(job_id: str) -> FileResponse:
    try:
        record = store.get(job_id)
    except KeyError as exc:
        raise HTTPException(status_code=404, detail="Job not found") from exc

    if record.status != "completed" or not record.output_path:
        raise HTTPException(status_code=409, detail="Job not completed")

    if not record.output_path.exists():
        raise HTTPException(status_code=404, detail="Output missing on disk")

    return FileResponse(
        path=record.output_path,
        filename=record.output_path.name,
        media_type="video/mp4",
    )
