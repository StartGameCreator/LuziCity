<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration{
 public function up():void{Schema::create('video_clips',function(Blueprint $t){$t->id();$t->foreignId('video_id')->constrained()->cascadeOnDelete();$t->string('title');$t->string('status')->default('queued')->index();$t->string('aspect_ratio',10)->default('9:16');$t->unsignedInteger('starts_at_ms');$t->unsignedInteger('ends_at_ms');$t->text('caption_text')->nullable();$t->string('subtitle_path')->nullable();$t->string('output_path')->nullable();$t->text('error_message')->nullable();$t->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();$t->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();$t->timestamp('rendered_at')->nullable();$t->timestamp('reviewed_at')->nullable();$t->timestamps();});}
 public function down():void{Schema::dropIfExists('video_clips');}
};
