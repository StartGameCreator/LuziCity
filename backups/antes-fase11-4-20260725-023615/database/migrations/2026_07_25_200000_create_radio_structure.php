<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration{
 public function up():void{
  Schema::create('radio_stations',function(Blueprint $t){$t->id();$t->string('name');$t->string('call_sign')->nullable();$t->string('description',1000)->nullable();$t->string('stream_url',2048)->nullable();$t->string('logo_path')->nullable();$t->boolean('is_active')->default(true)->index();$t->boolean('force_on_air')->default(false);$t->string('on_air_label')->nullable();$t->timestamps();});
  Schema::create('radio_hosts',function(Blueprint $t){$t->id();$t->string('name');$t->text('bio')->nullable();$t->string('photo_path')->nullable();$t->boolean('is_active')->default(true)->index();$t->timestamps();});
  Schema::create('radio_programs',function(Blueprint $t){$t->id();$t->foreignId('radio_station_id')->constrained()->cascadeOnDelete();$t->foreignId('radio_host_id')->nullable()->constrained()->nullOnDelete();$t->string('name');$t->text('description')->nullable();$t->string('cover_path')->nullable();$t->boolean('is_active')->default(true)->index();$t->timestamps();});
  Schema::create('radio_schedule_slots',function(Blueprint $t){$t->id();$t->foreignId('radio_program_id')->constrained()->cascadeOnDelete();$t->unsignedTinyInteger('day_of_week')->index();$t->time('starts_at');$t->time('ends_at');$t->boolean('is_live')->default(true);$t->boolean('is_active')->default(true)->index();$t->timestamps();$t->index(['day_of_week','starts_at','ends_at']);});
 }
 public function down():void{Schema::dropIfExists('radio_schedule_slots');Schema::dropIfExists('radio_programs');Schema::dropIfExists('radio_hosts');Schema::dropIfExists('radio_stations');}
};
