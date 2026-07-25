<?php
namespace Tests\Unit\Security;
use App\Services\Security\PublicUrlGuard;use Illuminate\Validation\ValidationException;use Tests\TestCase;
class PublicUrlGuardTest extends TestCase{
 public function test_blocks_internal_and_credential_urls():void{$g=new PublicUrlGuard;foreach(['http://127.0.0.1/admin','http://10.0.0.1','http://user:pass@example.com','file:///etc/passwd'] as $url){try{$g->validate($url);$this->fail("URL deveria ser bloqueada: $url");}catch(ValidationException){$this->assertTrue(true);}}}
 public function test_accepts_public_ip():void{$this->assertSame('https://1.1.1.1/test',(new PublicUrlGuard)->validate('https://1.1.1.1/test'));}
}
