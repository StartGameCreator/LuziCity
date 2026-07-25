<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\DB;use Illuminate\Support\Facades\Schema;
return new class extends Migration{
 public function up():void{
  if(!Schema::hasTable('editorial_verification_reviews'))Schema::create('editorial_verification_reviews',function(Blueprint $t){$t->id();$t->foreignId('editorial_source_claim_id')->constrained()->cascadeOnDelete();$t->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();$t->string('decision',30)->index();$t->text('rationale');$t->text('evidence_excerpt')->nullable();$t->json('alerts')->nullable();$t->timestamps();});
  if(Schema::hasTable('editorial_source_claims')){DB::table('editorial_source_claims')->where('status','unverified')->update(['status'=>'review_required']);DB::table('editorial_source_claims')->where('status','contradicted')->update(['status'=>'conflicting']);}
 }
 public function down():void{Schema::dropIfExists('editorial_verification_reviews');}
};
