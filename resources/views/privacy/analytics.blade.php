@extends('layouts.app')
@section('title','Privacidade e Analytics')
@section('content')
<section class="content-band"><p class="eyebrow">Fase 15.2 · LGPD</p><h1>Privacidade e Analytics</h1><p>Você controla a coleta opcional de dados de audiência.</p></section>
<section class="settings-panel"><h2>O que coletamos</h2><p>Página acessada, sessão anonimizada por hash, origem, campanha, tipo de dispositivo, tempo de leitura e rolagem. Não armazenamos endereço IP na tabela de analytics e não coletamos dados de cartão.</p><h2>Finalidade</h2><p>Medir audiência, melhorar conteúdo e compreender campanhas. Os dados são mantidos por {{ config('analytics.retention_days') }} dias e depois removidos automaticamente.</p><h2>Seus controles</h2><p>Você pode aceitar, recusar ou retirar o consentimento. O opt-out remove os eventos vinculados à sessão atual e, quando autenticado, à sua conta.</p>
<div style="display:flex;gap:.75rem;flex-wrap:wrap"><form method="post" action="{{ route('privacy.analytics.consent') }}">@csrf<input type="hidden" name="choice" value="accepted"><button class="primary-action">Aceitar analytics</button></form><form method="post" action="{{ route('privacy.analytics.consent') }}">@csrf<input type="hidden" name="choice" value="denied"><button class="secondary-action">Recusar analytics</button></form></div></section>
<section class="settings-panel"><h2>Opt-out e remoção</h2><form method="post" action="{{ route('privacy.analytics.opt-out') }}">@csrf<button class="secondary-action">Retirar consentimento e remover meus dados de analytics</button></form></section>
@endsection
