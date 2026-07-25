<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration{
 public function up():void{
  Schema::create('audio_voice_profiles',function(Blueprint $t){$t->id();$t->string('name');$t->string('provider')->default('openai');$t->string('voice')->default('alloy');$t->string('model')->default('gpt-4o-mini-tts');$t->string('format',10)->default('mp3');$t->decimal('cost_per_million_chars',10,4)->default(15);$t->boolean('is_active')->default(true)->index();$t->timestamps();});
  Schema::create('news_narrations',function(Blueprint $t){$t->id();$t->foreignId('news_article_id')->constrained('news_articles')->cascadeOnDelete();$t->foreignId('audio_voice_profile_id')->constrained()->restrictOnDelete();$t->string('status')->default('queued')->index();$t->text('input_text');$t->unsignedInteger('character_count')->default(0);$t->string('audio_path')->nullable();$t->unsignedBigInteger('audio_bytes')->nullable();$t->decimal('estimated_cost',12,6)->default(0);$t->decimal('actual_cost',12,6)->nullable();$t->text('error_message')->nullable();$t->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();$t->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();$t->timestamp('generated_at')->nullable();$t->timestamp('reviewed_at')->nullable();$t->timestamps();});
 }
 public function down():void{Schema::dropIfExists('news_narrations');Schema::dropIfExists('audio_voice_profiles');}
};
