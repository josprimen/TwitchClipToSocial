# Proyecto de Automatización de Recopilación y Publicación de Clips de Twitch en Instagram

## Descripción del Proyecto

Este proyecto está diseñado para automatizar el proceso de recopilación y publicación de clips populares de canales de Twitch en la plataforma de Instagram. La aplicación utiliza Laravel 10 y PHP 8.2, aprovechando diversas bibliotecas como Browsershot, cURL, Crawler y DataTables para lograr sus funcionalidades clave.

### Funcionalidades Principales

1. **Recopilación de Clips de Twitch:**
    - Consulta canales de Twitch y mantiene actualizada la información de los clips más populares.
    - Utiliza Browsershot para realizar peticiones a la web de Twitch, localizar y recopilar clips.

2. **Publicación Automática en Instagram:**
    - Utiliza la API Graph proporcionada por plataformas como Facebook e Instagram.
    - Realiza publicaciones que contienen automáticamente los clips de video recopilados.
    - Añade descripciones de manera automática para mejorar la calidad de las publicaciones.

### Tecnologías Principales

- **Laravel 10:** Framework de desarrollo web en PHP para la construcción del backend.
- **PHP 8.2:** Lenguaje de programación para el desarrollo de la lógica del proyecto.
- **Browsershot:** Librería para realizar peticiones y navegar por la web de Twitch.
- **cURL:** Utilizado para realizar peticiones a la API Graph de plataformas como Facebook e Instagram.
- **Crawler:** Permite tratar el HTML obtenido con Browsershot desde PHP.
- **DataTables:** Utilizado para la visualización de datos en la interfaz del proyecto.

## Instalación y Despliegue

### Requisitos Previos
- [Node.js](https://nodejs.org/) con [Puppeteer](https://developers.google.com/web/tools/puppeteer) instalado.
- [Composer](https://getcomposer.org/) para gestionar las dependencias de PHP.

### Pasos de Instalación

1. Clona el repositorio: `git clone https://github.com/tuusuario/tuproyecto.git`.
2. Instala las dependencias de PHP: `composer install`.
3. Crea el archivo de entorno (`env`) con la configuración de la base de datos.
4. Ejecuta las migraciones: `php artisan migrate`.
5. Configura las credenciales necesarias de tokens de Facebook en el archivo `.env`.

## Uso y Contribuciones

Este proyecto está orientado principalmente al backend y automatización, con una interfaz mínima necesaria para agregar nuevos canales de Twitch. Se prevé la expansión de la interfaz con tablas adicionales de información en futuras actualizaciones.


---

Este proyecto es un esfuerzo continuo para mejorar y simplificar el proceso de recopilación y publicación de contenido de Twitch en Instagram. ¡Esperamos que encuentres útil esta aplicación!
