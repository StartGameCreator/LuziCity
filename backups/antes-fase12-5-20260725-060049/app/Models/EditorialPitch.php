<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\Relations\BelongsTo;use Illuminate\Database\Eloquent\Relations\HasMany;
class EditorialPitch extends Model{
 public const STATUSES=['idea'=>'Ideia','research'=>'Em pesquisa','writing'=>'Em redação','review'=>'Em revisão','approved'=>'Aprovada','scheduled'=>'Agendada','published'=>'Publicada','discarded'=>'Descartada'];
 protected $fillable=['title','summary','status','priority','category_id','assignee_id','created_by','news_article_id','due_at','position'];
 protected function casts():array{return['due_at'=>'datetime'];}
 public function category():BelongsTo{return $this->belongsTo(Category::class);} public function assignee():BelongsTo{return $this->belongsTo(User::class,'assignee_id');}
 public function creator():BelongsTo{return $this->belongsTo(User::class,'created_by');} public function article():BelongsTo{return $this->belongsTo(NewsArticle::class,'news_article_id');}
 public function sources():HasMany{return $this->hasMany(EditorialPitchSource::class)->orderBy('id');} public function tasks():HasMany{return $this->hasMany(EditorialPitchTask::class)->orderBy('position');}
 public function comments():HasMany{return $this->hasMany(EditorialPitchComment::class)->latest();}
 public function agentRuns():HasMany{return $this->hasMany(AiAgentRun::class)->latest();}
}
