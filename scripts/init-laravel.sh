#!/usr/bin/env sh
set -eu

if [ -f artisan ]; then
  echo "Laravel ja existe nesta pasta."
  exit 0
fi

TMP_DIR=".laravel-bootstrap-tmp"
BACKUP_DIR=".backend-custom-backup"

rm -rf "$TMP_DIR" "$BACKUP_DIR"
mkdir -p "$BACKUP_DIR"

for path in README.md docker-compose.yml openapi docs scripts; do
  if [ -e "$path" ]; then
    cp -R "$path" "$BACKUP_DIR/$path"
  fi
done

docker run --rm \
  -u "$(id -u):$(id -g)" \
  -v "$(pwd):/workspace" \
  -w /workspace \
  composer:2 \
  composer create-project laravel/laravel "$TMP_DIR"

(cd "$TMP_DIR" && tar cf - .) | tar xf -

for path in README.md docker-compose.yml openapi docs scripts; do
  if [ -e "$BACKUP_DIR/$path" ]; then
    rm -rf "$path"
    cp -R "$BACKUP_DIR/$path" "$path"
  fi
done

rm -rf "$TMP_DIR" "$BACKUP_DIR"

echo "Laravel criado com sucesso na pasta backend/."
