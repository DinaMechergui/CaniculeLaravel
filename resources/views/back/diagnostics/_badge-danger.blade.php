{{-- Badge du niveau de danger prédit par l'IA. Variable attendue : $niveau (string|null) --}}
@php
    $classe = match ($niveau) {
        'faible' => 'badge bg-success',
        'moyen'  => 'badge bg-warning text-dark',
        'eleve'  => 'badge bg-danger',
        default  => 'badge bg-label-secondary',
    };
@endphp
<span class="{{ $classe }}">{{ \App\Models\Diagnostic::DANGERS[$niveau] ?? 'En attente' }}</span>
