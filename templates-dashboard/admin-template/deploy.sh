#!/bin/bash

# Vuexy Admin Template - Cluster Deployment Script
# This script automates the deployment of Vuexy admin template in a clustered environment

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
PROJECT_NAME="vuexy-admin-cluster"
COMPOSE_FILE="docker-compose.yml"

# Functions
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

check_requirements() {
    log_info "Checking system requirements..."
    
    # Check Docker
    if ! command -v docker &> /dev/null; then
        log_error "Docker is not installed. Please install Docker first."
        exit 1
    fi
    
    # Check Docker Compose
    if ! docker compose version &> /dev/null; then
        log_error "Docker Compose is not installed. Please install Docker Compose first."
        exit 1
    fi
    
    # Check if Docker daemon is running
    if ! docker info &> /dev/null; then
        log_error "Docker daemon is not running. Please start Docker first."
        exit 1
    fi
    
    log_success "All requirements met!"
}

build_images() {
    log_info "Building Docker images..."
    docker compose build --no-cache
    log_success "Docker images built successfully!"
}

deploy_cluster() {
    log_info "Deploying Vuexy cluster..."
    
    # Stop existing containers
    docker compose down --remove-orphans
    
    # Start the cluster
    docker compose up -d
    
    log_success "Cluster deployed successfully!"
}

check_health() {
    log_info "Checking cluster health..."
    
    # Wait for services to start
    sleep 30
    
    # Check load balancer
    if curl -f http://localhost/health &> /dev/null; then
        log_success "Load balancer is healthy!"
    else
        log_warning "Load balancer health check failed"
    fi
    
    # Check individual instances
    for i in {1..3}; do
        if docker exec vuexy-app-$i curl -f http://localhost/health &> /dev/null; then
            log_success "Vuexy instance $i is healthy!"
        else
            log_warning "Vuexy instance $i health check failed"
        fi
    done
}

show_status() {
    log_info "Cluster Status:"
    docker compose ps
    
    echo ""
    log_info "Access URLs:"
    echo "  - Main Application: http://localhost"
    echo "  - HAProxy Stats: http://localhost:8081/stats (admin/admin123)"
    echo "  - Prometheus: http://localhost:9090"
    echo "  - Grafana: http://localhost:3000 (admin/admin123)"
}

scale_service() {
    local service=$1
    local replicas=$2
    
    log_info "Scaling $service to $replicas replicas..."
    docker compose up -d --scale $service=$replicas
    log_success "Service $service scaled to $replicas replicas!"
}

cleanup() {
    log_info "Cleaning up cluster..."
    docker compose down --volumes --remove-orphans
    docker system prune -f
    log_success "Cleanup completed!"
}

show_logs() {
    local service=$1
    if [ -z "$service" ]; then
        docker compose logs -f
    else
        docker compose logs -f $service
    fi
}

# Main script
case "$1" in
    "deploy")
        check_requirements
        build_images
        deploy_cluster
        check_health
        show_status
        ;;
    "build")
        check_requirements
        build_images
        ;;
    "up")
        deploy_cluster
        ;;
    "down")
        docker compose down
        ;;
    "status")
        show_status
        ;;
    "health")
        check_health
        ;;
    "scale")
        if [ -z "$2" ] || [ -z "$3" ]; then
            log_error "Usage: $0 scale <service> <replicas>"
            exit 1
        fi
        scale_service $2 $3
        ;;
    "logs")
        show_logs $2
        ;;
    "cleanup")
        cleanup
        ;;
    "restart")
        docker compose restart
        ;;
    *)
        echo "Vuexy Admin Template - Cluster Management Script"
        echo ""
        echo "Usage: $0 {deploy|build|up|down|status|health|scale|logs|cleanup|restart}"
        echo ""
        echo "Commands:"
        echo "  deploy   - Full deployment (build + up + health check)"
        echo "  build    - Build Docker images"
        echo "  up       - Start the cluster"
        echo "  down     - Stop the cluster"
        echo "  status   - Show cluster status"
        echo "  health   - Check cluster health"
        echo "  scale    - Scale a service (usage: scale <service> <replicas>)"
        echo "  logs     - Show logs (usage: logs [service])"
        echo "  cleanup  - Clean up all resources"
        echo "  restart  - Restart all services"
        echo ""
        echo "Examples:"
        echo "  $0 deploy"
        echo "  $0 scale vuexy-app 5"
        echo "  $0 logs loadbalancer"
        exit 1
        ;;
esac