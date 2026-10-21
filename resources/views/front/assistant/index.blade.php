@extends('front.layouts.front')

@section('title', 'Assistant IA — Alerte Canicule')
@section('description', 'Assistant IA : questions sur les points de fraîcheur, les horaires et les conseils anti-canicule.')

@section('content')
    <section class="ve-section ai-section">
        <div class="container">
            <div class="ve-section-header text-center">
                <span class="ve-section-tag">Assistant IA</span>
                <h2>Où trouver un peu de frais ?</h2>
                <p>
                    Posez vos questions sur les points de fraîcheur, les horaires
                    ou l'hydratation pendant les vagues de chaleur.
                </p>
            </div>

            <div class="wow fadeInUp">
                @include('front.partials.chat-ia', [
                    'chatId' => 'ai',
                    'bienvenue' => "Bonjour ! Je suis l'assistant IA d'Alerte Canicule. Demandez-moi par exemple « Où faire du frais près de La Marsa ? » ou « Quels conseils contre la déshydratation ? ».",
                    'questions' => [
                        'Où sont les salles climatisées à Tunis ?',
                        'Quels sont les meilleurs conseils contre la déshydratation ?',
                        'Quelles plages accessibles me conseilles-tu ?',
                    ],
                    'hint' => "Réponses générées par une intelligence artificielle : vérifiez toujours les horaires des lieux.",
                    'lienPleinEcran' => false,
                ])
            </div>
        </div>
    </section>
@endsection
