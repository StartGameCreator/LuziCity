# Governança da documentação

## Fonte de verdade

A documentação oficial deve permanecer alinhada ao código da branch principal. Planos antigos não devem ser apresentados como estado atual.

## Organização recomendada

```text
docs/
├── architecture/
├── modules/
├── operations/
├── api/
├── integrations/
└── archive/
```

## Documentos históricos

Mover para `docs/archive/` planos de fases já concluídas, relatórios antigos e instruções que apontam para caminhos locais obsoletos. Preserve o histórico, mas adicione cabeçalho indicando data, finalidade e substituto atual.

## Regra de atualização

Toda pull request que altera comportamento, variável de ambiente, rota, schema ou operação deve atualizar a documentação correspondente.
