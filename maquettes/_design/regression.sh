#!/bin/bash
# Filet anti-regression des maquettes.
#   bash regression.sh baseline   -> photographie TOUTES les maquettes (etat de reference)
#   bash regression.sh check      -> rephotographie et liste tout ce qui a change par rapport a la reference
# Mesure a 2 largeurs d'ecran (1300 et 600). Ne modifie aucune maquette (copie temporaire _snap_*.html, supprimee apres).
MODE="$1"; [ "$MODE" = baseline ] || [ "$MODE" = check ] || { echo "usage: regression.sh baseline|check"; exit 1; }
D="$(cd "$(dirname "$0")" && pwd)"; M="$(dirname "$D")"; OUT="$D/snap_$MODE"; rm -rf "$OUT"; mkdir -p "$OUT"
PS="$(cygpath -w "$D/edge_dom.ps1")"
cd "$M"
snap_one(){  # une page, 2 largeurs
  local f="$1" p="${1%.html}"
  { cat "$f"; echo '<script src="/maquettes/_design/mq_probe.js"></script>'; } > "_snap_$p.html"
  for w in 1300 600; do
    local tmp="$OUT/$p.$w.dom"
    powershell -NoProfile -File "$PS" "http://dne.local/maquettes/_snap_$p.html" "$(cygpath -w "$tmp")" $w 2>/dev/null
    node -e 'const s=require("fs").readFileSync(process.argv[1],"utf8");const m=s.match(/data-mq-snap="([^"]*)"/);process.stdout.write(m?m[1].replace(/&amp;/g,"&").replace(/&#10;|\n/g,"\n"):"PAS DE MESURE\n")' "$tmp" > "$OUT/$p.$w.txt"
    rm -f "$tmp"
  done
  rm -f "_snap_$p.html"
}
# 4 pages en parallele
for f in *.html; do
  case "$f" in _*) continue;; esac
  snap_one "$f" &
  while [ "$(jobs -r | wc -l)" -ge 4 ]; do sleep 0.3; done
done
wait
if [ "$MODE" = check ]; then
  B="$D/snap_baseline"; n=0
  for t in "$OUT"/*.txt; do
    b="$B/$(basename "$t")"
    if [ ! -f "$b" ]; then echo "NOUVELLE PAGE: $(basename "$t")"; continue; fi
    if ! cmp -s "$b" "$t"; then n=$((n+1)); echo "=== CHANGE: $(basename "$t" .txt)"; diff "$b" "$t" | grep '^[<>]' | head -12; fi
  done
  echo "--- pages/largeurs changees: $n"
fi
