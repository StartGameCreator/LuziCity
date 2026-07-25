<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration{
 public function up():void{
  if(!Schema::hasTable('editorial_pitches'))Schema::create('editorial_pitches',function(Blueprint $t){$t->id();$t->string('title',180);$t->text('summary')->nullable();$t->string('status',30)->default('idea')->index();$t->string('priority',20)->default('normal')->index();$t->foreignId('category_id')->nullable()->constrained()->nullOnDelete();$t->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();$t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();$t->foreignId('news_article_id')->nullable()->constrained('news_articles')->nullOnDelete();$t->dateTime('due_at')->nullable()->index();$t->unsignedInteger('position')->default(0);$t->timestamps();});
  if(!Schema::hasTable('editorial_pitch_sources'))Schema::create('editorial_pitch_sources',function(Blueprint $t){$t->id();$t->foreignId('editorial_pitch_id')->constrained()->cascadeOnDelete();$t->string('title',180)->nullable();$t->string('url',2048)->nullable();$t->text('notes')->nullable();$t->timestamps();});
  if(!Schema::hasTable('editorial_pitch_tasks'))Schema::create('editorial_pitch_tasks',function(Blueprint $t){$t->id();$t->foreignId('editorial_pitch_id')->constrained()->cascadeOnDelete();$t->string('description',240);$t->boolean('is_completed')->default(false);$t->unsignedInteger('position')->default(0);$t->timestamps();});
  if(!Schema::hasTable('editorial_pitch_comments'))Schema::create('editorial_pitch_comments',function(Blueprint $t){$t->id();$t->foreignId('editorial_pitch_id')->constrained()->cascadeOnDelete();$t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();$t->text('body');$t->timestamps();});
 }
 public function down():void{Schema::dropIfExists('editorial_pitch_comments');Schema::dropIfExists('editorial_pitch_tasks');Schema::dropIfExists('editorial_pitch_sources');Schema::dropIfExists('editorial_pitches');}
};
