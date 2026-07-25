<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration{
 public function up():void{
  Schema::create('podcast_series',function(Blueprint $t){$t->id();$t->string('title');$t->string('slug')->unique();$t->text('description')->nullable();$t->string('cover_path')->nullable();$t->string('author')->nullable();$t->string('language',10)->default('pt-BR');$t->boolean('is_published')->default(false)->index();$t->timestamps();});
  Schema::create('podcast_episodes',function(Blueprint $t){$t->id();$t->foreignId('podcast_series_id')->constrained('podcast_series')->cascadeOnDelete();$t->string('title');$t->string('slug');$t->text('description')->nullable();$t->string('audio_path',2048);$t->string('audio_mime',80)->default('audio/mpeg');$t->unsignedBigInteger('audio_bytes')->nullable();$t->unsignedInteger('duration_seconds')->nullable();$t->unsignedInteger('episode_number')->nullable();$t->boolean('is_published')->default(false)->index();$t->timestamp('published_at')->nullable()->index();$t->timestamps();$t->unique(['podcast_series_id','slug']);});
 }
 public function down():void{Schema::dropIfExists('podcast_episodes');Schema::dropIfExists('podcast_series');}
};
