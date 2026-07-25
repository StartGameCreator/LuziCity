<?php
return ['ffmpeg_binary'=>env('FFMPEG_BINARY','ffmpeg'),'video_render_queue'=>env('VIDEO_RENDER_QUEUE','video-render'),'max_clip_seconds'=>(int)env('VIDEO_MAX_CLIP_SECONDS',180)];
