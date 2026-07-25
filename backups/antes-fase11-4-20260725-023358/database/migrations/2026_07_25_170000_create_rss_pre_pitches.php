<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration{
 public function up():void{Schema::create('rss_pre_pitches',function(Blueprint $t){$t->id();$t->foreignId('rss_imported_article_id')->constrained('rss_imported_articles')->cascadeOnDelete();$t->string('status')->default('pending_review')->index();$t->string('title',180);$t->text('summary');$t->json('source_links');$t->json('questions');$t->json('risks');$t->text('local_relevance')->nullable();$t->text('editorial_recommendation')->nullable();$t->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();$t->timestamp('generated_at');$t->timestamps();$t->unique('rss_imported_article_id');});}
 public function down():void{Schema::dropIfExists('rss_pre_pitches');}
};
