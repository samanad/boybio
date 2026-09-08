#!/bin/bash
set -euo pipefail

SITE_DIR="$(cd "$(dirname "$0")" && pwd)"
BOYBIOS_DIR="$(cd "$SITE_DIR/.." && pwd)"
ANDROID_DIR="$BOYBIOS_DIR/android"
SDK_DIR="${ANDROID_SDK_ROOT:-$BOYBIOS_DIR/.android-sdk}"
GRADLE_DIR="$BOYBIOS_DIR/.gradle-dist/gradle-8.9"
LOG="$SITE_DIR/logs/build.log"
OUT_DIR="$SITE_DIR/downloads"
STATUS="$SITE_DIR/logs/status.json"
LOCK="$SITE_DIR/logs/build.lock"
CMDLINE_ZIP_URL="https://dl.google.com/android/repository/commandlinetools-linux-11076708_latest.zip"
GRADLE_ZIP_URL="https://services.gradle.org/distributions/gradle-8.9-bin.zip"

mkdir -p "$SITE_DIR/logs" "$OUT_DIR" "$SDK_DIR" "$(dirname "$GRADLE_DIR")"
export GRADLE_USER_HOME="$BOYBIOS_DIR/.gradle-home"

exec 9>"$SITE_DIR/logs/build.flock"
if ! flock -n 9; then
  echo "Another build is already running"
  exit 0
fi

log() { echo "[$(date -Is)] $*"; }

write_status() {
  local state="$1"
  local msg="$2"
  printf '{"state":"%s","message":"%s","updated":"%s"}\n' "$state" "$msg" "$(date -Is)" > "$STATUS"
}

ensure_swap() {
  local swap_kb
  swap_kb="$(awk '/SwapTotal/{print $2}' /proc/meminfo)"
  if [ "${swap_kb:-0}" -ge 1000000 ]; then
    log "Swap already present (${swap_kb} kB)"
    return
  fi
  if [ "$(id -u)" -ne 0 ]; then
    log "WARNING: 4GB RAM needs ~2G swap. As root run:"
    log "  fallocate -l 2G /swapfile-boybio && chmod 600 /swapfile-boybio && mkswap /swapfile-boybio && swapon /swapfile-boybio"
    return
  fi
  log "Creating 2G swap so Gradle does not kill the server"
  if [ ! -f /swapfile-boybio ]; then
    fallocate -l 2G /swapfile-boybio || dd if=/dev/zero of=/swapfile-boybio bs=1M count=2048
    chmod 600 /swapfile-boybio
    mkswap /swapfile-boybio
  fi
  swapon /swapfile-boybio || true
}

write_status "running" "Starting build"
: > "$LOG"
log "cloub APK build on $(hostname) (low-memory mode)"
rm -f "$LOCK"
echo "$$" > "$LOCK"

if ! command -v java >/dev/null; then
  log "ERROR: install OpenJDK 17: apt install -y openjdk-17-jdk-headless unzip wget"
  write_status "error" "Java is not installed"
  exit 1
fi

ensure_swap
pkill -f gradle || true
pkill -f kotlin-daemon || true

export JAVA_HOME="${JAVA_HOME:-$(dirname "$(dirname "$(readlink -f "$(command -v java)")")")}"
export ANDROID_SDK_ROOT="$SDK_DIR"
export ANDROID_HOME="$SDK_DIR"

if [ ! -x "$SDK_DIR/cmdline-tools/latest/bin/sdkmanager" ]; then
  log "Downloading Android command-line tools"
  tmp="$(mktemp -d)"
  wget -qO "$tmp/cmdline.zip" "$CMDLINE_ZIP_URL"
  unzip -q "$tmp/cmdline.zip" -d "$tmp"
  mkdir -p "$SDK_DIR/cmdline-tools"
  rm -rf "$SDK_DIR/cmdline-tools/latest"
  mv "$tmp/cmdline-tools" "$SDK_DIR/cmdline-tools/latest"
  rm -rf "$tmp"
fi

SDKMANAGER="$SDK_DIR/cmdline-tools/latest/bin/sdkmanager"
mkdir -p "$SDK_DIR/licenses"
echo "24333f8a63b6825ea9c5514f83c2829b004d1fee" > "$SDK_DIR/licenses/android-sdk-license"
echo "84831b9409646161da1d697564849ea3" > "$SDK_DIR/licenses/android-sdk-preview-license"
log "Installing Android platform 34 (smaller than 35)"
"$SDKMANAGER" --sdk_root="$SDK_DIR" "platforms;android-34" "build-tools;34.0.0" "platform-tools"

if [ ! -x "$GRADLE_DIR/bin/gradle" ]; then
  log "Downloading Gradle 8.9"
  tmp="$(mktemp -d)"
  wget -qO "$tmp/gradle.zip" "$GRADLE_ZIP_URL"
  unzip -q "$tmp/gradle.zip" -d "$BOYBIOS_DIR/.gradle-dist"
  rm -rf "$tmp"
fi

cat > "$ANDROID_DIR/local.properties" <<EOF
sdk.dir=$SDK_DIR
EOF

log "Compiling APK with 512MB Java heap (slow but safe on 4GB)"
cd "$ANDROID_DIR"
nice -n 15 "$GRADLE_DIR/bin/gradle" --no-daemon --max-workers=1 assembleDebug

APK="$(find "$ANDROID_DIR/app/build/outputs/apk/debug" -name "*.apk" | head -n 1)"
if [ -z "$APK" ]; then
  write_status "error" "APK was not produced"
  rm -f "$LOCK"
  exit 1
fi

cp -f "$APK" "$OUT_DIR/cloub.apk"
cp -f "$APK" "$OUT_DIR/boybios.apk"
chmod 644 "$OUT_DIR/cloub.apk" "$OUT_DIR/boybios.apk"
log "APK ready: $OUT_DIR/cloub.apk"
write_status "ok" "APK ready"
rm -f "$LOCK"
