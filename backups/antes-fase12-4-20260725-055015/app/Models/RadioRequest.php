<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RadioRequest extends Model
{
    protected $fillable = [
        'name',
        'city',
        'region',
        'category',
        'is_private',
        'recipient_name',
        'private_contact',
        'phone',
        'message',
        'attachment_path',
        'attachment_type',
        'attachment_original_name',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_private' => 'boolean',
        ];
    }

    public static function categoryOptions(): array
    {
        return [
            'geral' => 'Geral',
            'df' => 'Distrito Federal',
            'luziania' => 'Luziania',
            'cristalina' => 'Cristalina',
            'cidade-ocidental' => 'Cidade Ocidental',
            'valparaiso' => 'Valparaiso de Goias',
            'novo-gama' => 'Novo Gama',
            'santo-antonio' => 'Santo Antonio do Descoberto',
            'aguas-lindas' => 'Aguas Lindas de Goias',
            'formosa' => 'Formosa',
            'nacional' => 'Outras regioes do Brasil',
            'encontros-namoro' => 'Encontros e Namoro',
            'trabalho-oportunidades' => 'Trabalho e Oportunidades',
            'pedidos-musica' => 'Pedidos de Musica',
        ];
    }

    public static function regionOptions(): array
    {
        return [
            'df' => 'Distrito Federal',
            'luziania' => 'Luziania',
            'cristalina' => 'Cristalina',
            'cidade-ocidental' => 'Cidade Ocidental',
            'valparaiso' => 'Valparaiso de Goias',
            'novo-gama' => 'Novo Gama',
            'santo-antonio' => 'Santo Antonio do Descoberto',
            'aguas-lindas' => 'Aguas Lindas de Goias',
            'formosa' => 'Formosa',
            'nacional' => 'Outras regioes do Brasil',
        ];
    }

    public function categoryLabel(): string
    {
        return self::categoryOptions()[$this->category] ?? 'Geral';
    }

    public function regionLabel(): string
    {
        return $this->city ?: $this->categoryLabel();
    }
}
