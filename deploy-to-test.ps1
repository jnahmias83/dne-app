# deploy-to-test.ps1
# Uploads files changed since the last successful test deployment to the TEST server (Development folder).
# Run this immediately after committing to the dev branch.

$winScpPath = "C:\Users\jnahm\AppData\Local\Programs\WinSCP\WinSCP.com"
$ftpHost    = "davidnahmiasengineering.com"
$ftpUser    = "xz71g5thavyl"
$remotePath = "/public_html/Development"
$localBase  = "c:\DNE Local"
$logFile    = "$localBase\deploy-test.log"
$markerFile = "$localBase\.last-deploy-test"

# Prompt for password (never stored)
$securePass = Read-Host -Prompt "FTP Password" -AsSecureString
$plainPass  = [Runtime.InteropServices.Marshal]::PtrToStringAuto(
                  [Runtime.InteropServices.Marshal]::SecureStringToBSTR($securePass))
Add-Type -AssemblyName System.Web
$encodedPass = [System.Web.HttpUtility]::UrlEncode($plainPass)

# Compare against the last commit actually deployed (fallback to HEAD~7 on first run)
if (Test-Path $markerFile) {
    $sinceCommit = (Get-Content $markerFile -Raw).Trim()
} else {
    $sinceCommit = "HEAD~7"
}

# Files to upload (modified/added since last successful test deployment)
$files = & git -C $localBase diff $sinceCommit --name-only --diff-filter=d | Sort-Object -Unique

# Files to delete from server (deleted since last successful test deployment)
$deletedFiles = & git -C $localBase diff $sinceCommit --name-only --diff-filter=D | Sort-Object -Unique

if (-not $files -and -not $deletedFiles) {
    Write-Host "No files found from last commits. Nothing to do." -ForegroundColor Yellow
    exit
}

if ($files) {
    Write-Host "`nFiles to upload to TEST server:" -ForegroundColor Cyan
    $files | ForEach-Object { Write-Host "  $_" -ForegroundColor White }
}
if ($deletedFiles) {
    Write-Host "`nFiles to DELETE from TEST server:" -ForegroundColor Yellow
    $deletedFiles | ForEach-Object { Write-Host "  $_" -ForegroundColor Red }
}
Write-Host ""

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

    Write-Host "Uploading files..." -ForegroundColor Cyan
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

    Write-Host "Deleting removed files..." -ForegroundColor Cyan
    & $winScpPath /script=$tmpScript /log=$logFile
    Remove-Item $tmpScript -ErrorAction SilentlyContinue
}

if ($uploadOk) {
    & git -C $localBase rev-parse HEAD | Set-Content -Path $markerFile -NoNewline
    Write-Host "`nDone. Test at: https://davidnahmiasengineering.com/Development/" -ForegroundColor Green
} else {
    Write-Host "`nUpload failed. Check deploy-test.log for details." -ForegroundColor Red
}
