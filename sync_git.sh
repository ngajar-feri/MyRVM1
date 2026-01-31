#!/bin/bash

# MyRVM Global Git Manager
# Deskripsi: Menu utama untuk sinkronisasi repositori MyRVM

# Warna
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
RED='\033[0;31m'
NC='\033[0m'

show_menu() {
    clear
    echo -e "${CYAN}=======================================${NC}"
    echo -e "${CYAN}    MyRVM GIT SYNCHRONIZATION MENU     ${NC}"
    echo -e "${CYAN}=======================================${NC}"
    echo -e "1) ${GREEN}Sync ALL (Edge + Server + Root)${NC}"
    echo -e "2) ${BLUE}Sync EDGE Only${NC}"
    echo -e "3) ${YELLOW}Sync SERVER Only${NC}"
    echo -e "4) ${NC}Sync ROOT Only${NC}"
    echo -e "q) Exit"
    echo -e "${CYAN}=======================================${NC}"
}

run_sync() {
    echo -e "\n${YELLOW}Masukkan pesan commit (kosongkan untuk default):${NC}"
    read -r msg
    
    case $1 in
        1)
            ./sync_all.sh "$msg"
            ;;
        2)
            ./sync_edge.sh "$msg"
            ;;
        3)
            ./sync_server.sh "$msg"
            ;;
        4)
            echo -e "${BLUE}=== Syncing MyRVM1 (Root) ===${NC}"
            git add .
            git commit -m "${msg:-"Sync Root: $(date +'%Y-%m-%d %H:%M:%S')"}"
            git push origin master
            ;;
    esac
    echo -e "\n${GREEN}Proses selesai. Tekan [Enter] untuk kembali ke menu...${NC}"
    read -r
}

while true; do
    show_menu
    read -p "Pilih opsi [1-4 atau q]: " opt
    case $opt in
        1|2|3|4)
            run_sync "$opt"
            ;;
        q|Q)
            echo -e "${GREEN}Sampai jumpa!${NC}"
            exit 0
            ;;
        *)
            echo -e "${RED}Opsi tidak valid!${NC}"
            sleep 1
            ;;
    esac
done
