<?php

use App\Http\Controllers\SubscriptionPaymentController;
use Illuminate\Support\Facades\Route;

Route::post('/pagamentos/webhook/mercado-pago', [SubscriptionPaymentController::class, 'webhook'])
    ->middleware('throttle:120,1')->name('payments.webhook');
Route::middleware('auth')->group(function (): void {
    Route::post('/minha-assinatura/pagar', [SubscriptionPaymentController::class, 'checkout'])->name('subscriber.payment.checkout');
    Route::get('/minha-assinatura/retorno/{status}', [SubscriptionPaymentController::class, 'returned'])->name('subscriber.payment.return');
});
Route::post('/admin/assinaturas/pagamentos/{payment}/reembolsar', [SubscriptionPaymentController::class, 'refund'])
    ->middleware(['auth', 'roles:Super Admin,Admin'])->name('admin.subscription-payments.refund');
