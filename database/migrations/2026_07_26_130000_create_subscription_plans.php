<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug', 60)->unique();
            $table->text('description')->nullable();
            $table->decimal('monthly_price', 12, 2)->default(0);
            $table->decimal('yearly_price', 12, 2)->default(0);
            $table->json('benefits')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_ad_free')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });

        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->foreignId('subscription_plan_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->string('billing_cycle', 20)->default('monthly')->after('status');
            $table->decimal('price', 12, 2)->default(0)->after('billing_cycle');
            $table->boolean('auto_renew')->default(false)->after('price');
        });

        $now = now();
        DB::table('subscription_plans')->insert([
            ['name'=>'Gratuito','slug'=>'gratuito','description'=>'Acesso às notícias abertas.','monthly_price'=>0,'yearly_price'=>0,'benefits'=>json_encode(['Notícias abertas','Newsletter básica']),'display_order'=>10,'is_ad_free'=>false,'is_active'=>true,'is_featured'=>false,'created_at'=>$now,'updated_at'=>$now],
            ['name'=>'Premium','slug'=>'premium','description'=>'Conteúdo exclusivo e navegação sem anúncios.','monthly_price'=>19.90,'yearly_price'=>199,'benefits'=>json_encode(['Conteúdo premium','Sem anúncios','Newsletter exclusiva']),'display_order'=>20,'is_ad_free'=>true,'is_active'=>true,'is_featured'=>true,'created_at'=>$now,'updated_at'=>$now],
            ['name'=>'VIP','slug'=>'vip','description'=>'Experiência completa e benefícios especiais.','monthly_price'=>39.90,'yearly_price'=>399,'benefits'=>json_encode(['Todos os benefícios Premium','Eventos exclusivos','Podcasts antecipados']),'display_order'=>30,'is_ad_free'=>true,'is_active'=>true,'is_featured'=>false,'created_at'=>$now,'updated_at'=>$now],
            ['name'=>'Empresarial','slug'=>'empresarial','description'=>'Acesso para equipes e empresas.','monthly_price'=>99.90,'yearly_price'=>999,'benefits'=>json_encode(['Até 10 usuários','Conteúdo premium','Atendimento comercial']),'display_order'=>40,'is_ad_free'=>true,'is_active'=>true,'is_featured'=>false,'created_at'=>$now,'updated_at'=>$now],
        ]);
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->dropForeign(['subscription_plan_id']);
            $table->dropColumn(['subscription_plan_id', 'billing_cycle', 'price', 'auto_renew']);
        });
        Schema::dropIfExists('subscription_plans');
    }
};
