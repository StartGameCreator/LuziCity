<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\DB;use Illuminate\Support\Facades\Schema;
return new class extends Migration{
 public function up():void{
  if(!Schema::hasTable('ai_agents'))Schema::create('ai_agents',function(Blueprint $t){$t->id();$t->string('slug',80)->unique();$t->string('name',120);$t->text('instructions');$t->boolean('is_enabled')->default(true);$t->unsignedInteger('position')->default(0);$t->timestamps();});
  if(!Schema::hasTable('ai_agent_runs'))Schema::create('ai_agent_runs',function(Blueprint $t){$t->id();$t->foreignId('editorial_pitch_id')->constrained()->cascadeOnDelete();$t->foreignId('ai_agent_id')->constrained()->restrictOnDelete();$t->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();$t->string('status',30)->default('draft')->index();$t->unsignedInteger('current_step')->default(1);$t->timestamps();});
  if(!Schema::hasTable('ai_agent_steps'))Schema::create('ai_agent_steps',function(Blueprint $t){$t->id();$t->foreignId('ai_agent_run_id')->constrained()->cascadeOnDelete();$t->unsignedInteger('sequence');$t->string('status',30)->default('pending_review')->index();$t->longText('output');$t->text('editor_note')->nullable();$t->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();$t->timestamp('decided_at')->nullable();$t->timestamps();$t->unique(['ai_agent_run_id','sequence']);});
  if(DB::table('ai_agents')->count()===0){$agents=['editor-chefe'=>'Editor-chefe','reporter'=>'Repórter','pesquisador'=>'Pesquisador','verificador'=>'Verificador','reescritor'=>'Reescritor','revisor'=>'Revisor','seo'=>'Especialista SEO','social-media'=>'Social media'];$i=0;foreach($agents as $slug=>$name)DB::table('ai_agents')->insert(['slug'=>$slug,'name'=>$name,'instructions'=>"Produza somente a etapa de {$name}. Não publique, não invente fatos e sinalize incertezas.",'is_enabled'=>true,'position'=>$i++,'created_at'=>now(),'updated_at'=>now()]);}
 }
 public function down():void{Schema::dropIfExists('ai_agent_steps');Schema::dropIfExists('ai_agent_runs');Schema::dropIfExists('ai_agents');}
};
