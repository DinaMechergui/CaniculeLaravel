(() => {
    const config = window.smartGridConfig;
    const mapElement = document.getElementById('smartgrid-map');
    if (!config || !mapElement || !window.L) return;

    const map = L.map(mapElement).setView([34, 9], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    }).addTo(map);
    const outageLayer = L.markerClusterGroup();
    const impactLayer = L.layerGroup();
    const interventionLayer = L.layerGroup();
    const routeLayer = L.geoJSON(null, { style: { color: '#696cff', weight: 5, opacity: 0.8 } }).addTo(map);
    outageLayer.addTo(map);
    impactLayer.addTo(map);
    const layers = L.control.layers(null, {
        'Coupures': outageLayer,
        'Impacts': impactLayer,
        'Interventions (position de la coupure)': interventionLayer,
        'Itinéraire calculé': routeLayer,
    }, { collapsed: true }).addTo(map);
    const cutsById = new Map();
    let currentCuts = [];
    let pickingOrigin = false;

    const escape = (value) => String(value ?? '—').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;',
    })[char]);
    const statusColor = (status) => ({ en_cours: '#dc3545', prevue: '#fd7e14', terminee: '#198754' })[status] || '#6c757d';
    const gravities = { moyen: 'Moyenne', eleve: 'Élevée', critique: 'Critique' };
    const categories = { hopital: 'Hôpital', ecole: 'École', commerce: 'Commerce', residence: 'Résidence', autre: 'Autre' };
    const labels = {
        resume: 'Résumé', facteurs_risque: 'Facteurs de risque', priorite_recommandee: 'Priorité recommandée',
        justification: 'Justification', actions_suggerees: 'Actions suggérées', incertitudes: 'Informations incertaines',
        impacts: 'Analyse des impacts', zones_prioritaires: 'Zones prioritaires',
        organisation_interventions: 'Organisation des interventions', explication: 'Explication',
    };

    function renderAnalysis(container, analysis) {
        container.replaceChildren();
        Object.entries(analysis || {}).forEach(([key, value]) => {
            const section = document.createElement('div');
            section.className = 'mb-3';
            const title = document.createElement('strong');
            title.textContent = labels[key] || key;
            section.append(title);
            if (Array.isArray(value)) {
                const list = document.createElement('ul');
                list.className = 'mb-0 ps-3';
                value.forEach((item) => {
                    const li = document.createElement('li');
                    li.textContent = item;
                    list.append(li);
                });
                section.append(list);
            } else {
                const text = document.createElement('p');
                text.className = 'mb-0';
                text.textContent = value;
                section.append(text);
            }
            container.append(section);
        });
    }

    function interventionMarkup(cut) {
        if (!cut.interventions.length) return '<p class="mb-0">Aucune intervention enregistrée.</p>';
        return `<strong>Interventions</strong><ol class="mb-0 ps-3">${cut.interventions.map((item) => `<li>${escape(item.date)} · ${escape(item.equipe)} (${escape(item.technicien)}) · ${escape(item.duree)} min · ${escape(item.statut)}</li>`).join('')}</ol>`;
    }

    function popupMarkup(cut) {
        return `<div class="sg-popup"><strong>${escape(cut.quartier)} · ${escape(cut.type)}</strong>
            <div>Statut : ${escape(cut.statut)}</div><div>Début : ${escape(cut.date_debut)}</div>
            <div>Fin : ${escape(cut.date_fin || 'Non renseignée')}</div>
            <div>Personnes affectées : ${escape(cut.personnes_affectees)}</div>
            <div>Interventions : ${escape(cut.interventions_count)}</div>
            <div class="mt-2">${interventionMarkup(cut)}</div>
            <a class="btn btn-sm btn-primary mt-2" href="${escape(cut.detail_url)}">Détail de la coupure</a></div>`;
    }

    function addCutMarkers(cuts) {
        outageLayer.clearLayers();
        interventionLayer.clearLayers();
        cuts.forEach((cut) => {
            if (cut.latitude === null || cut.longitude === null) return;
            const marker = L.circleMarker([cut.latitude, cut.longitude], {
                radius: 9, color: '#fff', weight: 2, fillColor: statusColor(cut.statut), fillOpacity: 0.95,
            }).bindPopup(popupMarkup(cut));
            marker.on('click', () => selectCut(cut.id));
            outageLayer.addLayer(marker);

            if (cut.interventions.length) {
                const interventionMarker = L.circleMarker([cut.latitude, cut.longitude], {
                    radius: 5, color: '#fff', weight: 1, fillColor: '#343a40', fillOpacity: 0.95,
                }).bindPopup(`<strong>Interventions liées à #${escape(cut.id)}</strong><p class="small mb-1">Marqueur au lieu précis de la coupure; aucune position d’équipe n’est enregistrée.</p>${interventionMarkup(cut)}`);
                interventionLayer.addLayer(interventionMarker);
            }
        });
    }

    function addImpactMarkers(impacts) {
        impactLayer.clearLayers();
        const color = { moyen: '#0dcaf0', eleve: '#fd7e14', critique: '#dc3545' };
        const symbols = { hopital: 'H', ecole: 'E', commerce: 'C', residence: 'R', autre: 'A' };
        impacts.forEach((impact) => {
            const severityColor = color[impact.niveau_gravite] || '#6c757d';
            const icon = L.divIcon({
                className: '',
                html: `<span class="sg-marker" title="${escape(gravities[impact.niveau_gravite] || impact.niveau_gravite)}" style="width:28px;height:28px;background:${severityColor};color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700">${symbols[impact.categorie] || 'A'}</span>`,
                iconSize: [28, 28],
                iconAnchor: [14, 14],
            });
            const marker = L.marker([impact.latitude, impact.longitude], { icon });
            marker.bindPopup(`<strong>${escape(impact.nom_structure)}</strong><div>${escape(categories[impact.categorie] || impact.categorie)}</div>
                <div>Coupure : ${escape(impact.coupure)}</div><div>Personnes affectées : ${escape(impact.personnes_affectees)}</div>
                <div>Gravité : ${escape(gravities[impact.niveau_gravite] || impact.niveau_gravite)}</div>
                <div>Statut : ${escape(impact.statut)}</div>
                ${impact.mesures_urgence ? `<div class="mt-1">Mesures : ${escape(impact.mesures_urgence)}</div>` : ''}`);
            impactLayer.addLayer(marker);
        });
    }

    function renderList(cuts) {
        const container = document.getElementById('sg-cut-list');
        container.replaceChildren();
        document.getElementById('sg-cut-count').textContent = String(cuts.length);
        if (!cuts.length) {
            container.innerHTML = '<p class="p-3 text-muted mb-0">Aucune coupure ne correspond aux filtres.</p>';
            return;
        }
        [...cuts].sort((a, b) => b.priority.score - a.priority.score).forEach((cut) => {
            const row = document.createElement('div');
            row.className = 'list-group-item';
            const check = cut.latitude === null || cut.longitude === null || cut.statut === 'terminee' ? '' :
                `<input class="form-check-input me-2 sg-route-select" type="checkbox" value="${cut.id}" aria-label="Inclure ${escape(cut.quartier)} dans l’itinéraire">`;
            row.innerHTML = `<div class="d-flex align-items-start gap-1">${check}<button class="btn btn-link text-start p-0 flex-grow-1 sg-cut-select" type="button" data-id="${cut.id}">
                <span class="fw-semibold">${escape(cut.quartier)} · ${escape(cut.type)}</span>
                <span class="small text-muted d-block">${escape(cut.date_debut)} · ${escape(cut.personnes_affectees)} personne(s)</span>
                <span class="badge ${cut.priority.level === 'critique' ? 'bg-danger' : cut.priority.level === 'elevee' ? 'bg-warning text-dark' : 'bg-info'} sg-priority">Priorité ${cut.priority.score}</span>
                <span class="badge ms-1 ${cut.statut === 'en_cours' ? 'bg-danger' : cut.statut === 'terminee' ? 'bg-success' : 'bg-warning text-dark'}">${escape(cut.statut)}</span>
                ${cut.latitude === null ? '<span class="badge bg-secondary ms-1">Position non renseignée</span>' : ''}
            </button></div>`;
            row.querySelector('.sg-cut-select').addEventListener('click', () => selectCut(cut.id));
            container.append(row);
        });
    }

    function selectCut(id) {
        const cut = cutsById.get(Number(id));
        if (!cut) return;
        const container = document.getElementById('sg-selected');
        container.replaceChildren();
        const title = document.createElement('h6');
        title.textContent = `${cut.quartier} — ${cut.type}`;
        const score = document.createElement('p');
        score.textContent = `Priorité déterministe : ${cut.priority.score}/100 (${cut.priority.level}). Gravité ${cut.priority.factors.gravite}/40, personnes ${cut.priority.factors.personnes}/25, établissement sensible ${cut.priority.factors.etablissements_sensibles}/15, durée ${cut.priority.factors.duree}/15, absence d’intervention active ${cut.priority.factors.absence_intervention_active}/5.`;
        const button = document.createElement('button');
        button.className = 'btn btn-outline-primary btn-sm';
        button.textContent = 'Analyser cette coupure avec l’IA';
        button.disabled = !config.aiConfigured;
        const result = document.createElement('div');
        result.className = 'mt-3';
        button.addEventListener('click', async () => {
            button.disabled = true;
            result.textContent = 'Analyse en cours…';
            try {
                const response = await fetch(`${config.analyzeUrl}/${cut.id}`, {
                    method: 'POST', headers: { 'X-CSRF-TOKEN': config.csrf, Accept: 'application/json' },
                });
                const body = await response.json();
                if (!response.ok) throw new Error(body.message || 'Analyse indisponible.');
                const deterministic = document.createElement('p');
                deterministic.className = 'alert alert-info';
                deterministic.textContent = `Score déterministe (non modifié par l’IA) : ${body.deterministic_priority.score}/100 — ${body.deterministic_priority.level}.`;
                result.replaceChildren(deterministic);
                const analysis = document.createElement('div');
                result.append(analysis);
                renderAnalysis(analysis, body.analysis);
            } catch (error) {
                result.textContent = error.message;
            } finally {
                button.disabled = false;
            }
        });
        container.append(title, score, button, result);
    }

    async function refresh() {
        const query = new URLSearchParams(new FormData(document.getElementById('sg-filters')));
        const list = document.getElementById('sg-cut-list');
        list.innerHTML = '<div class="p-3 text-muted">Chargement des coupures…</div>';
        try {
            const response = await fetch(`${config.dataUrl}?${query}`, { headers: { Accept: 'application/json' } });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Les données cartographiques ne sont pas disponibles.');
            currentCuts = data.coupures;
            cutsById.clear();
            currentCuts.forEach((cut) => cutsById.set(cut.id, cut));
            addCutMarkers(currentCuts);
            addImpactMarkers(data.impacts);
            document.querySelectorAll('[data-stat]').forEach((element) => {
                element.textContent = new Intl.NumberFormat('fr-FR').format(data.statistics[element.dataset.stat] ?? 0);
            });
            renderList(currentCuts);
            if (!document.getElementById('sg-show-cuts').checked) map.removeLayer(outageLayer);
            if (!document.getElementById('sg-show-impacts').checked) map.removeLayer(impactLayer);
            if (!document.getElementById('sg-show-interventions').checked) map.removeLayer(interventionLayer);
            document.getElementById('sg-route-button').disabled = !data.routing_configured;
        } catch (error) {
            list.innerHTML = '';
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger m-3';
            alert.textContent = `${error.message} Vérifiez la connexion puis réessayez.`;
            list.append(alert);
        }
    }

    document.getElementById('sg-filters').addEventListener('submit', (event) => {
        event.preventDefault();
        refresh();
    });
    document.getElementById('sg-quartier')?.addEventListener('change', (event) => {
        const zoneSelect = document.getElementById('sg-zone');
        if (zoneSelect && event.target.value) {
            zoneSelect.value = event.target.value;
        }
    });
    document.getElementById('sg-filters').addEventListener('reset', () => window.setTimeout(refresh, 0));
    document.getElementById('sg-cut-list').addEventListener('change', (event) => {
        if (!event.target.matches('.sg-route-select:checked')) return;
        const selected = document.querySelectorAll('.sg-route-select:checked');
        if (selected.length > 10) {
            event.target.checked = false;
            window.alert('Le calcul accepte au maximum 10 coupures par itinéraire.');
        }
    });
    document.getElementById('sg-show-cuts').addEventListener('change', (event) => event.target.checked ? map.addLayer(outageLayer) : map.removeLayer(outageLayer));
    document.getElementById('sg-show-impacts').addEventListener('change', (event) => event.target.checked ? map.addLayer(impactLayer) : map.removeLayer(impactLayer));
    document.getElementById('sg-show-interventions').addEventListener('change', (event) => event.target.checked ? map.addLayer(interventionLayer) : map.removeLayer(interventionLayer));
    document.getElementById('sg-pick-origin').addEventListener('click', (event) => {
        pickingOrigin = !pickingOrigin;
        event.currentTarget.classList.toggle('active', pickingOrigin);
        event.currentTarget.textContent = pickingOrigin ? 'Cliquez sur la carte pour choisir le départ…' : 'Choisir le départ sur la carte';
    });
    map.on('click', (event) => {
        if (!pickingOrigin) return;
        document.getElementById('sg-origin-lat').value = event.latlng.lat.toFixed(7);
        document.getElementById('sg-origin-lng').value = event.latlng.lng.toFixed(7);
        pickingOrigin = false;
        document.getElementById('sg-pick-origin').classList.remove('active');
        document.getElementById('sg-pick-origin').textContent = 'Choisir le départ sur la carte';
    });
    document.getElementById('sg-route-button').addEventListener('click', async () => {
        const selected = [...document.querySelectorAll('.sg-route-select:checked')].map((input) => Number(input.value));
        const output = document.getElementById('sg-route-result');
        if (!selected.length) {
            output.textContent = 'Sélectionnez au moins une coupure géolocalisée.';
            return;
        }
        if (!document.getElementById('sg-origin-lat').value || !document.getElementById('sg-origin-lng').value) {
            output.textContent = 'Saisissez ou choisissez explicitement le point de départ.';
            return;
        }
        output.textContent = 'Calcul de l’itinéraire routier…';
        routeLayer.clearLayers();
        try {
            const response = await fetch(config.routeUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrf, Accept: 'application/json' },
                body: JSON.stringify({
                    origin: {
                        latitude: Number(document.getElementById('sg-origin-lat').value),
                        longitude: Number(document.getElementById('sg-origin-lng').value),
                    },
                    coupure_ids: selected,
                }),
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Le trajet ne peut pas être calculé.');
            routeLayer.addData(data.geometry);
            map.fitBounds(routeLayer.getBounds(), { padding: [24, 24] });
            output.replaceChildren();
            const summary = document.createElement('p');
            summary.textContent = `Distance ${Number(data.distance_km).toFixed(1)} km · déplacement estimé ${Math.round(data.duration_seconds / 60)} min (service routier).`;
            const order = document.createElement('ol');
            data.steps.forEach((step) => {
                const item = document.createElement('li');
                item.textContent = `${step.label} — priorité ${step.priority.score}/100`;
                order.append(item);
            });
            const note = document.createElement('small');
            note.className = 'text-muted';
            note.textContent = data.algorithm;
            output.append(summary, order, note);
        } catch (error) {
            output.textContent = error.message;
        }
    });
    document.getElementById('sg-zone-button').addEventListener('click', async (event) => {
        const quartierId = document.getElementById('sg-zone').value;
        const output = document.getElementById('sg-zone-result');
        if (!quartierId) {
            output.textContent = 'Sélectionnez un quartier.';
            return;
        }
        event.currentTarget.disabled = true;
        output.textContent = 'Analyse en cours…';
        try {
            const response = await fetch(config.analyzeZoneUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrf, Accept: 'application/json' },
                body: JSON.stringify({ quartier_id: Number(quartierId) }),
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Analyse indisponible.');
            renderAnalysis(output, data.analysis);
        } catch (error) {
            output.textContent = error.message;
        } finally {
            event.currentTarget.disabled = false;
        }
    });

    window.setTimeout(() => map.invalidateSize(), 150);
    refresh();
})();
