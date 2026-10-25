#!/usr/bin/env bash
# Builds the installable theme ZIP from the committed tree.
#   bin/build.sh [ref]   (default: HEAD)  ->  dist/enfrute-inscricao-<version>.zip
set -euo pipefail
cd "$(dirname "$0")/.."
REF="${1:-HEAD}"
VERSION=$(git show "$REF:style.css" | sed -n 's/^Version:[[:space:]]*//p' | tr -d '[:space:]')
mkdir -p dist
OUT="dist/enfrute-inscricao-${VERSION}.zip"
git archive --format=zip --prefix=enfrute-inscricao/ -o "$OUT" "$REF"
if unzip -l "$OUT" | grep -E '\.git/|/docs/|\.env|\.sql|\.log$|/scss/' ; then
  echo "ERRO: arquivo indevido no pacote" >&2; exit 1
fi
for f in style.css functions.php index.php inc/sciflow-integration.php; do
  unzip -l "$OUT" | grep -q "enfrute-inscricao/$f" || { echo "ERRO: $f ausente" >&2; exit 1; }
done
( cd "$(dirname "$OUT")" && sha256sum "$(basename "$OUT")" > "$(basename "$OUT").sha256" )
echo "OK $OUT ($(unzip -l "$OUT" | tail -1 | awk '{print $2}') arquivos)"
