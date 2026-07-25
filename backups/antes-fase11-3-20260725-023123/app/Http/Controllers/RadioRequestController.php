<?php

namespace App\Http\Controllers;

use App\Models\RadioRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RadioRequestController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'category' => ['required', 'string', 'max:80'],
            'is_private' => ['nullable', 'boolean'],
            'recipient_name' => ['nullable', 'string', 'max:120'],
            'private_contact' => ['nullable', 'string', 'max:180'],
            'phone' => ['nullable', 'string', 'max:40'],
            'message' => ['required', 'string', 'max:1000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ]);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $data['attachment_path'] = $file->store('radio-chat', 'public');
            $data['attachment_type'] = 'image';
            $data['attachment_original_name'] = $file->getClientOriginalName();
        }

        $data['category'] = array_key_exists($data['category'] ?? '', RadioRequest::categoryOptions())
            ? $data['category']
            : 'geral';

        $data['region'] = null;

        $data['is_private'] = $request->boolean('is_private');
        $data['recipient_name'] = filled($data['recipient_name'] ?? null) ? $data['recipient_name'] : null;

        if (! $data['is_private']) {
            $data['private_contact'] = null;
        }

        unset($data['attachment']);

        RadioRequest::query()->create($data + ['status' => 'new']);

        return back()->with('status', 'Mensagem enviada para o chat da radio.');
    }
}
