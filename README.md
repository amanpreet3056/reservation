# Face Swap Toolkit

This repository now includes a pure local toolchain for swapping faces between
images and videos without relying on any third-party web APIs.

## Requirements

Install the Python dependencies (Python 3.9+ recommended):

```bash
pip install -r requirements.txt
```

## Usage

All scripts live under the `tools/` directory. The entry point is
`swap_faces.py`, which can be executed either directly or with `python -m`.

### Swap Between Images

```bash
python tools/swap_faces.py image path/to/source.jpg path/to/target.jpg output.jpg
```

The script detects the dominant face in each image, warps the source face to
match the target geometry, and blends it seamlessly.

### Swap Onto a Video

```bash
python tools/swap_faces.py video path/to/source.jpg path/to/target.mp4 swapped.mp4 \
  --max-dimension 1920 --preserve-audio
```

The source face is detected once and then overlaid on each frame of the target
video. Frames where no face is detected are left unchanged to avoid artifacts.
The optional flags shown above keep frames within a 1920px bounding box (helpful
for UHD footage) and copy the input audio track via `ffmpeg` when available.

Progress is logged every 100 frames by default; use
`--progress-interval <N>` to change the cadence.

### Run as an API Service

The new `tools/face_swap_service.py` module exposes the video swapping pipeline
over HTTP via FastAPI. Start it locally with:

```bash
uvicorn tools.face_swap_service:app --host 0.0.0.0 --port 8000
```

Submit jobs by POSTing to `/swap` with `multipart/form-data` including
`source_image`, `target_video`, and optional `max_dimension` / `preserve_audio`
fields. Poll `/jobs/{job_id}` for status updates and download the resulting
video from `/jobs/{job_id}/result` when the job completes. Outputs are stored
under `public/face_swaps/` by default so they can be served statically if you
expose that directory via Nginx.

## Implementation Notes

* MediaPipe's FaceMesh is used for landmark detection entirely offline.
* Delaunay triangulation plus affine warping is used to align the source face
  with the destination.
* OpenCV's `seamlessClone` performs Poisson blending for natural results.

The core warping implementation is contained in `tools/face_swapper.py` while
`tools/video_face_swap.py` and `tools/face_swap_service.py` provide video-level
orchestration and an HTTP API ready for integration.

## VPS Deployment (victor.websitedevelopment.cloud)

To run the service on your VPS and expose it at
`https://victor.websitedevelopment.cloud`:

1. **Install system packages** (Ubuntu example):

   ```bash
   sudo apt update
   sudo apt install python3-venv ffmpeg nginx
   ```

2. **Create an isolated environment and install dependencies**:

   ```bash
   python3 -m venv ~/faceswap-env
   source ~/faceswap-env/bin/activate
   pip install --upgrade pip
   pip install -r /var/www/victor/reservation/requirements.txt
   ```

3. **Launch the API** (adjust worker count as needed for CPU cores):

   ```bash
   export FACE_SWAP_STORAGE=/var/www/victor/reservation/public/face_swaps
   export FACE_SWAP_PUBLIC_URL="https://victor.websitedevelopment.cloud/face_swaps"
   export FACE_SWAP_WORKERS=2
   uvicorn tools.face_swap_service:app --host 0.0.0.0 --port 8000 --workers 1
   ```

   For a permanent service create a `systemd` unit pointing at the command
   above.

4. **Expose the API through Nginx** by adding a server block similar to:

   ```nginx
   server {
       server_name victor.websitedevelopment.cloud;

       location /face_swaps/ {
           alias /var/www/victor/reservation/public/face_swaps/;
           autoindex off;
       }

       location / {
           proxy_pass http://127.0.0.1:8000;
           proxy_set_header Host $host;
           proxy_set_header X-Forwarded-Proto $scheme;
           proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
       }
   }
   ```

Reload Nginx after updating the configuration and the `/swap` endpoint will be
available at your domain. All processing occurs locally on the VPS—no third-
party APIs are involved.
