<?php

namespace App\Console\Commands;

use App\Services\Database\DatabaseHealthService;
use Illuminate\Console\Command;

class AuditDatabaseCommand extends Command
{
    protected $signature = 'luzicity:database-audit {--json : Exibe o resultado em JSON}';

    protected $description = 'Audita a estrutura, integridade e configuração do banco do LuziCity.';

    public function handle(DatabaseHealthService $service): int
    {
        $result = $service->audit();

        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            return $result['ok'] ? self::SUCCESS : self::FAILURE;
        }

        $this->info('Auditoria do banco LuziCity');
        $this->table(['Item', 'Resultado'], [
            ['Driver', $result['driver']],
            ['Banco', $result['database']],
            ['Tabelas', (string) $result['table_count']],
            ['Migrations registradas', (string) $result['migration_count']],
            ['Foreign keys ativas', $result['foreign_keys_enabled'] === null ? 'não aplicável' : ($result['foreign_keys_enabled'] ? 'sim' : 'não')],
            ['Tabelas ausentes', (string) count($result['missing_tables'])],
            ['Órfãos detectados', (string) count($result['orphans'])],
            ['Slugs duplicados', (string) count($result['duplicate_slugs'])],
        ]);

        foreach ($result['missing_tables'] as $table) {
            $this->error('Tabela ausente: '.$table);
        }
        foreach ($result['orphans'] as $orphan) {
            $this->error("Órfãos: {$orphan['table']}.{$orphan['foreignKey']} -> {$orphan['parent']} ({$orphan['count']})");
        }
        foreach ($result['duplicate_slugs'] as $table => $count) {
            $this->error("Slugs duplicados em {$table}: {$count}");
        }

        if ($result['foreign_keys_enabled'] === false) {
            $this->warn('SQLite está com foreign_keys desativado nesta conexão. Confirme DB_FOREIGN_KEYS=true.');
        }

        $result['ok'] ? $this->info('Banco aprovado na auditoria.') : $this->warn('Banco requer atenção.');

        return $result['ok'] ? self::SUCCESS : self::FAILURE;
    }
}
