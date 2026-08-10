#!/bin/bash
set -e
export PATH="/opt/homebrew/bin:$PATH"
cd "$(dirname "$0")"

mkdir -p segments
rm -f segments/*.mp4 segments/list.txt

# Segment 1 — Title card: slow zoom in (6s)
ffmpeg -y -loglevel error -i shots/00-title.png -filter_complex \
  "zoompan=z='min(1.0+0.002*on,1.12)':x='iw/2-(iw/zoom/2)':y='ih/2-(ih/zoom/2)':d=180:s=1920x1080:fps=30,fade=t=in:st=0:d=0.6,fade=t=out:st=5.4:d=0.6,format=yuv420p" \
  -r 30 -c:v libx264 -preset medium -crf 18 segments/seg01.mp4

# Segment 2 — Hero page: pan down (7s)
ffmpeg -y -loglevel error -loop 1 -framerate 30 -t 7 -i shots/01-hero-full.png -vf \
  "scale=2560:-2,crop=1920:1080:x='(iw-ow)/2':y='(ih-oh)*t/7',fade=t=in:st=0:d=0.5,fade=t=out:st=6.5:d=0.5,format=yuv420p" \
  -r 30 -c:v libx264 -preset medium -crf 18 segments/seg02.mp4

# Segment 3 — Dashboard/card: slow zoom + pan right (9s)
ffmpeg -y -loglevel error -loop 1 -framerate 30 -t 9 -i shots/02-dashboard-full.png -vf \
  "scale=2560:-2,crop=1920:1080:x='(iw-ow)*t/9':y='(ih-oh)/2',fade=t=in:st=0:d=0.5,fade=t=out:st=8.5:d=0.5,format=yuv420p" \
  -r 30 -c:v libx264 -preset medium -crf 18 segments/seg03.mp4

# Segment 4 — Full page: pan down (8s)
ffmpeg -y -loglevel error -loop 1 -framerate 30 -t 8 -i shots/03-full-full.png -vf \
  "scale=2560:-2,crop=1920:1080:x='(iw-ow)/2':y='(ih-oh)*t/8',fade=t=in:st=0:d=0.5,fade=t=out:st=7.5:d=0.5,format=yuv420p" \
  -r 30 -c:v libx264 -preset medium -crf 18 segments/seg04.mp4

# Segment 5 — End card: slow zoom out (7s)
ffmpeg -y -loglevel error -i shots/04-end.png -filter_complex \
  "zoompan=z='max(1.12-0.002*on,1.0)':x='iw/2-(iw/zoom/2)':y='ih/2-(ih/zoom/2)':d=210:s=1920x1080:fps=30,fade=t=in:st=0:d=0.6,fade=t=out:st=6.4:d=0.6,format=yuv420p" \
  -r 30 -c:v libx264 -preset medium -crf 18 segments/seg05.mp4

for i in 1 2 3 4 5; do echo "file 'seg0$i.mp4'" >> segments/list.txt; done

# Concat video-only
ffmpeg -y -loglevel error -f concat -safe 0 -i segments/list.txt -c copy segments/video_concat.mp4

# Pad narration with 0.8s lead-in, mix with video
ffmpeg -y -loglevel error -i segments/video_concat.mp4 -i audio/narration.wav \
  -filter_complex "[1:a]adelay=800|800,apad[a]" \
  -map 0:v -map "[a]" -c:v copy -c:a aac -b:a 192k -shortest marcel-ai-promo.mp4

echo "=== DONE ==="
ffprobe -v error -show_entries format=duration -of csv=p=0 marcel-ai-promo.mp4
