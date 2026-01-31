#!/bin/bash

# MyRVM Global Sync Script
# Deskripsi: Melakukan push otomatis ke 3 repositori (Edge, Server, dan Root)

# Warna untuk output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Ambil pesan commit dari argumen, jika kosong gunakan default
COMMIT_MSG=${1:-"Sync all repositories: $(date +'%Y-%m-%d %H:%M:%S')"}

echo -e "${BLUE}=== Starting MyRVM Global Sync ===${NC}"
echo -e "${YELLOW}Commit Message: $COMMIT_MSG${NC}\n"

# 1. Sync MyRVM-Edge
echo -e "${GREEN}>>> Syncing MyRVM-Edge...${NC}"
cd MyRVM-Edge
git add .
git commit -m "$COMMIT_MSG"
git push origin master
cd ..

echo -e "\n${GREEN}>>> Syncing MyRVM-Server...${NC}"
# 2. Sync MyRVM-Server
cd MyRVM-Server
git add .
git commit -m "$COMMIT_MSG"
git push origin master
cd ..

echo -e "\n${GREEN}>>> Syncing MyRVM1 (Root)...${NC}"
# 3. Sync Root Repo
git add .
git commit -m "$COMMIT_MSG"
git push origin master

echo -e "\n${BLUE}=== All Repositories Synced Successfully! ===${NC}"
