<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration{
 public function up():void{
  if(Schema::hasTable('ai_editorial_profiles'))foreach(['category_id','target_audience','priority_region'] as $c)if(!Schema::hasColumn('ai_editorial_profiles',$c))Schema::table('ai_editorial_profiles',function(Blueprint $t)use($c){$c==='category_id'?$t->foreignId($c)->nullable()->constrained('categories')->nullOnDelete():$t->string($c,240)->nullable();});
  if(!Schema::hasTable('ai_editorial_rules'))Schema::create('ai_editorial_rules',function(Blueprint $t){$t->id();$t->foreignId('profile_id')->constrained('ai_editorial_profiles')->cascadeOnDelete();$t->string('name',180);$t->string('rule_type',60)->index();$t->text('instruction');$t->unsignedSmallInteger('priority')->default(100);$t->boolean('active')->default(true)->index();$t->timestamps();});
  if(!Schema::hasTable('ai_editorial_terms'))Schema::create('ai_editorial_terms',function(Blueprint $t){$t->id();$t->foreignId('profile_id')->constrained('ai_editorial_profiles')->cascadeOnDelete();$t->string('term',180);$t->string('replacement',180)->nullable();$t->string('type',30)->index();$t->string('context',500)->nullable();$t->boolean('active')->default(true)->index();$t->timestamps();});
 }
 public function down():void{Schema::dropIfExists('ai_editorial_terms');Schema::dropIfExists('ai_editorial_rules');}
};
