.PHONY: help build up down restart logs shell artisan clean migrate permissions

help: ## Mostrar esta ayuda
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

build: ## Construir la imagen Docker
	docker-compose build

up: ## Iniciar el contenedor
	docker-compose up -d

down: ## Detener el contenedor
	docker-compose down

restart: down up ## Reiniciar el contenedor

logs: ## Ver logs en tiempo real
	docker-compose logs -f app

shell: ## Acceder al shell del contenedor
	docker-compose exec app bash

artisan: ## Ejecutar comando artisan (ej: make artisan cmd="migrate")
	docker-compose exec app php artisan $(cmd)

npm: ## Ejecutar npm en el contenedor (ej: make npm cmd="run dev")
	docker-compose exec app npm $(cmd)

clean: ## Limpiar cachés de Laravel
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear
	docker-compose exec app php artisan cache:clear

migrate: ## Ejecutar migraciones
	docker-compose exec app php artisan migrate

fresh: ## Reconstruir imagen desde cero
	docker-compose down
	docker-compose build --no-cache
	docker-compose up -d

permissions: ## Arreglar permisos de storage y cache
	docker-compose exec app chmod -R 777 storage bootstrap/cache

# Comandos específicos del proyecto
actualizar-clips: ## Ejecutar comando de actualizar clips
	docker-compose exec app php artisan actualizar_informacion_clips_twitch

actualizar-videos: ## Ejecutar comando de actualizar videos
	docker-compose exec app php artisan actualizar_informacion_videos_twitch

crear-media: ## Ejecutar comando de crear media
	docker-compose exec app php artisan crear_media_twitch

publicar-media: ## Ejecutar comando de publicar media
	docker-compose exec app php artisan publicar_media_twitch
