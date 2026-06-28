# deploy-prod-force.ps1
# Force-uploads specific files to PRODUCTION. Use when deploy-to-prod.ps1 is incomplete.

$winScpPath = "C:\Users\jnahm\AppData\Local\Programs\WinSCP\WinSCP.com"
$ftpHost    = "davidnahmiasengineering.com"
$ftpUser    = "xz71g5thavyl"
$remotePath = "/public_html"
$localBase  = "c:\DNE Local"
$logFile    = "$localBase\deploy-prod-force.log"

$files = @(
    "projects.php",
    "meetings.php",
    "js/script.js",
    "css/style.css",
    "send_email.php",
    "get_project_details.php"
)

$securePass = Read-Host -Prompt "FTP Password" -AsSecureString
$plainPass  = [Runtime.InteropServices.Marshal]::PtrToStringAuto(
                  [Runtime.InteropServices.Marshal]::SecureStringToBSTR($securePass))
Add-Type -AssemblyName System.Web
$encodedPass = [System.Web.HttpUtility]::UrlEncode($plainPass)

Write-Host "`nFiles to upload to PRODUCTION:" -ForegroundColor Cyan
$files | ForEach-Object { Write-Host "  $_" -ForegroundColor White }
Write-Host ""
Write-Host "WARNING: This will overwrite files on the live production server." -ForegroundColor Yellow
$confirm = Read-Host "Type YES to continue"
if ($confirm -ne "YES") {
    Write-Host "Cancelled." -ForegroundColor Yellow
    exit
}

$lines = @(
    "option batch abort",
    "option confirm off",
    "open ftp://${ftpUser}:${encodedPass}@${ftpHost}/",
    ""
)
foreach ($file in $files) {
    $local  = Join-Path $localBase $file
    $remote = $remotePath + "/" + $file.Replace("\", "/")
    $lines += "put -nopermissions -transfer=binary `"$local`" `"$remote`""
}
$lines += ""; $lines += "exit"

$tmpScript = [System.IO.Path]::GetTempFileName() + ".winscp"
[System.IO.File]::WriteAllText($tmpScript, ($lines -join "`n"), [System.Text.Encoding]::ASCII)

Write-Host "`nConnecting to $ftpHost ..." -ForegroundColor Cyan
& $winScpPath /script=$tmpScript /log=$logFile
if ($LASTEXITCODE -eq 0) {
    Write-Host "`nAll done. Verify at: https://davidnahmiasengineering.com/" -ForegroundColor Green
} else {
    Write-Host "`nUpload failed. Check deploy-prod-force.log for details." -ForegroundColor Red
}
Remove-Item $tmpScript -ErrorAction SilentlyContinue
