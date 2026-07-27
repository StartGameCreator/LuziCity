<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSubscriberController extends Controller
{
    public function index(Request $request): View
    {
        $query = Subscription::with(['user', 'plan', 'histories.toPlan', 'payments.refunds']);
        $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')));
        $query->when($request->filled('q'), fn ($q) => $q->whereHas('user', fn ($u) => $u->where('name', 'like', '%'.$request->string('q').'%')->orWhere('email', 'like', '%'.$request->string('q').'%')));

        return view('admin.subscribers.index', [
            'subscriptions' => $query->latest()->paginate(20)->withQueryString(),
            'metrics' => [
                'active' => Subscription::active()->count(),
                'cancelled' => Subscription::where('status', 'cancelled')->count(),
                'expiring' => Subscription::active()->whereBetween('ends_at', [now(), now()->addDays(30)])->count(),
                'total' => Subscription::count(),
            ],
        ]);
    }
}
