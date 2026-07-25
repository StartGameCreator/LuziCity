<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration{
 public function up():void{Schema::table('rss_feeds',function(Blueprint $t){$t->string('source_policy')->default('review')->index();$t->unsignedSmallInteger('max_items_per_run')->default(12);$t->boolean('require_human_review')->default(true)->index();});}
 public function down():void{Schema::table('rss_feeds',fn(Blueprint $t)=>$t->dropColumn(['source_policy','max_items_per_run','require_human_review']));}
};
