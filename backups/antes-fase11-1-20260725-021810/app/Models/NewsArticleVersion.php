<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\Relations\BelongsTo;
class NewsArticleVersion extends Model{protected $fillable=['news_article_id','created_by','version','title','subtitle','excerpt','body','metadata'];protected function casts():array{return['metadata'=>'array'];}public function article():BelongsTo{return $this->belongsTo(NewsArticle::class,'news_article_id');}public function creator():BelongsTo{return $this->belongsTo(User::class,'created_by');}}
