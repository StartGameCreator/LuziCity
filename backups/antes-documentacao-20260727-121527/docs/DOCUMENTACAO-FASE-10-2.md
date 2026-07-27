# Central Editorial IA — Fase 10.2

## Módulos
- Visão geral: indicadores, últimas execuções e erros.
- Gerar notícia: rascunho assistido, sempre sujeito à revisão humana.
- Prompts: biblioteca, versões, comparação e restauração.
- Memória: perfis, termos e regras editoriais.
- Provedores: configuração, limites, saúde e fallback.
- Custos: consumo por período, recurso, provedor e usuário.
- Logs: histórico filtrável e detalhes seguros.

## Acesso
Admin e Super Admin administram todos os módulos. Jornalistas acessam a visão geral restrita às próprias execuções e a geração editorial. Chaves, payloads integrais e textos de fontes não aparecem nos logs comuns.

## Operação
As páginas ficam sob `/admin/ia`. O sistema não publica conteúdo gerado automaticamente: a revisão humana continua obrigatória.
