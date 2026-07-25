<?php
namespace App\Http\Controllers;use App\Models\PodcastSeries;use Illuminate\Http\Response;use Illuminate\View\View;
class PodcastController extends Controller{
 public function index():View{return view('podcasts.index',['seriesList'=>PodcastSeries::where('is_published',true)->with('publishedEpisodes')->latest()->get()]);}
 public function feed(PodcastSeries $series):Response{abort_unless($series->is_published,404);$series->load('publishedEpisodes');return response()->view('podcasts.feed',['series'=>$series])->header('Content-Type','application/rss+xml; charset=UTF-8');}
}
