#!/bin/bash
set -e
export PATH="/opt/homebrew/bin:$PATH"
cd "$(dirname "$0")"
rm -f segments/segA.mp4 segments/segB.mp4 segments/segC.mp4 segments/list2.txt marcel-ai-promo-live.mp4

# Title card (3s, slow zoom in)
ffmpeg -y -loglevel error -i shots/00-title.png -filter_complex \
  "zoompan=z='min(1.0+0.003*on,1.10)':x='iw/2-(iw/zoom/2)':y='ih/2-(ih/zoom/2)':d=90:s=1920x1080:fps=30,fade=t=in:st=0:d=0.5,fade=t=out:st=2.5:d=0.5,format=yuv420p" \
  -r 30 -c:v libx264 -preset medium -crf 18 segments/segA.mp4

# Real app walkthrough (keep as recorded)
cp segments/demo_raw.mp4 segments/segB.mp4

# CTA end card (4s, slow zoom out)
ffmpeg -y -loglevel error -i shots/04-end.png -filter_complex \
  "zoompan=z='max(1.10-0.002*on,1.0)':x='iw/2-(iw/zoom/2)':y='ih/2-(ih/zoom/2)':d=120:s=1920x1080:fps=30,fade=t=in:st=0:d=0.5,fade=t=out:st=3.5:d=0.5,format=yuv420p" \
  -r 30 -c:v libx264 -preset medium -crf 18 segments/segC.mp4

printf "file 'segA.mp4'\nfile 'segB.mp4'\nfile 'segC.mp4'\n" > segments/list2.txt

ffmpeg -y -loglevel error -f concat -safe 0 -i segments/list2.txt -c copy segments/video2.mp4

# Narration starts at title end (3s), pads to video end
ffmpeg -y -loglevel error -i segments/video2.mp4 -i audio/narration.wav \
  -filter_complex "[1:a]adelay=3000|3000,apad[a]" \
  -map 0:v -map "[a]" -c:v copy -c:a aac -b:a 192k -shortest marcel-ai-promo-live.mp4

echo "=== DONE ==="
ffprobe -v error -show_entries format=duration -of csv=p=0 marcel-ai-promo-live.mp4
ls -lh marcel-ai-promo-live.mp4
