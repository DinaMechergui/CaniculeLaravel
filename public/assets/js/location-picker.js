(() => {
    const initialize = () => {
        const wrapper = document.querySelector('[data-location-picker]');
        const mapElement = wrapper?.querySelector('#location-picker-map');
        if (!wrapper || !mapElement || !window.L) return;

        const latitude = wrapper.querySelector('[name="latitude"]');
        const longitude = wrapper.querySelector('[name="longitude"]');
        const map = L.map(mapElement).setView([34, 9], 6);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        }).addTo(map);

        let marker = null;
        const updateMarker = (lat, lng, zoom = true) => {
            if (marker) marker.setLatLng([lat, lng]);
            else marker = L.marker([lat, lng], { draggable: true }).addTo(map);
            latitude.value = Number(lat).toFixed(7);
            longitude.value = Number(lng).toFixed(7);
            marker.off('dragend');
            marker.on('dragend', (event) => {
                const position = event.target.getLatLng();
                latitude.value = position.lat.toFixed(7);
                longitude.value = position.lng.toFixed(7);
            });
            if (zoom) map.setView([lat, lng], 14);
        };

        const currentLatitude = Number(latitude.value);
        const currentLongitude = Number(longitude.value);
        if (latitude.value !== '' && longitude.value !== '' && Number.isFinite(currentLatitude) && Number.isFinite(currentLongitude)) {
            updateMarker(currentLatitude, currentLongitude);
        }

        map.on('click', (event) => updateMarker(event.latlng.lat, event.latlng.lng));
        const syncFromInputs = () => {
            const lat = Number(latitude.value);
            const lng = Number(longitude.value);
            if (latitude.value !== '' && longitude.value !== '' && Number.isFinite(lat) && Number.isFinite(lng)
                && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                updateMarker(lat, lng);
            }
        };
        latitude.addEventListener('change', syncFromInputs);
        longitude.addEventListener('change', syncFromInputs);
        wrapper.querySelector('[data-clear-location]')?.addEventListener('click', () => {
            if (marker) map.removeLayer(marker);
            marker = null;
            latitude.value = '';
            longitude.value = '';
        });
        window.setTimeout(() => map.invalidateSize(), 100);
    };

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize);
    else initialize();
})();
