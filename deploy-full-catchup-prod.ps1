# deploy-full-catchup-prod.ps1
# One-time script: uploads EVERY file tracked by git to the PRODUCTION server, regardless of
# which commit last touched them. This exists because deploy-to-prod.ps1 used to only diff
# against HEAD~1, silently skipping any file whose last change happened more than 1 commit ago
# if the script wasn't run after every single commit. Run this once to bring PROD back in sync
# with git HEAD, then rely on the corrected deploy-to-prod.ps1 (marker-based) going forward.
# Only PUTs files (no deletes), so untracked server folders (uploads/, images_tmp/, sessions/)
# are never touched.

$winScpPath = "C:\Users\jnahm\AppData\Local\Programs\WinSCP\WinSCP.com"
$ftpHost    = "davidnahmiasengineering.com"
$ftpUser    = "xz71g5thavyl"
$remotePath = "/public_html"
$localBase  = "c:\DNE Local"
$logFile    = "$localBase\deploy-prod.log"
$markerFile = "$localBase\.last-deploy-prod"

$files = & git -C $localBase ls-files | Sort-Object -Unique

if (-not $files) {
    Write-Host "No tracked files found. Nothing to do." -ForegroundColor Yellow
    exit
}

Write-Host "`nThis will upload ALL $($files.Count) git-tracked files to PRODUCTION," -ForegroundColor Yellow
Write-Host "overwriting whatever is currently there, to fix accumulated deployment drift." -ForegroundColor Yellow
$confirm = Read-Host "Type YES to continue"
if ($confirm -ne "YES") {
    Write-Host "Cancelled." -ForegroundColor Yellow
    exit
}

$securePass = Read-Host -Prompt "FTP Password" -AsSecureString
$plainPass  = [Runtime.InteropServices.Marshal]::PtrToStringAuto(
                  [Runtime.InteropServices.Marshal]::SecureStringToBSTR($securePass))
Add-Type -AssemblyName System.Web
$encodedPass = [System.Web.HttpUtility]::UrlEncode($plainPass)

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
    & git -C $localBase rev-parse HEAD | Set-Content -Path $markerFile -NoNewline
    Write-Host "`nAll $($files.Count) files uploaded successfully to PRODUCTION." -ForegroundColor Green
    Write-Host "Verify at: https://davidnahmiasengineering.com/" -ForegroundColor Cyan
} else {
    Write-Host "`nUpload failed on one or more files. Check deploy-prod.log for details." -ForegroundColor Red
}
