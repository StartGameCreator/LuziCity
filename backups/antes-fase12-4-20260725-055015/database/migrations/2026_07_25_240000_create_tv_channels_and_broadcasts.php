<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration{
 public function up():void{
  Schema::create('tv_channels',function(Blueprint $t){$t->id();$t->string('name');$t->string('slug')->unique();$t->text('description')->nullable();$t->string('logo_path')->nullable();$t->boolean('is_active')->default(true)->index();$t->timestamps();});
  Schema::create('tv_broadcasts',function(Blueprint $t){$t->id();$t->foreignId('tv_channel_id')->constrained()->cascadeOnDelete();$t->string('title');$t->text('description')->nullable();$t->string('provider')->index();$t->string('playback_url',2048)->nullable();$t->text('embed_code')->nullable();$t->string('rtmp_server',2048)->nullable();$t->text('rtmp_key')->nullable();$t->timestamp('starts_at')->nullable()->index();$t->timestamp('ends_at')->nullable()->index();$t->string('status')->default('scheduled')->index();$t->boolean('force_live')->default(false)->index();$t->timestamps();});
 }
 public function down():void{Schema::dropIfExists('tv_broadcasts');Schema::dropIfExists('tv_channels');}
};
