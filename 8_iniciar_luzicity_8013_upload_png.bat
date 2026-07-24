@echo off
cd /d "%~dp0"

echo ==========================================
echo  Iniciando Luzicity na porta 8013
echo ==========================================
echo.
echo Este iniciador usa o php-luzicity.ini com upload PNG maior.
echo Endereco do site: http://127.0.0.1:8013
echo.
echo IMPORTANTE:
echo Se ja existir outro servidor aberto na porta 8013,
echo feche o terminal antigo com CTRL+C antes de continuar.
echo.

if not exist "storage\app\tmp" mkdir "storage\app\tmp"
if not exist "public\images\identity" mkdir "public\images\identity"
for %%I in ("storage\app\tmp") do set "LUZICITY_UPLOAD_TMP=%%~fI"

echo Conferindo configuracao de upload:
php -c "%~dp0php-luzicity.ini" -d upload_tmp_dir="%LUZICITY_UPLOAD_TMP%" -d sys_temp_dir="%LUZICITY_UPLOAD_TMP%" -d upload_max_filesize=128M -d post_max_size=140M -r "echo 'upload_max_filesize='.ini_get('upload_max_filesize').PHP_EOL; echo 'post_max_size='.ini_get('post_max_size').PHP_EOL; echo 'upload_tmp_dir='.ini_get('upload_tmp_dir').PHP_EOL; echo 'sys_temp_dir='.ini_get('sys_temp_dir').PHP_EOL; echo is_dir(ini_get('upload_tmp_dir')) ? 'tmp_existe=sim'.PHP_EOL : 'tmp_existe=nao'.PHP_EOL; echo is_writable(ini_get('upload_tmp_dir')) ? 'tmp_gravavel=sim'.PHP_EOL : 'tmp_gravavel=nao'.PHP_EOL;"
echo.

php -c "%~dp0php-luzicity.ini" artisan optimize:clear

echo.
echo Servidor iniciado diretamente pelo PHP com upload temporario corrigido.
echo Se aparecer "Failed to listen", feche o servidor antigo da porta 8013.
echo.

cd public
php -c "%~dp0php-luzicity.ini" -d upload_tmp_dir="%LUZICITY_UPLOAD_TMP%" -d sys_temp_dir="%LUZICITY_UPLOAD_TMP%" -d upload_max_filesize=128M -d post_max_size=140M -S 127.0.0.1:8013 "%~dp0vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php"

pause
