<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration{
 public function up():void{
  Schema::create('video_categories',function(Blueprint $t){$t->id();$t->string('name');$t->string('slug')->unique();$t->timestamps();});
  Schema::create('video_series',function(Blueprint $t){$t->id();$t->foreignId('video_category_id')->nullable()->constrained()->nullOnDelete();$t->string('title');$t->string('slug')->unique();$t->text('description')->nullable();$t->string('thumbnail_path')->nullable();$t->boolean('is_published')->default(false)->index();$t->timestamps();});
  Schema::create('videos',function(Blueprint $t){$t->id();$t->foreignId('video_category_id')->nullable()->constrained()->nullOnDelete();$t->foreignId('video_series_id')->nullable()->constrained()->nullOnDelete();$t->string('title');$t->string('slug')->unique();$t->text('description')->nullable();$t->string('provider')->default('file');$t->string('video_path',2048);$t->string('thumbnail_path')->nullable();$t->string('subtitle_path')->nullable();$t->string('subtitle_language',10)->default('pt-BR');$t->unsignedInteger('duration_seconds')->nullable();$t->unsignedInteger('episode_number')->nullable();$t->boolean('is_published')->default(false)->index();$t->timestamp('published_at')->nullable()->index();$t->timestamps();});
  Schema::create('video_playlists',function(Blueprint $t){$t->id();$t->string('title');$t->string('slug')->unique();$t->text('description')->nullable();$t->boolean('is_published')->default(false)->index();$t->timestamps();});
  Schema::create('video_playlist_items',function(Blueprint $t){$t->foreignId('video_playlist_id')->constrained()->cascadeOnDelete();$t->foreignId('video_id')->constrained()->cascadeOnDelete();$t->unsignedInteger('position')->default(0);$t->primary(['video_playlist_id','video_id']);});
 }
 public function down():void{Schema::dropIfExists('video_playlist_items');Schema::dropIfExists('video_playlists');Schema::dropIfExists('videos');Schema::dropIfExists('video_series');Schema::dropIfExists('video_categories');}
};
