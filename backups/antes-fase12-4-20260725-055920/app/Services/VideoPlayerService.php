<?php
namespace App\Services;use App\Models\Video;
class VideoPlayerService{public function embedUrl(Video $video):?string{$url=$video->publicUrl();if($video->provider==='youtube'&&preg_match('~(?:youtu\\.be/|youtube\\.com/(?:watch\\?v=|live/|embed/))([A-Za-z0-9_-]{6,})~',$url,$m))return 'https://www.youtube-nocookie.com/embed/'.$m[1];if($video->provider==='vimeo'&&preg_match('~vimeo\\.com/(?:video/)?(\\d+)~',$url,$m))return 'https://player.vimeo.com/video/'.$m[1];return null;}}
