@extends('layouts.app', ['title' => 'Dados da Empresa - Luzicity'])

@section('content')
    <section class="content-band">
        <p class="eyebrow">Administração</p>
        <h1>Dados da Empresa</h1>
        <p>Cadastre as informações exibidas no rodapé do site.</p>
    </section>

    <section class="settings-panel" aria-label="Cadastro dos dados da empresa">
        <form method="post" action="{{ route('admin.company-info.update') }}" enctype="multipart/form-data" class="admin-form social-settings-form">
            @csrf
            @method('put')

            <section class="ad-settings-panel" aria-label="Identidade visual do site">
                <p class="eyebrow">Marca</p>
                <h2>Identidade do site</h2>
                <p>O nome e a logo aparecem no topo, no favicon do navegador e nas prévias de compartilhamento.</p>

                <label>
                    Nome do site
                    <input name="site_name" value="{{ old('site_name', $companyInfo['site_name'] ?? 'Luzicity') }}" placeholder="Luzicity">
                </label>

                <div class="visual-block-admin-grid">
                    @foreach([
                        'site_logo' => 'Logo do topo',
                        'site_favicon' => 'Favicon / icone do app',
                        'default_share_image' => 'Imagem padrão de compartilhamento',
                    ] as $field => $label)
                        @php($image = $companyInfo[$field] ?? '')
                        <article class="visual-block-admin-card">
                            <div class="visual-block-admin-preview identity-preview" @if($image) style="background-image: url('{{ str_starts_with($image, 'http') ? $image : asset($image) }}')" @endif></div>
                            <label>
                                {{ $label }}
                                <input type="file" accept="image/png,.png" data-identity-upload="{{ $field }}">
                                <textarea name="{{ $field }}_data" data-identity-data="{{ $field }}" hidden></textarea>
                            </label>
                            @if($field === 'site_logo')
                                <p class="field-help">Use somente PNG, de preferencia com fundo transparente. Tamanho recomendado: 320 x 96 px. Limite: 64 MB.</p>
                            @elseif($field === 'site_favicon')
                                <p class="field-help">Use somente PNG quadrado. Tamanho recomendado: 512 x 512 px. Limite: 16 MB.</p>
                            @else
                                <p class="field-help">Use somente PNG horizontal. Tamanho recomendado: 1200 x 630 px para Facebook e WhatsApp. Limite: 64 MB.</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>

            <label>
                Copyright
                <input
                    name="copyright"
                    value="{{ old('copyright', $companyInfo['copyright'] ?? '') }}"
                    placeholder="Copyright © 2026 Luzicity. Todos os direitos reservados."
                >
            </label>

            <div class="social-settings-grid">
                <label>
                    CNPJ
                    <input name="cnpj" value="{{ old('cnpj', $companyInfo['cnpj'] ?? '') }}" placeholder="00.000.000/0001-00">
                </label>

                <label>
                    Telefone
                    <input name="phone" value="{{ old('phone', $companyInfo['phone'] ?? '') }}" placeholder="(00) 0000-0000">
                </label>

                <label>
                    WhatsApp
                    <input name="whatsapp" value="{{ old('whatsapp', $companyInfo['whatsapp'] ?? '') }}" placeholder="(00) 00000-0000">
                </label>

                <label>
                    WhatsApp secundario
                    <input name="whatsapp_secondary" value="{{ old('whatsapp_secondary', $companyInfo['whatsapp_secondary'] ?? '') }}" placeholder="(00) 00000-0000">
                </label>

                <label>
                    E-mail de contato
                    <input type="email" name="email" value="{{ old('email', $companyInfo['email'] ?? '') }}" placeholder="contato@luzicity.com.br">
                </label>

                <label>
                    E-mail de contato 2
                    <input type="email" name="email_secondary" value="{{ old('email_secondary', $companyInfo['email_secondary'] ?? '') }}" placeholder="comercial@luzicity.com.br">
                </label>

                <label>
                    E-mail de contato 3
                    <input type="email" name="email_tertiary" value="{{ old('email_tertiary', $companyInfo['email_tertiary'] ?? '') }}" placeholder="redacao@luzicity.com.br">
                </label>
            </div>

            <label>
                Endereço da Empresa
                <textarea name="address" rows="4" placeholder="Rua, número, bairro, cidade, estado e CEP">{{ old('address', $companyInfo['address'] ?? '') }}</textarea>
            </label>

            <button class="secondary-action" type="submit">Salvar dados da empresa</button>
        </form>
    </section>

    <script>
        (() => {
            document.querySelectorAll('[data-identity-upload]').forEach((input) => {
                input.addEventListener('change', () => {
                    const file = input.files && input.files[0];
                    const field = input.dataset.identityUpload;
                    const dataField = document.querySelector(`[data-identity-data="${field}"]`);
                    const preview = input.closest('.visual-block-admin-card')?.querySelector('.identity-preview');

                    if (! dataField) {
                        return;
                    }

                    dataField.value = '';

                    if (! file) {
                        return;
                    }

                    if (! file.name.toLowerCase().endsWith('.png')) {
                        alert('Use somente arquivo PNG.');
                        input.value = '';
                        return;
                    }

                    const reader = new FileReader();

                    reader.onload = () => {
                        dataField.value = reader.result;

                        if (preview) {
                            preview.style.backgroundImage = `url("${reader.result}")`;
                        }
                    };

                    reader.readAsDataURL(file);
                });
            });
        })();
    </script>
@endsection
