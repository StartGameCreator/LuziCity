<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\View\View;

class EventGalleryController extends Controller
{
    public function __invoke(): View
    {
        $eventBlock = Setting::visualBlock('events');
        $eventImage = $eventBlock['image'] ?? 'images/events/fotos-eventos.png';
        $eventImageUrl = str_starts_with($eventImage, 'http') ? $eventImage : asset($eventImage);

        $event = [
            'title' => 'Fotos de Eventos',
            'subtitle' => 'Coberturas especiais da Luzicity',
            'location' => 'Luziânia, Entorno e região',
            'date' => 'Galeria em atualização',
            'report' => 'Este espaço fica reservado para a reportagem do evento, com bastidores, atrações, entrevistas, nomes dos organizadores e os melhores momentos registrados pela equipe Luzicity.',
        ];

        $photos = collect([
            [
                'title' => 'Palco principal',
                'location' => 'Cobertura Luzicity',
                'image' => $eventImageUrl,
            ],
            [
                'title' => 'Público e energia',
                'location' => 'Momentos do evento',
                'image' => $eventImageUrl,
            ],
            [
                'title' => 'Bastidores',
                'location' => 'Equipe e convidados',
                'image' => $eventImageUrl,
            ],
            [
                'title' => 'Melhores registros',
                'location' => 'Galeria oficial',
                'image' => $eventImageUrl,
            ],
        ]);

        return view('events.gallery', [
            'event' => $event,
            'eventImageUrl' => $eventImageUrl,
            'photos' => $photos,
            'meta' => [
                'title' => 'Fotos de Eventos - Luzicity',
                'description' => 'Galeria de fotos, reportagens e coberturas de eventos da Luzicity.',
                'image' => $eventImage,
                'type' => 'website',
            ],
        ]);
    }
}
