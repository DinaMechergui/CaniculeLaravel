(() => {
    const element = document.getElementById('public-outage-map');
    if (!element || !window.L || !window.publicOutageMapUrl) return;
    const map = L.map(element).setView([34, 9], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    }).addTo(map);
    const cuts = L.markerClusterGroup().addTo(map);
    const impacts = L.layerGroup().addTo(map);
    L.control.layers(null, { Coupures: cuts, Impacts: impacts }, { collapsed: true }).addTo(map);
    const escape = (value) => String(value ?? '—').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;',
    })[char]);
    const statuses = { en_cours: 'En cours', prevue: 'Prévue' };
    const categories = { hopital: 'Hôpital', ecole: 'École', commerce: 'Commerce', residence: 'Résidence', autre: 'Autre' };

    function popup(cut) {
        const interventions = cut.interventions.length
            ? `<ul class="mb-0">${cut.interventions.map((item) => `<li>${escape(item.equipe)} · ${escape(item.date)} · ${escape(statuses[item.statut] || item.statut)}</li>`).join('')}</ul>`
            : 'Aucune intervention enregistrée.';
        return `<strong>${escape(cut.quartier)} · ${escape(cut.type)}</strong>
            <div>Statut : ${escape(statuses[cut.statut])}</div><div>Début : ${escape(cut.date_debut)}</div>
            <div>Fin : ${escape(cut.date_fin || 'Non renseignée')}</div>
            <div>Personnes affectées : ${escape(cut.personnes_affectees)}</div>
            <div>Interventions : ${escape(cut.interventions_count)}</div><div class="small mt-1">${interventions}</div>
            <a class="btn btn-sm btn-primary mt-2" href="${escape(cut.detail_url)}">Consulter le détail</a>`;
    }

    async function load() {
        const query = new URLSearchParams(new FormData(document.getElementById('public-map-filters')));
        const error = document.getElementById('public-map-error');
        error.classList.add('d-none');
        try {
            const response = await fetch(`${window.publicOutageMapUrl}?${query}`, { headers: { Accept: 'application/json' } });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'La carte est momentanément indisponible.');
            cuts.clearLayers();
            impacts.clearLayers();
            const colors = { en_cours: '#dc3545', prevue: '#fd7e14' };
            data.coupures.forEach((cut) => {
                if (cut.latitude === null || cut.longitude === null) return;
                L.circleMarker([cut.latitude, cut.longitude], {
                    radius: 9, color: '#fff', weight: 2, fillColor: colors[cut.statut], fillOpacity: 0.95,
                }).bindPopup(popup(cut)).addTo(cuts);
            });
            data.impacts.forEach((impact) => {
                const colorsByGravity = { moyen: '#0dcaf0', eleve: '#fd7e14', critique: '#dc3545' };
                const symbols = { hopital: 'H', ecole: 'E', commerce: 'C', residence: 'R', autre: 'A' };
                const icon = L.divIcon({
                    className: '',
                    html: `<span style="width:26px;height:26px;border:2px solid #fff;border-radius:50%;box-shadow:0 1px 5px #444;background:${colorsByGravity[impact.niveau_gravite] || '#6c757d'};color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">${symbols[impact.categorie] || 'A'}</span>`,
                    iconSize: [26, 26],
                    iconAnchor: [13, 13],
                });
                const marker = L.marker([impact.latitude, impact.longitude], { icon });
                marker.bindPopup(`<strong>${escape(impact.nom_structure)}</strong><div>${escape(categories[impact.categorie] || impact.categorie)} · ${escape(impact.coupure)}</div>
                    <div>Personnes affectées : ${escape(impact.personnes_affectees)}</div>
                    <div>Gravité : ${escape(impact.niveau_gravite)} · Statut : ${escape(impact.statut)}</div>
                    ${impact.mesures_urgence ? `<div>Mesures : ${escape(impact.mesures_urgence)}</div>` : ''}`).addTo(impacts);
            });
            const exactCuts = data.coupures.filter((cut) => cut.latitude !== null && cut.longitude !== null);
            const empty = document.getElementById('public-map-empty');
            empty.classList.toggle('d-none', exactCuts.length > 0);
            const list = document.getElementById('public-map-list');
            list.replaceChildren();
            exactCuts.forEach((cut) => {
                const article = document.createElement('article');
                article.className = 'pf-card';
                const body = document.createElement('div');
                body.className = 'pf-card-body';
                const title = document.createElement('h4');
                title.textContent = `${cut.quartier} · ${cut.type}`;
                const details = document.createElement('p');
                details.textContent = `${statuses[cut.statut]} · ${cut.date_debut} · ${cut.personnes_affectees} personne(s) affectée(s)`;
                const link = document.createElement('a');
                link.className = 'pf-link';
                link.href = cut.detail_url;
                link.textContent = 'Détail de la coupure';
                body.append(title, details, link);
                article.append(body);
                list.append(article);
            });
        } catch (exception) {
            error.textContent = `${exception.message} Vérifiez votre connexion et réessayez.`;
            error.classList.remove('d-none');
        }
    }

    document.getElementById('public-map-filters').addEventListener('submit', (event) => {
        event.preventDefault();
        load();
    });
    document.getElementById('public-map-filters').addEventListener('reset', () => setTimeout(load, 0));
    setTimeout(() => map.invalidateSize(), 150);
    load();
})();
