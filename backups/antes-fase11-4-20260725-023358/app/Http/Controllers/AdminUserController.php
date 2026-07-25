<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class AdminUserController extends Controller
{
    public function index(): View
    {
        $this->authorizeAdmin();

        return view('admin.users.index', [
            'users' => User::query()
                ->with(['roles', 'subscription'])
                ->latest()
                ->paginate(20),
            'roles' => Role::query()->orderBy('name')->pluck('name'),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $request->validate([
            'roles' => ['array'],
            'roles.*' => ['string', 'exists:roles,name'],
            'subscription_status' => ['required', 'in:inactive,active,expired,suspended'],
            'subscription_ends_at' => ['nullable', 'date'],
        ]);

        $user->syncRoles($data['roles'] ?? []);

        $user->subscription()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'status' => $data['subscription_status'],
                'starts_at' => $data['subscription_status'] === 'active' ? now() : null,
                'ends_at' => $data['subscription_ends_at'] ?? null,
                'assigned_by' => $request->user()->id,
            ]
        );

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
