<?php
namespace App\Services\Security;
use Illuminate\Validation\ValidationException;
class PublicUrlGuard{
 public function validate(string $url):string{$url=trim($url);$parts=parse_url($url);if(!is_array($parts)||!in_array(strtolower($parts['scheme']??''),['http','https'],true)||empty($parts['host'])||isset($parts['user'])||isset($parts['pass']))$this->reject();$host=strtolower(rtrim($parts['host'],'.'));if($host==='localhost'||str_ends_with($host,'.local'))$this->reject();$ips=filter_var($host,FILTER_VALIDATE_IP)?[$host]:(gethostbynamel($host)?:[]);if($ips===[])$this->reject('Não foi possível resolver o domínio.');foreach($ips as $ip)if(!filter_var($ip,FILTER_VALIDATE_IP,FILTER_FLAG_NO_PRIV_RANGE|FILTER_FLAG_NO_RES_RANGE))$this->reject();return $url;}
 private function reject(string $message='URL privada ou inválida bloqueada.'):never{throw ValidationException::withMessages(['url'=>$message]);}
}
