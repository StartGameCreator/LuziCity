<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        abort_unless(auth()->user()?->hasAnyRole(['Super Admin', 'Admin']), 403);

        return view('admin.dashboard', [
            'sections' => [
                ['label' => 'Agência Assistida', 'description' => 'Coleta, tendências, pré-pautas, aprovações, logs e política de fontes.', 'route' => 'admin.agency-dashboard.index', 'icon' => 'rss'],
                ['label' => 'Saude do Sistema', 'description' => 'Verificar banco, cache, RSS, login social, radio, anuncios e permissoes.', 'route' => 'admin.system-health.index', 'icon' => 'dashboard'],
                ['label' => 'Noticias', 'description' => 'Criar, editar e preparar materias com apoio de IA.', 'route' => 'admin.news.index', 'icon' => 'news'],
                ['label' => 'Usuarios', 'description' => 'Definir assinantes, jornalistas, colunistas, anunciantes e patrocinadores.', 'route' => 'admin.users.index', 'icon' => 'user'],
                ['label' => 'Radio Web', 'description' => 'Configurar transmissao, chat, pedidos e presenca do locutor.', 'route' => 'admin.radio.edit', 'icon' => 'radio'],
                ['label' => 'Banners', 'description' => 'Gerenciar carrosseis, lives, YouTube, Facebook Reels e publicidade.', 'route' => 'admin.media-banners.index', 'icon' => 'video'],
                ['label' => 'Veiculos', 'description' => 'Moderar classificados, limites de anuncios e logos das marcas.', 'route' => 'admin.vehicles.index', 'icon' => 'car'],
                ['label' => 'Imoveis', 'description' => 'Acompanhar anuncios de compra, venda e aluguel.', 'route' => 'admin.real-estate.index', 'icon' => 'home'],
                ['label' => 'Editorias', 'description' => 'Organizar menus, categorias e submenus do portal.', 'route' => 'admin.categories.index', 'icon' => 'grid'],
                ['label' => 'Tags', 'description' => 'Cadastrar tags para busca, SEO e organizacao editorial.', 'route' => 'admin.tags.index', 'icon' => 'grid'],
                ['label' => 'RSS', 'description' => 'Cadastrar fontes externas de noticias e atualizacao editorial.', 'route' => 'admin.rss-feeds.index', 'icon' => 'rss'],
                ['label' => 'Importacao RSS', 'description' => 'Importar noticias dos feeds para o banco e controlar o que aparece na home.', 'route' => 'admin.rss-imports.index', 'icon' => 'rss'],
                ['label' => 'Login Social', 'description' => 'Configurar Client ID, Secret e URLs de retorno dos provedores sociais.', 'route' => 'admin.social-login.edit', 'icon' => 'login'],
                ['label' => 'Links do Site', 'description' => 'Configurar loja, redes sociais e plataformas de audio.', 'route' => 'admin.social-links.edit', 'icon' => 'share'],
                ['label' => 'Pixels', 'description' => 'Cadastrar Pixel da Meta e TikTok para acompanhamento.', 'route' => 'admin.tracking-pixels.edit', 'icon' => 'dashboard'],
                ['label' => 'Empresa', 'description' => 'Editar copyright, CNPJ, contatos e endereco no rodape.', 'route' => 'admin.company-info.edit', 'icon' => 'info'],
                ['label' => 'Sala de Redação', 'description' => 'Pautas, agentes, fontes, verificação, aprovação e calendário editorial.', 'route' => 'admin.editorial-room.dashboard', 'icon' => 'edit'],
                ['label' => 'Central Editorial IA', 'description' => 'Acompanhar execucoes, custos, erros e producao assistida com revisao humana.', 'route' => 'admin.ai.dashboard', 'icon' => 'dashboard'],
                ['label' => 'Configuracoes de IA', 'description' => 'Cadastrar chaves do ChatGPT, Gemini e Copilot.', 'route' => 'admin.ai-settings.edit', 'icon' => 'dashboard'],
                ['label' => 'Quem Somos', 'description' => 'Editar o texto institucional com assistencia de IA.', 'route' => 'admin.site-content.edit', 'icon' => 'edit'],
            ],
        ]);
    }
}
