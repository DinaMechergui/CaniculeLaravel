<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Billet {{ $reservation->code }}</title>
    <style>
        body { color: #212529; font-size: 13px; margin: 0; padding: 24px; }
        .entete { border-bottom: 3px solid #0d6efd; padding-bottom: 12px; margin-bottom: 18px; }
        .entete h1 { font-size: 20px; margin: 0 0 4px; color: #0d6efd; }
        .entete p { margin: 0; color: #6c757d; font-size: 12px; }
        .code { text-align: center; margin: 18px 0; }
        .code .valeur { font-size: 30px; letter-spacing: 6px; font-weight: bold; color: #0d6efd; }
        .code .libelle { font-size: 11px; color: #6c757d; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table td { padding: 8px 10px; border-bottom: 1px solid #e9ecef; vertical-align: top; }
        table td.label { color: #6c757d; width: 38%; }
        .statut { display: inline-block; padding: 3px 10px; border-radius: 10px; background: #e7f1ff; color: #0d6efd; font-size: 11px; }
        .pied { margin-top: 22px; font-size: 11px; color: #6c757d; text-align: center; border-top: 1px solid #e9ecef; padding-top: 12px; }
        .qr { text-align: center; margin-top: 16px; }
    </style>
</head>
<body>
    <div class="entete">
        <h1>{{ config('app.name') }} — Billet de réservation</h1>
        <p>Point de fraîcheur · Réservation de créneau</p>
    </div>

    <div class="code">
        <div class="valeur">{{ $reservation->code }}</div>
        <div class="libelle">Code de réservation</div>
        <div class="statut">{{ $reservation->statut_label }}</div>
    </div>

    <table>
        <tr>
            <td class="label">Nom</td>
            <td>{{ $reservation->nom }}</td>
        </tr>
        <tr>
            <td class="label">Lieu</td>
            <td>{{ $reservation->pointFraicheur?->nom ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Adresse</td>
            <td>{{ $reservation->pointFraicheur?->adresse ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Date</td>
            <td>{{ $reservation->date_reservation?->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="label">Créneau</td>
            <td>{{ $reservation->heure_debut_courte }} – {{ $reservation->heure_fin_courte }}</td>
        </tr>
        <tr>
            <td class="label">Nombre de personnes</td>
            <td>{{ $reservation->nombre_personnes }}</td>
        </tr>
        @if ($reservation->besoins_specifiques)
            <tr>
                <td class="label">Besoins spécifiques</td>
                <td>{{ $reservation->besoins_specifiques }}</td>
            </tr>
        @endif
    </table>

    <div class="qr">
        <img src="data:image/svg+xml;base64,{{ base64_encode($qrSvg) }}" width="130" height="130" alt="QR code">
    </div>

    <div class="pied">
        Présentez ce billet (ou le code {{ $reservation->code }}) à votre arrivée.<br>
        {{ config('app.name') }}
    </div>
</body>
</html>
