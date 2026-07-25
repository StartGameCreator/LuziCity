<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\Relations\BelongsTo;
class NewsEditorialReview extends Model{protected $fillable=['news_article_id','user_id','action','from_status','to_status','note'];public function article():BelongsTo{return $this->belongsTo(NewsArticle::class,'news_article_id');}public function user():BelongsTo{return $this->belongsTo(User::class);}}
