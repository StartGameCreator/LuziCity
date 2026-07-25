<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration{
 public function up():void{
  if(Schema::hasTable('editorial_pitch_sources'))Schema::table('editorial_pitch_sources',function(Blueprint $t){if(!Schema::hasColumn('editorial_pitch_sources','source_type'))$t->string('source_type',20)->default('url');if(!Schema::hasColumn('editorial_pitch_sources','document_path'))$t->string('document_path',500)->nullable();if(!Schema::hasColumn('editorial_pitch_sources','metadata'))$t->json('metadata')->nullable();if(!Schema::hasColumn('editorial_pitch_sources','excerpt'))$t->text('excerpt')->nullable();if(!Schema::hasColumn('editorial_pitch_sources','summary'))$t->text('summary')->nullable();if(!Schema::hasColumn('editorial_pitch_sources','reliability'))$t->string('reliability',20)->default('unrated')->index();if(!Schema::hasColumn('editorial_pitch_sources','fetched_at'))$t->timestamp('fetched_at')->nullable();});
  if(!Schema::hasTable('editorial_source_claims'))Schema::create('editorial_source_claims',function(Blueprint $t){$t->id();$t->foreignId('editorial_pitch_id')->constrained()->cascadeOnDelete();$t->foreignId('editorial_pitch_source_id')->constrained()->cascadeOnDelete();$t->text('claim');$t->string('status',30)->default('unverified')->index();$t->text('contradiction_note')->nullable();$t->timestamps();});
 }
 public function down():void{Schema::dropIfExists('editorial_source_claims');}
};
