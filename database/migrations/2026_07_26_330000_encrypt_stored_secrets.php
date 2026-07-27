<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('settings')) {
            DB::table('settings')
                ->where(function ($query): void {
                    $query->where(function ($ai): void {
                        $ai->where('group', 'ai')->whereIn('key', [
                            'openai_api_key', 'gemini_api_key', 'copilot_api_key',
                        ]);
                    })->orWhere(function ($social): void {
                        $social->where('group', 'social_login')->where('key', 'like', '%_client_secret');
                    });
                })
                ->whereNotNull('value')
                ->orderBy('id')
                ->each(function ($setting): void {
                    if (! str_starts_with($setting->value, 'enc:')) {
                        DB::table('settings')->where('id', $setting->id)
                            ->update(['value' => 'enc:'.Crypt::encryptString($setting->value)]);
                    }
                });
        }

        if (Schema::hasTable('social_accounts')) {
            DB::table('social_accounts')->orderBy('id')->each(function ($account): void {
                $updates = [];
                foreach (['access_token', 'refresh_token'] as $column) {
                    if (filled($account->{$column})) {
                        $updates[$column] = Crypt::encryptString($account->{$column});
                    }
                }
                if ($updates) {
                    DB::table('social_accounts')->where('id', $account->id)->update($updates);
                }
            });
        }
    }

    public function down(): void
    {
        // A reversão não descriptografa segredos para evitar regressão de segurança.
    }
};
