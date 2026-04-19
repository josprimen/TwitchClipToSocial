#!/bin/bash
set -e

# Script de inicio rápido para TwitchClips

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

print_success() { echo -e "${GREEN}✓ $1${NC}"; }
print_warning() { echo -e "${YELLOW}⚠ $1${NC}"; }
print_error() { echo -e "${RED}✗ $1${NC}"; }

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  🚀 TwitchClips - Docker"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Verificar Docker
if ! command -v docker &> /dev/null; then
    print_error "Docker no está instalado"
    exit 1
fi

if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null 2>&1; then
    print_error "Docker Compose no está instalado"
    exit 1
fi

# Verificar .env
if [ ! -f ".env" ]; then
    print_warning "No existe archivo .env"
    
    if [ -f ".env.dev" ]; then
        echo "¿Copiar .env.dev a .env? (S/n)"
        read -r response
        if [ -z "$response" ] || [ "$response" = "s" ] || [ "$response" = "S" ]; then
            cp .env.dev .env
            print_success "Archivo .env creado"
            print_warning "IMPORTANTE: Edita .env y configura las credenciales"
            echo ""
        fi
    else
        print_error "No existe .env.dev - Crea un archivo .env manualmente"
        exit 1
    fi
fi

echo "Selecciona una opción:"
echo ""
echo "  1) Construir e iniciar (primera vez)"
echo "  2) Iniciar contenedor"
echo "  3) Ver logs"
echo "  4) Detener contenedor"
echo "  5) Reconstruir desde cero"
echo "  6) Acceder al shell"
echo ""
echo -n "Opción [1-6]: "
read -r option

case $option in
    1)
        echo ""
        print_success "Construyendo imagen..."
        docker-compose build
        
        print_success "Iniciando contenedor..."
        docker-compose up -d
        
        echo ""
        print_success "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
        print_success "  ✨ Aplicación lista"
        print_success "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
        echo ""
        echo "  🌐 http://localhost:8080"
        echo ""
        echo "  📝 Edita código en tu IDE"
        echo "     Los cambios se reflejan automáticamente"
        echo ""
        echo "  Ver logs: make logs"
        echo "  Shell: make shell"
        echo ""
        ;;
        
    2)
        echo ""
        print_success "Iniciando contenedor..."
        docker-compose up -d
        print_success "Disponible en: http://localhost:8080"
        ;;
        
    3)
        echo ""
        print_success "Logs (Ctrl+C para salir)..."
        docker-compose logs -f app
        ;;
        
    4)
        echo ""
        print_success "Deteniendo contenedor..."
        docker-compose down
        print_success "Contenedor detenido"
        ;;
        
    5)
        echo ""
        print_warning "Esto reconstruirá la imagen desde cero"
        echo -n "¿Continuar? (s/N): "
        read -r confirm
        
        if [ "$confirm" = "s" ] || [ "$confirm" = "S" ]; then
            docker-compose down
            docker-compose build --no-cache
            docker-compose up -d
            print_success "Reconstruido y iniciado"
        else
            print_success "Cancelado"
        fi
        ;;
        
    6)
        echo ""
        print_success "Accediendo al shell..."
        docker-compose exec app bash
        ;;
        
    *)
        print_error "Opción no válida"
        exit 1
        ;;
esac

echo ""
