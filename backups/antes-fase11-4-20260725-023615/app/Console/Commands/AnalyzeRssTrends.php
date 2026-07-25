<?php
namespace App\Console\Commands;
use App\Services\RssTrendService;
use Illuminate\Console\Command;
class AnalyzeRssTrends extends Command {
 protected $signature='luzicity:rss-analyze-trends'; protected $description='Analisa tendências dos itens RSS coletados';
 public function handle(RssTrendService $service):int{$result=$service->analyze();$this->info("{$result['saved']} tendência(s), {$result['alerts']} alerta(s).");return self::SUCCESS;}
}
