#!/usr/bin/env sh
set -eu
ROOT=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
find "$ROOT" -type f -name '*.php' -not -path '*/vendor/*' -exec php -l {} \;
if command -v node >/dev/null 2>&1; then
  for file in "$ROOT/public/js/app.js" "$ROOT/public/js/form-builder.js" "$ROOT/public/js/form-validation.js"; do
    node --check "$file"
  done
fi
php "$ROOT/tests/run.php"
echo "Validação concluída."
