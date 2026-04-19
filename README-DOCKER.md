# TwitchClips - Docker

Proyecto Laravel con scraping (Chromium + Puppeteer) en Docker con **sincronización de archivos**.

## 🚀 Inicio Rápido

### 1. Configurar variables de entorno

```bash
cp .env.dev .env
nano .env  # Configura DB_HOST, DB_DATABASE, credenciales de APIs, etc.
```

### 2. Construir e iniciar

```bash
make build    # Construir imagen
make up       # Iniciar contenedor
make logs     # Ver logs
```

O usando el script:

```bash
./start.sh
```

### 3. Acceder

**http://localhost:8080**

---

## 📝 Sincronización de Archivos

Los archivos se **sincronizan automáticamente** entre tu sistema y el contenedor:

**✅ Sincronizados (cambios instantáneos):**
- `app/` - Modelos, Controllers, Commands
- `resources/` - Views, CSS, JS
- `routes/` - Rutas
- `config/` - Configuración
- `.env` - Variables de entorno

Editas en tu IDE → Se refleja inmediatamente en el contenedor

**❌ NO sincronizados (solo en contenedor):**
- `vendor/` - Dependencias PHP (instaladas con composer)
- `node_modules/` - Dependencias npm
- `storage/framework/` - Cache de Laravel
- `bootstrap/cache/` - Cache de configuración

---

## 🛠️ Comandos Útiles

### Básicos

```bash
make help       # Ver todos los comandos
make up         # Iniciar contenedor
make down       # Detener contenedor
make restart    # Reiniciar contenedor
make logs       # Ver logs
make shell      # Acceder al shell del contenedor
```

### Laravel

```bash
make artisan cmd="migrate"           # Ejecutar migraciones
make artisan cmd="cache:clear"       # Limpiar cache
make artisan cmd="make:controller X" # Crear controller
make clean                           # Limpiar todos los cachés
```

### NPM

```bash
make npm cmd="run dev"    # Compilar assets en modo desarrollo
make npm cmd="run build"  # Compilar assets para producción
make npm cmd="install X"  # Instalar paquete npm
```

### Comandos del proyecto

```bash
make actualizar-clips    # Actualizar clips de Twitch
make actualizar-videos   # Actualizar videos
make crear-media         # Crear media
make publicar-media      # Publicar media
```

### Mantenimiento

```bash
make fresh        # Reconstruir imagen desde cero
make permissions  # Arreglar permisos de storage
```

---

## 📦 Estructura Docker

```
.
├── Dockerfile              # Imagen con PHP, Nginx, Chromium, Node
├── docker-compose.yml      # Configuración con sincronización
├── .dockerignore           # Archivos a ignorar
├── Makefile                # Comandos útiles
├── start.sh                # Script de inicio rápido
├── .env.dev                # Plantilla de variables
└── docker/
    ├── nginx/nginx.conf    # Configuración de Nginx
    └── entrypoint.sh       # Script de inicialización
```

---

## 🔧 Workflow de Desarrollo

### Editar código

1. Abre tu editor (VS Code, PHPStorm, etc.)
2. Edita cualquier archivo PHP, Blade, JS, CSS
3. Guarda
4. Refresca el navegador → **¡Cambios visibles!**

### Instalar nuevas dependencias

**Composer:**
```bash
make shell
composer require nombre/paquete
exit
```

**NPM:**
```bash
make shell
npm install nombre-paquete
exit
```

### Si los cambios no se ven

```bash
make clean  # Limpia todos los cachés de Laravel
```

---

## 🐛 Problemas Comunes

### Error de permisos

```bash
make permissions
```

### Chromium falla

Verifica memoria compartida:
```bash
make shell
df -h /dev/shm  # Debe mostrar ~2GB
```

### Dependencias faltantes

```bash
make shell
composer install
npm install
exit
```

### Reconstruir desde cero

```bash
make fresh
```

---

## 📋 Variables de Entorno (.env)

Principales variables a configurar:

```env
# Base de datos (externa, no incluida en Docker)
DB_HOST=192.168.1.100
DB_DATABASE=twitch
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña

# APIs
GOOGLE_KEY=...
GOOGLE_ID=...
GOOGLE_SECRET=...
YOUTUBE_CLIENT_ID=...
YOUTUBE_CLIENT_SECRET=...
```

---

## 🔒 Tecnologías Incluidas

- **PHP 8.2** + extensiones (pdo_mysql, zip, bcmath, etc.)
- **Nginx** como servidor web
- **Composer 2** para dependencias PHP
- **Node.js 18.x** + npm
- **Chromium** + dependencias completas
- **Puppeteer** (vía Spatie Browsershot)
- **FFmpeg** para procesamiento multimedia

---

## 💡 Tips

- **No necesitas reconstruir** la imagen tras cambios de código
- **Solo reconstruye** si modificas Dockerfile o instalas dependencias del sistema
- **Los logs** están en `storage/logs/` (accesibles desde host)
- **Chromium** consume RAM, asegúrate de tener suficiente memoria

---

## 📤 Desplegar en Servidor

1. Sube el código a Git
2. En el servidor: clona el repositorio
3. Copia y configura `.env`
4. Ejecuta `make build && make up`

---

¿Problemas? Revisa los logs: `make logs`
