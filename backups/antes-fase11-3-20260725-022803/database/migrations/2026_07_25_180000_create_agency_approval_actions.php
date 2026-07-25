<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration{
 public function up():void{Schema::create('agency_approval_actions',function(Blueprint $t){$t->id();$t->string('approvable_type');$t->unsignedBigInteger('approvable_id');$t->string('action')->index();$t->string('from_status')->nullable();$t->string('to_status');$t->text('note')->nullable();$t->foreignId('user_id')->constrained()->restrictOnDelete();$t->timestamps();$t->index(['approvable_type','approvable_id']);});}
 public function down():void{Schema::dropIfExists('agency_approval_actions');}
};
