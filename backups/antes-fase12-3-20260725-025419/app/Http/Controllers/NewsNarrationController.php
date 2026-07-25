<?php
namespace App\Http\Controllers;use App\Models\NewsArticle;use App\Models\NewsNarration;use Illuminate\Http\RedirectResponse;use Illuminate\Support\Facades\Storage;
class NewsNarrationController extends Controller{public function show(NewsArticle $news):RedirectResponse{$n=NewsNarration::where('news_article_id',$news->id)->where('status','approved')->latest('reviewed_at')->firstOrFail();return redirect()->away(Storage::disk('public')->url($n->audio_path));}}
