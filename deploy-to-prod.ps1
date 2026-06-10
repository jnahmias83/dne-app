# deploy-to-prod.ps1
# Uploads files changed in the last git commit to the PRODUCTION server (root folder).
# Only run this AFTER testing on the test server and merging dev into main.

$winScpPath = "C:\Users\jnahm\AppData\Local\Programs\WinSCP\WinSCP.com"
$ftpHost    = "davidnahmiasengineering.com"
$ftpUser    = "xz71g5thavyl"
$remotePath = "/public_html"
$localBase  = "c:\DNE Local"
$logFile    = "$localBase\deploy-prod.log"

# Prompt for password (never stored)
$securePass = Read-Host -Prompt "FTP Password" -AsSecureString
$plainPass  = [Runtime.InteropServices.Marshal]::PtrToStringAuto(
                  [Runtime.InteropServices.Marshal]::SecureStringToBSTR($securePass))
Add-Type -AssemblyName System.Web
$encodedPass = [System.Web.HttpUtility]::UrlEncode($plainPass)

# Get files changed in the last commit
$files = & git -C $localBase diff "HEAD~1" --name-only | Sort-Object -Unique

if (-not $files) {
    Write-Host "No files found from last commit. Nothing to upload." -ForegroundColor Yellow
    exit
}

Write-Host "`nFiles to upload to PRODUCTION server:" -ForegroundColor Cyan
$files | ForEach-Object { Write-Host "  $_" -ForegroundColor White }
Write-Host ""
Write-Host "WARNING: This will overwrite files on the live production server." -ForegroundColor Yellow
$confirm = Read-Host "Type YES to continue"
if ($confirm -ne "YES") {
    Write-Host "Cancelled." -ForegroundColor Yellow
    exit
}

# Build WinSCP script
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
$lines += ""
$lines += "exit"

$tmpScript = [System.IO.Path]::GetTempFileName() + ".winscp"
[System.IO.File]::WriteAllText($tmpScript, ($lines -join "`n"), [System.Text.Encoding]::ASCII)

Write-Host "`nConnecting to $ftpHost ..." -ForegroundColor Cyan
& $winScpPath /script=$tmpScript /log=$logFile
$exitCode = $LASTEXITCODE

Remove-Item $tmpScript -ErrorAction SilentlyContinue

if ($exitCode -eq 0) {
    Write-Host "`nAll files uploaded successfully to PRODUCTION server." -ForegroundColor Green
    Write-Host "Verify at: https://davidnahmiasengineering.com/" -ForegroundColor Cyan
} else {
    Write-Host "`nOne or more files failed. Check deploy-prod.log for details." -ForegroundColor Red
}
