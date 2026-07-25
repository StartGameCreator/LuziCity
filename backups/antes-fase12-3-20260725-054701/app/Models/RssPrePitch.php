<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\Relations\BelongsTo;
class RssPrePitch extends Model{
 protected $fillable=['rss_imported_article_id','status','title','summary','source_links','questions','risks','local_relevance','editorial_recommendation','generated_by','generated_at'];
 protected function casts():array{return['source_links'=>'array','questions'=>'array','risks'=>'array','generated_at'=>'datetime'];}
 public function article():BelongsTo{return $this->belongsTo(RssImportedArticle::class,'rss_imported_article_id');}
 public function generator():BelongsTo{return $this->belongsTo(User::class,'generated_by');}
}
