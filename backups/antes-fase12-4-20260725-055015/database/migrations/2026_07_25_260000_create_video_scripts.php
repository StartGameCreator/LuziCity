<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration{
 public function up():void{Schema::create('video_scripts',function(Blueprint $t){$t->id();$t->foreignId('news_article_id')->constrained('news_articles')->cascadeOnDelete();$t->string('title');$t->string('status')->default('pending_review')->index();$t->string('provider')->default('local');$t->string('model')->nullable();$t->unsignedInteger('target_duration_seconds')->default(90);$t->unsignedInteger('estimated_duration_seconds')->default(0);$t->json('scenes');$t->longText('teleprompter_text');$t->text('editorial_notes')->nullable();$t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();$t->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();$t->timestamp('reviewed_at')->nullable();$t->timestamps();});}
 public function down():void{Schema::dropIfExists('video_scripts');}
};
