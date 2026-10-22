# Supervision & Métriques — Prometheus & Grafana (DevSpark)

Ce document détaille l'infrastructure de supervision mise en place avec **Prometheus** et **Grafana** pour l'application DevSpark (Alerte Canicule).

---

## 🚀 Accès Rapide aux Interfaces

| Service | URL | Identifiants par défaut | Description |
| :--- | :--- | :--- | :--- |
| **Grafana** | [http://localhost:3000](http://localhost:3000) | `admin` / `admin` | Tableau de bord visuel interactif préconfiguré |
| **Prometheus** | [http://localhost:9090](http://localhost:9090) | *(Aucun)* | Moteur de collecte, requêtes PromQL et cibles |
| **Métriques brutes** | [http://localhost:8000/metrics](http://localhost:8000/metrics) | *(Accès direct)* | Endpoint OpenMetrics exposé par Laravel |
| **MySQL Exporter** | [http://localhost:9104/metrics](http://localhost:9104/metrics) | *(Accès direct)* | Métriques internes du serveur MySQL |

---

## 🛠️ Commandes Docker

### Démarrer la stack de monitoring :
```bash
docker compose up -d prometheus grafana mysql_exporter
```

### Vérifier l'état des conteneurs :
```bash
docker compose ps
```

### Arrêter les services de monitoring :
```bash
docker compose stop prometheus grafana mysql_exporter
```

---

## 📊 Tableau de Bord Grafana (« DevSpark - Alerte Canicule & Métier »)

Le tableau de bord est **automatiquement provisionné** au démarrage de Grafana sans configuration manuelle requise :

1. **État des Services & Santé Système** :
   - Statut de l'application Laravel (`UP` / `DOWN`)
   - Statut de la connexion MySQL (`CONNECTÉ` / `DOWN`)
   - Latence du ping MySQL (en millisecondes)
   - Consommation mémoire PHP (courante et pic)
   - Score moyen de risque no-show calculé par l'IA

2. **Indicateurs Métier en Temps Réel** :
   - Volume total des réservations et réservations du jour
   - Points de fraîcheur surveillés et capacité totale
   - Bulletins d'alerte météo émis
   - Nombre total d'utilisateurs inscrits

3. **Graphiques Temporels** :
   - Évolution de la consommation mémoire PHP
   - Évolution de la latence de la base de données MySQL

4. **Répartitions et Alertes** :
   - Répartition des réservations par statut (confirmée, en attente, terminée, no-show, etc.)
   - Répartition des utilisateurs par rôle (administrateurs vs citoyens)
   - Jauge d'incidents (coupures, interventions, signalements citoyens, diagnostics)

---

## ⚙️ Architecture & Collecte

```
                       ┌─────────────────────────┐
                       │  Laravel DevSpark App   │
                       │     (/metrics route)    │
                       └────────────▲────────────┘
                                    │
                                    │ scrape (5s)
                                    │
┌──────────────┐       ┌────────────┴────────────┐       ┌──────────────┐
│  Grafana     │◄──────┤       Prometheus        │◄──────┤ MySQL        │
│ (Port 3000)  │ PromQL│      (Port 9090)        │scrape │ Exporter     │
└──────────────┘       └─────────────────────────┘       └──────────────┘
```

- **Prometheus** interroge à intervalle régulier (5s) l'endpoint `/metrics` de Laravel ainsi que le serveur MySQL.
- **Grafana** se connecte automatiquement à la source de données Prometheus (`http://prometheus:9090`) et charge le dashboard `devspark_dashboard.json`.
- Le middleware `PrometheusMetricsMiddleware` enregistre le trafic HTTP et les durées d'exécution sans dégrader les performances.
