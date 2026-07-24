@echo off
cd /d "%~dp0"
echo ==========================================
echo  Luzicity - atualizar certificados do PHP
echo ==========================================
echo.

set "CERT_DIR=%~dp0storage\certs"
set "CERT_FILE=%CERT_DIR%\windows-ca-bundle.pem"

if not exist "%CERT_DIR%" mkdir "%CERT_DIR%"

powershell -NoProfile -ExecutionPolicy Bypass -Command "$bundle = '%CERT_FILE%'; '' | Set-Content -LiteralPath $bundle -Encoding ascii; $stores = @('Cert:\LocalMachine\Root','Cert:\CurrentUser\Root','Cert:\LocalMachine\CA','Cert:\CurrentUser\CA'); foreach ($store in $stores) { Get-ChildItem $store -ErrorAction SilentlyContinue | Where-Object { $_.NotAfter -gt (Get-Date) } | ForEach-Object { $base64 = [Convert]::ToBase64String($_.RawData, [Base64FormattingOptions]::InsertLineBreaks); Add-Content -LiteralPath $bundle -Value '-----BEGIN CERTIFICATE-----' -Encoding ascii; Add-Content -LiteralPath $bundle -Value $base64 -Encoding ascii; Add-Content -LiteralPath $bundle -Value '-----END CERTIFICATE-----' -Encoding ascii; Add-Content -LiteralPath $bundle -Value '' -Encoding ascii } }; Get-Item -LiteralPath $bundle | Select-Object FullName,Length | Format-List"

echo.
echo Certificados atualizados.
echo Reinicie o servidor da porta 9001 e teste a IA novamente.
echo.
pause
