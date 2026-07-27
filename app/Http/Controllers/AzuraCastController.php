<?php

namespace App\Http\Controllers;

use App\Contracts\RadioAutomationProvider;
use App\Exceptions\AzuraCastException;
use App\Services\RadioPlaybackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AzuraCastController extends Controller
{
    public function publicState(RadioPlaybackService $playback): JsonResponse
    {
        return response()->json($playback->state());
    }

    public function health(RadioAutomationProvider $provider): JsonResponse
    {
        return response()->json($provider->health());
    }

    public function test(RadioAutomationProvider $provider): RedirectResponse
    {
        $health = $provider->health();

        return back()->with(
            $health['connected'] ? 'status' : 'error',
            $health['message'] ?? 'Teste do AzuraCast concluído.'
        );
    }

    public function control(
        Request $request,
        string $action,
        RadioAutomationProvider $provider,
    ): RedirectResponse {
        try {
            $provider->control($action);
            Log::notice('Comando administrativo enviado ao AzuraCast.', [
                'action' => $action,
                'user_id' => $request->user()?->id,
                'ip' => $request->ip(),
            ]);

            return back()->with('status', 'Comando enviado ao motor da rádio.');
        } catch (AzuraCastException $exception) {
            Log::warning('Comando administrativo do AzuraCast recusado.', [
                'action' => $action,
                'user_id' => $request->user()?->id,
                'status' => $exception->statusCode,
                'message' => $exception->getMessage(),
            ]);

            return back()->with('error', $exception->getMessage());
        }
    }
}
