#!/usr/bin/env bash
set -e

TAG="${1:-dev}"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DIST_DIR="$SCRIPT_DIR/../dist"

rm -rf "$DIST_DIR"
mkdir -p "$DIST_DIR"

TARGETS=(
    "linux amd64 invoice-pdf tar.gz"
    "linux arm64 invoice-pdf tar.gz"
    "darwin amd64 invoice-pdf tar.gz"
    "darwin arm64 invoice-pdf tar.gz"
    "windows amd64 invoice-pdf.exe zip"
    "windows arm64 invoice-pdf.exe zip"
)

for target in "${TARGETS[@]}"; do
    read -r GOOS GOARCH BINARY EXTENSION <<< "$target"
    ARCHIVE="invoice-pdf_${GOOS}_${GOARCH}.${EXTENSION}"
    BUILD_DIR="$DIST_DIR/build_${GOOS}_${GOARCH}"

    echo "--> Building $GOOS/$GOARCH"
    mkdir -p "$BUILD_DIR"

    (cd "$SCRIPT_DIR" && CGO_ENABLED=0 GOOS=$GOOS GOARCH=$GOARCH go build -trimpath -ldflags="-s -w -X main.Version=$TAG" -o "$BUILD_DIR/$BINARY" .)

    if [[ "$EXTENSION" == "zip" ]]; then
        (cd "$BUILD_DIR" && zip -q -9 "$DIST_DIR/$ARCHIVE" "$BINARY")
    else
        tar -czf "$DIST_DIR/$ARCHIVE" -C "$BUILD_DIR" "$BINARY"
    fi

    rm -rf "$BUILD_DIR"
done

ls -lh "$DIST_DIR"
echo "Upload with: gh release upload $TAG dist/*"
