# BirraFinder - Tu Mapa de Bares 🍻

## 📖 Descripción
**BirraFinder** es una aplicación web moderna diseñada para registrar y gestionar tus descubrimientos de bares y cervecerías. Funciona como una bitácora personal donde puedes guardar bares que quieres visitar ("Pendientes") y aquellos que ya conoces ("Visitados"), permitiéndote puntuarlos y añadir comentarios sobre tu experiencia.

El proyecto está optimizado como una **Progressive Web App (PWA)**, lo que significa que puedes instalarla en tu dispositivo móvil directamente desde el navegador para una experiencia similar a una app nativa.

## 🚀 Características
- **🔐 Autenticación Segura:** Sistema completo de Registro e Inicio de Sesión para mantener tus datos privados.
- **📊 Gestión de Estados:** Clasifica tus lugares en "Pendiente" o "Visitado".
- **⭐ Valoraciones y Reseñas:** Puntúa tu experiencia del 1 al 5 y guarda notas personales.
- **📍 Geolocalización:** Almacena la ubicación exacta (latitud/longitud) de cada bar.
- **📱 PWA (Progressive Web App):** Instalable en móviles, con manifiesto y service worker incluidos.
- **🎨 Diseño Responsivo:** Interfaz adaptada a móviles y escritorio con un diseño moderno.

## 🛠️ Tecnologías Usadas
- **Backend:** PHP
- **Base de Datos:** MySQL / MariaDB
- **Frontend:** HTML5, CSS3, JavaScript
- **Estilos:** Diseño personalizado (CSS Grid/Flexbox)

## 📂 Estructura del Directorio

```text
Proyecto-Bares/
├── app/            # Lógica de la aplicación y controladores
├── config/         # Configuración de base de datos
├── css/            # Hojas de estilo
├── img/            # Imágenes e iconos PWA
├── js/             # Lógica Frontend
├── sql/            # Scripts de base de datos
├── index.php       # Pantalla de Login/Registro
├── dashboard.php   # Panel principal del usuario
├── manifest.json   # Configuración PWA
└── service-worker.js # Service Worker para PWA
```
