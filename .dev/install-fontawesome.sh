#!/usr/bin/env bash
# Installs Font Awesome Free 6.5.1 into assets/vendor/fontawesome (run from the plugin root).
# Layout matches ZapPopPop_Libraries::fa_url(): assets/vendor/fontawesome/css/all.min.css + ../webfonts/*
set -euo pipefail
VER="6.5.1"
DEST="assets/vendor/fontawesome"
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT
curl -fsSL "https://registry.npmjs.org/@fortawesome/fontawesome-free/-/fontawesome-free-${VER}.tgz" -o "$TMP/fa.tgz"
tar -xzf "$TMP/fa.tgz" -C "$TMP" package/css/all.min.css package/webfonts package/LICENSE.txt
mkdir -p "$DEST/css" "$DEST/webfonts"
cp "$TMP/package/css/all.min.css" "$DEST/css/all.min.css"
cp "$TMP/package/webfonts/"* "$DEST/webfonts/"
cp "$TMP/package/LICENSE.txt" "$DEST/LICENSE.txt"
test -s "$DEST/css/all.min.css"
echo "Font Awesome $VER installed in $DEST"
