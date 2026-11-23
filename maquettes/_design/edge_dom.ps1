# usage: edge_dom.ps1 <url> <fichier_sortie> [largeur]
# lance Edge sans fenetre et enregistre le DOM final (apres les scripts) dans le fichier de sortie
param([string]$Url, [string]$Out, [int]$Width = 1300)
$exe = "C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"
$profile = Join-Path $env:TEMP ("edge_dom_" + [guid]::NewGuid().ToString("N"))
Start-Process -FilePath $exe -ArgumentList '--headless=new','--disable-gpu',"--user-data-dir=$profile","--window-size=$Width,1000",'--virtual-time-budget=6000','--dump-dom',$Url -RedirectStandardOutput $Out -Wait -NoNewWindow
Remove-Item -Recurse -Force $profile -ErrorAction SilentlyContinue
