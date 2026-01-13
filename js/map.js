/* BirraFinder - Versión PRO (Places API New & Advanced Markers)
  Optimizado para producción y ahorro de costes.
*/

let map;
let infoWindow;

// Hacemos la función global para el callback del HTML
window.initMap = async function () {
    console.log("🚀 Iniciando Mapa versión PRO...");

    // 1. Importamos las librerías modernas (Async/Await)
    const { Map } = await google.maps.importLibrary("maps");
    const { AdvancedMarkerElement, PinElement } = await google.maps.importLibrary("marker");
    const { Place, SearchNearbyRankPreference } = await google.maps.importLibrary("places");

    // 2. Configuración Inicial (Valencia)
    const valencia = { lat: 39.4699, lng: -0.3763 };

    map = new Map(document.getElementById("map"), {
        center: valencia,
        zoom: 14,
        mapId: "DEMO_MAP_ID", // NECESARIO para marcadores avanzados
        mapTypeControl: false,
        streetViewControl: false,
    });

    infoWindow = new google.maps.InfoWindow();

    // 3. Cargar tus bares de la BD
    await loadMyBars(AdvancedMarkerElement, PinElement);

    // 4. Ubicación del Usuario y Búsqueda Automática
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const pos = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                };

                // Marcador de "Yo" (Personalizado con PinElement)
                const pinYo = new PinElement({
                    background: "#4285F4",
                    borderColor: "white",
                    glyphColor: "white",
                    scale: 1.2,
                });

                new AdvancedMarkerElement({
                    map: map,
                    position: pos,
                    content: pinYo.element,
                    title: "Tu ubicación",
                });

                map.setCenter(pos);
                // Búsqueda moderna
                searchNearbyBarsNew(pos, Place, AdvancedMarkerElement);
            },
            () => {
                console.warn("Sin GPS. Usando ubicación por defecto.");
                searchNearbyBarsNew(valencia, Place, AdvancedMarkerElement);
            }
        );
    } else {
        searchNearbyBarsNew(valencia, Place, AdvancedMarkerElement);
    }

    // Exponemos la función de búsqueda manual globalmente
    window.searchLocation = async function () {
        const query = document.getElementById('map-search').value;
        if (!query) return alert("Introduce una ciudad");

        // Usamos la búsqueda de texto moderna
        const { places } = await google.maps.places.Place.searchByText({
            textQuery: query,
            fields: ['location', 'displayName'], // Solo pagamos por esto
            isOpenNow: false,
        });

        if (places.length > 0) {
            const { location } = places[0];
            map.setCenter(location);
            map.setZoom(14);
            searchNearbyBarsNew(location, Place, AdvancedMarkerElement);
        } else {
            alert("No se encontró el lugar.");
        }
    };
};

// ## FUNCIÓN 1: CARGAR MIS BARES (BD) ##
async function loadMyBars(AdvancedMarkerElement, PinElement) {
    try {
        const response = await fetch('api/get_bars.php');
        if (!response.ok) return;

        const bars = await response.json();

        bars.forEach(bar => {
            if (bar.lat && bar.lng) {
                // Pin Rojo para mis bares guardados
                const pinGuardado = new PinElement({
                    background: "#DB4437", // Rojo Google
                    borderColor: "#b91c1c",
                    glyphColor: "white",
                });

                const marker = new AdvancedMarkerElement({
                    map: map,
                    position: { lat: parseFloat(bar.lat), lng: parseFloat(bar.lng) },
                    title: bar.nombre,
                    content: pinGuardado.element, // Usamos el elemento visual nuevo
                });

                const contentString = `
                    <div style="color:black; padding:5px;">
                        <h3 style="margin:0;">${bar.nombre}</h3>
                        <p>${bar.comentario || ''}</p>
                        <span style="font-size:0.8rem; background:#eee; padding:2px 5px; border-radius:4px;">${bar.estado}</span>
                    </div>`;

                marker.addListener("click", () => {
                    infoWindow.setContent(contentString);
                    infoWindow.open(map, marker);
                });
            }
        });
    } catch (e) {
        console.error("Error cargando BD:", e);
    }
}

