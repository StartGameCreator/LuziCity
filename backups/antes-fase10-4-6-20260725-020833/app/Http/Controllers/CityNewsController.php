<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CityNewsController extends Controller
{
    public function show(Request $request, string $city): View
    {
        $location = collect(config('luzicity.city_locations'))
            ->firstWhere('slug', $city);

        abort_unless($location, 404);

        return view('cities.show', [
            'location' => $location,
            'cityMenu' => collect(config('luzicity.city_locations')),
            'articles' => $this->sampleArticles($location),
        ]);
    }

    private function sampleArticles(array $location): Collection
    {
        return collect([
            [
                'section' => 'Cidade',
                'title' => 'Cobertura local de '.$location['name'].' ganha espaço próprio na Luzicity',
                'excerpt' => 'A editoria regional fica preparada para notícias, serviços, trânsito, comunidade e agenda pública.',
                'time' => 'Agora',
            ],
            [
                'section' => 'Serviço',
                'title' => 'Moradores acompanham atualizações importantes da região',
                'excerpt' => 'A página poderá receber alertas, calendário de eventos, decisões públicas e informações úteis.',
                'time' => 'Hoje',
            ],
            [
                'section' => 'Comunidade',
                'title' => 'Espaço regional permite expansão para novas cidades e estados',
                'excerpt' => 'Novas localidades podem ser adicionadas pela configuração do projeto conforme a plataforma crescer.',
                'time' => 'Hoje',
            ],
        ]);
    }
}
