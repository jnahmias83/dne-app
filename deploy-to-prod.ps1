# deploy-to-prod.ps1
# Uploads files changed since last prod state to the PRODUCTION server (root folder).
# Only run this AFTER testing on the test server and merging dev into prod.

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

# Files to upload (modified/added since last prod state)
$files = & git -C $localBase diff "HEAD~1" --name-only --diff-filter=d | Sort-Object -Unique

# Files to delete from server (deleted since last prod state)
$deletedFiles = & git -C $localBase diff "HEAD~1" --name-only --diff-filter=D | Sort-Object -Unique

if (-not $files -and -not $deletedFiles) {
    Write-Host "No files found. Nothing to do." -ForegroundColor Yellow
    exit
}

if ($files) {
    Write-Host "`nFiles to upload to PRODUCTION server:" -ForegroundColor Cyan
    $files | ForEach-Object { Write-Host "  $_" -ForegroundColor White }
}
if ($deletedFiles) {
    Write-Host "`nFiles to DELETE from PRODUCTION server:" -ForegroundColor Yellow
    $deletedFiles | ForEach-Object { Write-Host "  $_" -ForegroundColor Red }
}
Write-Host ""
Write-Host "WARNING: This will overwrite files on the live production server." -ForegroundColor Yellow
$confirm = Read-Host "Type YES to continue"
if ($confirm -ne "YES") {
    Write-Host "Cancelled." -ForegroundColor Yellow
    exit
}

$uploadOk = $true

# --- STEP 1: Uploads (batch abort = fail on upload error) ---
if ($files) {
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
    if ($LASTEXITCODE -ne 0) { $uploadOk = $false }
    Remove-Item $tmpScript -ErrorAction SilentlyContinue
}

# --- STEP 2: Deletes (batch continue = ignore if file already gone) ---
if ($deletedFiles) {
    $lines = @(
        "option batch continue",
        "option confirm off",
        "open ftp://${ftpUser}:${encodedPass}@${ftpHost}/",
        ""
    )
    foreach ($file in $deletedFiles) {
        $remote = $remotePath + "/" + $file.Replace("\", "/")
        $lines += "rm `"$remote`""
    }
    $lines += ""; $lines += "exit"

    $tmpScript = [System.IO.Path]::GetTempFileName() + ".winscp"
    [System.IO.File]::WriteAllText($tmpScript, ($lines -join "`n"), [System.Text.Encoding]::ASCII)

    Write-Host "Deleting removed files from PRODUCTION..." -ForegroundColor Cyan
    & $winScpPath /script=$tmpScript /log=$logFile
    Remove-Item $tmpScript -ErrorAction SilentlyContinue
}

if ($uploadOk) {
    Write-Host "`nAll done. Verify at: https://davidnahmiasengineering.com/" -ForegroundColor Green
} else {
    Write-Host "`nUpload failed. Check deploy-prod.log for details." -ForegroundColor Red
}
