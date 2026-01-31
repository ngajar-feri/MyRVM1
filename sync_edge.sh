#!/bin/bash

# MyRVM Edge Sync Script
# Deskripsi: Melakukan push otomatis ke repositori Edge

GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m'

COMMIT_MSG=${1:-"Sync Edge: $(date +'%Y-%m-%d %H:%M:%S')"}

echo -e "${BLUE}=== Syncing MyRVM-Edge ===${NC}"
cd MyRVM-Edge
git add .
git commit -m "$COMMIT_MSG"
git push origin master
cd ..
echo -e "${GREEN}Edge Synced!${NC}"
