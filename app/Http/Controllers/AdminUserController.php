<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class AdminUserController extends Controller
{
    public function index(): View
    {
        $this->authorizeAdmin();

        $site = Site::current();

        return view('admin.users.index', [
            'users' => User::query()->when($site, fn ($query) => $query->whereHas('sites', fn ($sites) => $sites->whereKey($site->id)->where('site_user.is_active', true)))
                ->with(['roles', 'subscription.plan'])
                ->latest()
                ->paginate(20),
            'roles' => Role::query()->orderBy('name')->pluck('name'),
            'plans' => SubscriptionPlan::where('is_active', true)->orderBy('display_order')->get(),
            'currentSite' => $site,
        ]);
    }

    public function update(Request $request, User $user, SubscriptionService $subscriptions): RedirectResponse
    {
        $this->authorizeAdmin();
        $site = Site::current();
        abort_if($site && ! $user->canAccessSite($site), 404);

        $data = $request->validate([
            'roles' => ['array'],
            'roles.*' => ['string', 'exists:roles,name'],
            'subscription_status' => ['required', 'in:inactive,active,expired,suspended,cancelled'],
            'subscription_plan_id' => ['nullable', 'exists:subscription_plans,id'],
            'billing_cycle' => ['required', 'in:monthly,yearly'],
            'subscription_ends_at' => ['nullable', 'date'],
            'site_permissions' => ['nullable', 'array'],
            'site_permissions.*' => ['string', 'in:manage_content,manage_users,manage_media,manage_ads'],
        ]);

        $user->syncRoles($data['roles'] ?? []);
        if ($site) {
            $user->sites()->updateExistingPivot($site->id, [
                'permissions' => json_encode(array_values(array_unique($data['site_permissions'] ?? []))),
            ]);
        }

        $subscriptions->update($user, [
            'status' => $data['subscription_status'],
            'subscription_plan_id' => $data['subscription_plan_id'] ?? null,
            'billing_cycle' => $data['billing_cycle'],
            'price' => ($plan = SubscriptionPlan::find($data['subscription_plan_id'] ?? null))
                ? ($data['billing_cycle'] === 'yearly' ? $plan->yearly_price : $plan->monthly_price) : 0,
            'starts_at' => $data['subscription_status'] === 'active' ? now() : null,
            'ends_at' => $data['subscription_ends_at'] ?? null,
            'assigned_by' => $request->user()->id,
        ], $request->user());

        $this->syncProfiles($user, $data['roles'] ?? []);

        return back()->with('status', 'Usuario atualizado com sucesso.');
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['Super Admin', 'Admin']), 403);
    }

    private function syncProfiles(User $user, array $roles): void
    {
        if (in_array('Jornalista', $roles, true)) {
            $user->journalistProfile()->firstOrCreate(['user_id' => $user->id]);
        }

        if (in_array('Colunista', $roles, true)) {
            $user->columnistProfile()->firstOrCreate(['user_id' => $user->id]);
        }

        if (in_array('Anunciante', $roles, true)) {
            $user->advertiserProfile()->firstOrCreate(['user_id' => $user->id]);
        }
    }
}
