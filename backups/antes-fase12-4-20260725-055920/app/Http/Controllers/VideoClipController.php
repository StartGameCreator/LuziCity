<?php
namespace App\Http\Controllers;use App\Models\VideoClip;use Illuminate\Http\RedirectResponse;use Illuminate\Support\Facades\Storage;
class VideoClipController extends Controller{public function show(VideoClip $clip):RedirectResponse{abort_unless($clip->status==='approved'&&$clip->output_path,404);return redirect()->away(Storage::disk('public')->url($clip->output_path));}}