// ## FUNCIÓN 2: BUSCAR BARES CERCANOS (API NUEVA) ##
async function searchNearbyBarsNew(location, Place, AdvancedMarkerElement) {
    console.log("🔎 Buscando bares con Places API (New)...");

    const list = document.getElementById('nearby-list-grid');
    if (list) list.innerHTML = '<div style="text-align:center"><i class="fa-solid fa-spinner fa-spin"></i> Cargando...</div>';

    // Definir el centro. La API nueva prefiere LatLngBounds o un círculo.
    // Pero 'searchNearby' funciona bien con locationRestriction.

    const request = {
        fields: ['displayName', 'location', 'formattedAddress', 'rating'], // AHORRO: Solo pedimos esto
        locationRestriction: {
            center: location,
            radius: 1000, // 1km
        },
        includedPrimaryTypes: ['bar', 'night_club', 'pub'], // Tipos modernos
        maxResultCount: 10, // Limitamos para no saturar
    };

    try {
        const { places } = await Place.searchNearby(request);

        if (list) list.innerHTML = '';

        if (places.length > 0) {
            places.forEach(place => {
                // Crear Marcador Avanzado (Azul para nuevos)
                const pinNuevo = new google.maps.marker.PinElement({
                    background: "#10B981", // Verde/Azulado
                    borderColor: "#047857",
                    glyphColor: "white",
                    glyph: "+", // Icono de texto dentro del pin
                });

                const marker = new AdvancedMarkerElement({
                    map: map,
                    position: place.location,
                    title: place.displayName,
                    content: pinNuevo.element,
                });

                // Crear tarjeta en la lista
                if (list) {
                    const name = place.displayName;
                    const address = place.formattedAddress;
                    // Escapamos comillas
                    const safeName = name ? name.replace(/'/g, "\\'") : "";
                    const safeAddress = address ? address.replace(/'/g, "\\'") : "";

                    const card = document.createElement('div');
                    card.className = 'bar-card';
                    card.innerHTML = `
                        <div class="bar-header">
                            <h4 style="margin:0; font-size:1rem;">${name}</h4>
                            <button class="btn-primary" style="padding:5px;" 
                                onclick="openAddModal('${safeName}', '${safeAddress}', ${place.location.lat()}, ${place.location.lng()})">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                        <p style="font-size:0.8rem; color:#666;">${address}</p>
                        <small>⭐ ${place.rating || 'N/A'}</small>
                    `;
                    list.appendChild(card);
                }

                // Evento Click en el marcador
                marker.addListener("click", () => {
                    const name = place.displayName;
                    const address = place.formattedAddress;
                    const safeName = name ? name.replace(/'/g, "\\'") : "";
                    const safeAddress = address ? address.replace(/'/g, "\\'") : "";

                    infoWindow.setContent(`
                        <div style="color:black; padding:5px; max-width:200px;">
                            <b>${name}</b><br>
                            <p style="font-size:12px;">${address}</p>
                            <button style="background:#10B981; color:white; border:none; padding:5px; width:100%; border-radius:4px; cursor:pointer;" 
                                onclick="openAddModal('${safeName}', '${safeAddress}', ${place.location.lat()}, ${place.location.lng()})">
                                Guardar Bar
                            </button>
                        </div>
                    `);
                    infoWindow.open(map, marker);
                });
            });
        } else {
            if (list) list.innerHTML = '<p>No se encontraron bares cerca.</p>';
        }
    } catch (error) {
        console.error("Error buscando bares:", error);
        if (list) list.innerHTML = '<p>Error buscando bares. Verifica la consola.</p>';
    }
}
// ## FUNCIÓN 3: ABRIR MODAL "AÑADIR" DESDE EL MAPA ##
window.openAddModal = function(name, address, lat, lng) {
    console.log("Abrir modal desde mapa:", name, lat, lng);
    
    const modal = document.getElementById('addModal');
    if(!modal) return console.error("No se encontró el modal 'addModal'");

    // Configurar Título y Acción
    document.getElementById('modalTitle').innerText = 'Guardar ' + (name || 'Bar');
    document.getElementById('formAccion').value = 'agregar';
    document.getElementById('barId').value = ''; // Nuevo

    // Pre-llenar datos
    document.getElementById('nombre').value = name || '';
    document.getElementById('direccion').value = address || '';
    
    // Coordenadas (Hidden inputs)
    document.getElementById('lat').value = lat || '';
    document.getElementById('lng').value = lng || '';

    // Resetear resto
    document.getElementById('estado').value = 'pendiente';
    document.getElementById('puntuacion').value = '5';
    document.getElementById('comentario').value = '';

    // Mostrar
    modal.style.display = 'flex';
};
