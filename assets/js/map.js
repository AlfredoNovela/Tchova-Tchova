let map;
let origemMarker = null;
let destinoMarker = null;
let routeControl = null;

function initMap(elementId) {
    map = L.map(elementId).setView([-25.965, 32.583], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
    }).addTo(map);

    // Garantir que o mapa renderize corretamente
    setTimeout(() => map.invalidateSize(), 200);

    // Geolocalização
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(pos => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;

            origemMarker = L.marker([lat, lng], {draggable:true}).addTo(map).bindPopup('Origem').openPopup();
            document.getElementById('lat_origem').value = lat;
            document.getElementById('lng_origem').value = lng;
            document.getElementById('origem').value = 'Minha localização';

            map.setView([lat,lng], 14);

            origemMarker.on('dragend', function(e) {
                const p = e.target.getLatLng();
                document.getElementById('lat_origem').value = p.lat;
                document.getElementById('lng_origem').value = p.lng;
                updateRoute();
            });
        }, () => alert('Não foi possível obter localização. Clique no mapa para definir origem.'));
    } else {
        alert('Geolocalização não suportada pelo navegador.');
    }

    map.on('click', function(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;

        document.getElementById('lat_destino').value = lat;
        document.getElementById('lng_destino').value = lng;

        if (destinoMarker) map.removeLayer(destinoMarker);
        destinoMarker = L.marker([lat,lng], {draggable:true}).addTo(map).bindPopup('Destino').openPopup();

        destinoMarker.on('dragend', function(ev){
            const p = ev.target.getLatLng();
            document.getElementById('lat_destino').value = p.lat;
            document.getElementById('lng_destino').value = p.lng;
            updateRoute();
        });

        updateRoute();
    });

    return map;
}

function calcularDist(lat1, lng1, lat2, lng2) {
    const R = 6371;
    const dLat = (lat2-lat1)*Math.PI/180;
    const dLng = (lng2-lng1)*Math.PI/180;
    const a = Math.sin(dLat/2)**2 + Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLng/2)**2;
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R*c;
}

function updateRoute() {
    if (!origemMarker || !destinoMarker) return;

    if (routeControl) map.removeControl(routeControl);

    const o = origemMarker.getLatLng();
    const d = destinoMarker.getLatLng();

    // Desenhar a rota
    routeControl = L.Routing.control({
        waypoints: [o, d],
        lineOptions: { styles: [{ color: '#1abc9c', weight: 5 }] },
        addWaypoints: false,
        draggableWaypoints: false,
        createMarker: () => null
    }).addTo(map);

    // Calcular distância em km
    const dist = calcularDist(o.lat, o.lng, d.lat, d.lng);

    // Tempo estimado (ex: média 30 km/h)
    const tempo = Math.round(dist / 30 * 60);

    // Valor: mínimo 94 MZN + 10 MZN por km
    const valorMinimo = 94;
    const valorPorKm = 10; // ajuste se quiser outro valor
    const valor = Math.max(valorMinimo, Math.round(dist * valorPorKm * 100) / 100);

    document.getElementById('info').innerText = `Distância: ${dist.toFixed(2)} km | Tempo: ${tempo} min | Valor: ${valor.toFixed(2)} MZN`;
    document.getElementById('origem').value = 'Minha localização';
    document.getElementById('destino').value = 'Destino selecionado';
}

