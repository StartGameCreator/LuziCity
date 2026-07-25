<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration{
 public function up():void{
  if(Schema::hasTable('news_articles'))Schema::table('news_articles',function(Blueprint $t){if(!Schema::hasColumn('news_articles','workflow_status'))$t->string('workflow_status',40)->default('draft')->index();if(!Schema::hasColumn('news_articles','approved_by'))$t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();if(!Schema::hasColumn('news_articles','approved_at'))$t->timestamp('approved_at')->nullable();if(!Schema::hasColumn('news_articles','scheduled_for'))$t->timestamp('scheduled_for')->nullable()->index();});
  if(!Schema::hasTable('news_article_versions'))Schema::create('news_article_versions',function(Blueprint $t){$t->id();$t->foreignId('news_article_id')->constrained()->cascadeOnDelete();$t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();$t->unsignedInteger('version');$t->string('title',180);$t->string('subtitle',240)->nullable();$t->text('excerpt')->nullable();$t->longText('body');$t->json('metadata')->nullable();$t->timestamps();$t->unique(['news_article_id','version']);});
  if(!Schema::hasTable('news_editorial_reviews'))Schema::create('news_editorial_reviews',function(Blueprint $t){$t->id();$t->foreignId('news_article_id')->constrained()->cascadeOnDelete();$t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();$t->string('action',40)->index();$t->string('from_status',40);$t->string('to_status',40);$t->text('note')->nullable();$t->timestamps();});
 }
 public function down():void{Schema::dropIfExists('news_editorial_reviews');Schema::dropIfExists('news_article_versions');}
};
