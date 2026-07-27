<?php

namespace Tests\Feature;

use App\Models\AdvertiserProfile;
use App\Models\CommercialInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CommercialFinanceManagementTest extends TestCase
{
    use RefreshDatabase;

    private function adminAndAdvertiser(): array
    {
        $admin = User::factory()->create();
        Role::findOrCreate('Admin');
        $admin->assignRole('Admin');
        $user = User::factory()->create();
        $advertiser = AdvertiserProfile::create([
            'user_id' => $user->id, 'company_name' => 'Cliente Financeiro', 'legal_name' => 'Cliente Financeiro Ltda',
            'email' => 'financeiro@example.com', 'commercial_status' => 'active', 'is_active' => true,
        ]);

        return [$admin, $advertiser];
    }

    public function test_admin_creates_invoice_and_records_partial_and_full_payments(): void
    {
        [$admin, $advertiser] = $this->adminAndAdvertiser();
        $this->actingAs($admin)->post(route('admin.commercial-finance.store'), [
            'advertiser_profile_id' => $advertiser->id, 'description' => 'Campanha mensal', 'amount' => 1000,
            'issued_at' => today()->format('Y-m-d'), 'due_at' => today()->addDays(10)->format('Y-m-d'),
        ])->assertRedirect();
        $invoice = CommercialInvoice::firstOrFail();
        $this->actingAs($admin)->post(route('admin.commercial-finance.payments.store', $invoice), [
            'amount' => 400, 'paid_at' => now()->format('Y-m-d H:i:s'), 'method' => 'pix',
        ])->assertRedirect();
        $this->assertSame('partial', $invoice->fresh()->status);
        $this->actingAs($admin)->post(route('admin.commercial-finance.payments.store', $invoice), [
            'amount' => 600, 'paid_at' => now()->format('Y-m-d H:i:s'), 'method' => 'transfer',
        ])->assertRedirect();
        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertSame('1000.00', $invoice->fresh()->paid_amount);
    }

    public function test_overdue_reconciliation_and_renewal(): void
    {
        [$admin, $advertiser] = $this->adminAndAdvertiser();
        $invoice = CommercialInvoice::create([
            'advertiser_profile_id' => $advertiser->id, 'number' => 'COB-TESTE', 'description' => 'Mensalidade',
            'amount' => 250, 'issued_at' => today()->subMonth(), 'due_at' => today()->subDay(),
            'is_recurring' => true, 'recurrence' => 'monthly',
        ]);
        $this->actingAs($admin)->get(route('admin.commercial-finance.index'))->assertOk();
        $this->assertSame('overdue', $invoice->fresh()->status);
        $this->actingAs($admin)->post(route('admin.commercial-finance.renew', $invoice))->assertRedirect();
        $this->assertCount(2, CommercialInvoice::all());
    }
}
