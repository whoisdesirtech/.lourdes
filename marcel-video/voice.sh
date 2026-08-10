#!/bin/bash
# Regenerate narration with OpenAI TTS and rebuild the promo.
# Requires credits at https://platform.openai.com/settings/organization/billing/
set -e
export PATH="$HOME/.npm-global/bin:$PATH"
cd "$(dirname "$0")"
export NODE_PATH="$HOME/.npm-global/lib/node_modules"
node tts-openai.js
export PATH="/opt/homebrew/bin:$PATH"
bash build2.sh
echo "DONE — open marcel-ai-promo-live.mp4"
