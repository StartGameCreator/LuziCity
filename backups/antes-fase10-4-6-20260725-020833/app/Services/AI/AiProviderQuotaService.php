<?php
namespace App\Services\AI;
use App\Models\AiProvider;use RuntimeException;
class AiProviderQuotaService{
 public function usage(AiProvider $p):array{$q=$p->executions();return ['daily'=>(clone $q)->whereDate('created_at',today())->count(),'monthly'=>(clone $q)->whereBetween('created_at',[now()->startOfMonth(),now()->endOfMonth()])->count()];}
 public function available(AiProvider $p):bool{if(!$p->is_enabled)return false;if($p->circuit_open_until&&$p->circuit_open_until->isFuture())return false;$u=$this->usage($p);return (!$p->daily_request_limit||$u['daily']<$p->daily_request_limit)&&(!$p->monthly_request_limit||$u['monthly']<$p->monthly_request_limit);}
 public function assertAvailable(AiProvider $p):void{if(!$this->available($p))throw new RuntimeException("O provedor {$p->name} está desativado, com circuito aberto ou atingiu o limite configurado.");}
}
