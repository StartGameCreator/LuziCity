<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration{
 public function up():void{
  Schema::create('audio_spots',function(Blueprint $t){$t->id();$t->string('name');$t->string('advertiser')->nullable();$t->string('audio_path',2048);$t->string('audio_mime',80)->default('audio/mpeg');$t->unsignedInteger('duration_seconds')->nullable();$t->boolean('is_active')->default(true)->index();$t->timestamps();});
  Schema::create('audio_campaigns',function(Blueprint $t){$t->id();$t->foreignId('audio_spot_id')->constrained()->cascadeOnDelete();$t->string('name');$t->string('status')->default('draft')->index();$t->timestamp('starts_at')->nullable()->index();$t->timestamp('ends_at')->nullable()->index();$t->time('daily_starts_at')->nullable();$t->time('daily_ends_at')->nullable();$t->json('weekdays')->nullable();$t->unsignedInteger('max_plays')->nullable();$t->unsignedInteger('priority')->default(0)->index();$t->timestamps();});
  Schema::create('audio_ad_plays',function(Blueprint $t){$t->id();$t->foreignId('audio_campaign_id')->constrained()->cascadeOnDelete();$t->string('session_hash',64)->nullable()->index();$t->boolean('completed')->default(false);$t->unsignedInteger('listened_seconds')->default(0);$t->string('user_agent_hash',64)->nullable();$t->timestamp('played_at')->index();$t->timestamps();});
 }
 public function down():void{Schema::dropIfExists('audio_ad_plays');Schema::dropIfExists('audio_campaigns');Schema::dropIfExists('audio_spots');}
};
