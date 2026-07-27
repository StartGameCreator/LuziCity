<label>Nome<input name="name" required maxlength="120" value="{{ old('name',$site?->name) }}"></label>
<label>Slug<input name="slug" maxlength="120" value="{{ old('slug',$site?->slug) }}" placeholder="gerado pelo nome"></label>
<label>Cidade<input name="city" maxlength="120" value="{{ old('city',$site?->city) }}"></label>
<label>UF<input name="state" maxlength="2" value="{{ old('state',$site?->state) }}"></label>
<label>Domínios, um por linha<textarea name="domains" required placeholder="portal.example.com">{{ old('domains',$site?->domains?->pluck('domain')->implode("\n")) }}</textarea></label>
<div style="display:flex;gap:1rem;flex-wrap:wrap"><label>Cor principal<input type="color" name="theme_primary" value="{{ old('theme_primary',$site?->theme_primary??'#0067c0') }}"></label><label>Cor secundária<input type="color" name="theme_secondary" value="{{ old('theme_secondary',$site?->theme_secondary??'#004e8c') }}"></label></div>
<label>Logo<input type="file" name="logo" accept="image/*"></label>
<label>Favicon<input type="file" name="favicon" accept="image/*"></label>
<label>Imagem de fundo<input type="file" name="theme_background" accept="image/*"></label>
<label>Configurações, uma por linha (`chave=valor`)<textarea name="settings" placeholder="share_image=images/share.png">{{ old('settings',$site?->settings?->map(fn($setting)=>$setting->key.'='.$setting->value)->implode("\n")) }}</textarea></label>
<label><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$site?->is_active??true))> Site ativo</label>
<label><input type="checkbox" name="is_default" value="1" @checked(old('is_default',$site?->is_default??false))> Site padrão</label>
