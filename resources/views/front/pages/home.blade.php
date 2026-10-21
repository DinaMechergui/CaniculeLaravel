@extends('front.layouts.front')

@section('title', 'Accueil')
@section('description', 'Aide les habitants du quartier à anticiper les canicules, les coupures de courant et à trouver un point de fraîcheur.')

@section('content')
    @if($campagneEnVedette)<section class="ve-section" style="padding-bottom:0"><div class="container"><div class="alert alert-warning"><strong>Campagne en cours :</strong> <a href="{{ route('campagnes.show',$campagneEnVedette) }}">{{ $campagneEnVedette->titre }}</a> — se termine le {{ $campagneEnVedette->date_fin->format('d/m/Y') }}</div></div></section>@endif
    {{-- ===== HERO ===== --}}
    <section class="ve-hero">
        {{-- Panneau de gauche : texte --}}
        <div class="ve-hero-left">
            <span class="ve-hero-badge">Vigilance canicule &nbsp;&bull;&nbsp; Quartier &amp; habitants solidaires</span>
            <h1>Restez informé face à la <span class="ve-highlight">canicule</span><br>et aux coupures de courant</h1>
            <p>
                {{ config('app.name') }} centralise les alertes météo, les coupures d'électricité
                prévues et les points de fraîcheur du quartier pour vous aider à anticiper
                et à vous protéger, été comme hiver.
            </p>
            <div class="ve-hero-btns">
                @guest
                    <a href="{{ route('register') }}" class="ve-btn-primary">Créer un compte</a>
                    <a href="{{ route('login') }}" class="ve-btn-ghost">Se connecter</a>
                @else
                    <a href="{{ route('alertes.index') }}" class="ve-btn-primary">Consulter les alertes</a>
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="ve-btn-ghost">Back office</a>
                    @endif
                @endauth
            </div>

            {{-- Statistiques --}}
            <div class="ve-hero-stats">
                <div class="ve-stat">
                    <strong>4</strong>
                    <span>Services essentiels</span>
                </div>
                <div class="ve-stat-divider"></div>
                <div class="ve-stat">
                    <strong>24/7</strong>
                    <span>Alertes actualisées</span>
                </div>
                <div class="ve-stat-divider"></div>
                <div class="ve-stat">
                    <strong>100%</strong>
                    <span>Gratuit pour le quartier</span>
                </div>
            </div>
        </div>

        {{-- Panneau de droite : images --}}
        <div class="ve-hero-right">
            <div class="ve-hero-img-main bg-img" style="background-image:url('{{ asset('assets/front/img/bg-img/1.jpg') }}');"></div>
            <div class="ve-hero-img-accent bg-img" style="background-image:url('{{ asset('assets/front/img/bg-img/3.jpg') }}');"></div>
            <div class="ve-float-card">
                <i class="fa fa-thermometer-full"></i>
                <div>
                    <strong>32°C</strong>
                    <span>Pic de chaleur prévu</span>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== BARRE D'INFORMATIONS ===== --}}
    <div class="ve-trust-bar">
        <div class="ve-trust-inner">
            <span><i class="fa fa-cloud-sun"></i> Alertes météo du quartier</span>
            <span><i class="fa fa-bolt"></i> Coupures de courant annoncées</span>
            <span><i class="fa fa-snowflake"></i> Points de fraîcheur à proximité</span>
            <span><i class="fa fa-heart"></i> Conseils anti-canicule</span>
            <span><i class="fa fa-bell"></i> Notifications en temps réel</span>
            <span><i class="fa fa-users"></i> Entraide entre voisins</span>
            <span><i class="fa fa-cloud-sun"></i> Alertes météo du quartier</span>
            <span><i class="fa fa-bolt"></i> Coupures de courant annoncées</span>
            <span><i class="fa fa-snowflake"></i> Points de fraîcheur à proximité</span>
        </div>
    </div>

    {{-- ===== SERVICES ===== --}}
    <section class="ve-section ve-services-section">
        <div class="container">
            <div class="ve-section-header text-center">
                <span class="ve-section-tag">Ce que nous proposons</span>
                <h2>Tout pour traverser l'<span>été sereinement</span></h2>
                <p>Quatre services complémentaires pensés pour les habitants du quartier.</p>
            </div>

            <div class="ve-services-grid">
                <div class="ve-service-card wow fadeInUp" data-wow-delay="100ms">
                    <div class="ve-service-icon"><i class="fa fa-cloud-sun"></i></div>
                    <h4>Alertes météo</h4>
                    <p>Recevez les prévisions et les alertes de vigilance canicule dès leur publication officielle.</p>
                </div>

                <div class="ve-service-card wow fadeInUp" data-wow-delay="200ms">
                    <div class="ve-service-icon"><i class="fa fa-bolt"></i></div>
                    <h4>Coupures de courant</h4>
                    <p>Consultez les coupures d'électricité prévues dans votre rue et leurs horaires estimées.</p>
                </div>

                <div class="ve-service-card wow fadeInUp" data-wow-delay="300ms">
                    <div class="ve-service-icon"><i class="fa fa-snowflake"></i></div>
                    <h4>Points de fraîcheur</h4>
                    <p>
                        Repérez les lieux climatisés et rafraîchissants ouverts près de chez vous.
                        {{-- Module à venir : <a href="{{ route('points-fraicheur.index') }}">Voir la carte</a> --}}
                    </p>
                </div>

                <div class="ve-service-card wow fadeInUp" data-wow-delay="400ms">
                    <div class="ve-service-icon"><i class="fa fa-medkit"></i></div>
                    <h4>Conseils pratiques</h4>
                    <p>Des recommandations simples pour vous hydrater, protéger vos proches et faire des économies.</p>
                </div>

                <div class="ve-service-card wow fadeInUp" data-wow-delay="500ms">
                    <div class="ve-service-icon"><i class="fa fa-map-marker"></i></div>
                    <h4>Mon quartier</h4>
                    <p>Retrouvez les informations utiles de votre quartier : équipements, contacts et dispositifs d'aide.</p>
                </div>

                <div class="ve-service-card wow fadeInUp" data-wow-delay="600ms">
                    <div class="ve-service-icon"><i class="fa fa-comments"></i></div>
                    <h4>Entraide voisine</h4>
                    <p>Signalez une situation préoccupante et aidez vos voisins les plus vulnérables pendant les épisodes de chaleur.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== COMMENT ÇA MARCHE ===== --}}
    <section class="ve-section ve-mvv-section">
        <div class="container">
            <div class="ve-section-header text-center">
                <span class="ve-section-tag">Comment ça marche</span>
                <h2>Trois étapes pour <span>être prêt</span></h2>
            </div>

            <div class="ve-mvv-grid">
                <div class="ve-mvv-card wow fadeInUp" data-wow-delay="100ms">
                    <div class="ve-mvv-icon"><i class="fa fa-user-plus"></i></div>
                    <h4>1. Créez votre compte</h4>
                    <p>Inscrivez-vous gratuitement pour personnaliser les alertes reçues pour votre quartier.</p>
                </div>

                <div class="ve-mvv-card wow fadeInUp" data-wow-delay="250ms">
                    <div class="ve-mvv-icon"><i class="fa fa-bell-o"></i></div>
                    <h4>2. Recevez les alertes</h4>
                    <p>Vigilance canicule, coupures de courant et fermetures de points de fraîcheur : tout est centralisé.</p>
                </div>

                <div class="ve-mvv-card wow fadeInUp" data-wow-delay="400ms">
                    <div class="ve-mvv-icon"><i class="fa fa-shield"></i></div>
                    <h4>3. Agissez à temps</h4>
                    <p>Anticipez vos déplacements, hydatez-vous et rejoignez le point de fraîcheur le plus proche.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== CONSEILS ===== --}}
    <section class="ve-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-12 col-lg-6 mb-4 mb-lg-0">
                    <div class="ve-section-header" style="margin-bottom:0;">
                        <span class="ve-section-tag">Conseils anti-canicule</span>
                        <h2>Les bons réflexes pendant <span>les fortes chaleurs</span></h2>
                        <p style="margin:0;">
                            Quelques habitudes simples suffisent à réduire fortement les risques
                            liés aux épisodes de canicule et aux coupures d'électricité.
                        </p>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="ve-sidebar-widget">
                        <ul class="ve-checklist">
                            <li class="ve-check-item"><i class="fa fa-check"></i> Buvez de l'eau régulièrement, sans attendre la soif.</li>
                            <li class="ve-check-item"><i class="fa fa-check"></i> Fermez les volets et les fenêtres en journée, aérez la nuit.</li>
                            <li class="ve-check-item"><i class="fa fa-check"></i> Passez au moins deux heures dans un lieu climatisé.</li>
                            <li class="ve-check-item"><i class="fa fa-check"></i> Vérifiez les horaires des coupures de courant avant elles.</li>
                            <li class="ve-check-item"><i class="fa fa-check"></i> Prenez des nouvelles de vos voisins isolés.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== APPEL À L'ACTION ===== --}}
    <section class="ve-section" style="padding-top:0;">
        <div class="container">
            <div class="ve-cta-banner">
                <div class="ve-cta-overlay"></div>
                <div class="ve-cta-content text-center">
                    <h2>Prêt à anticiper la prochaine canicule ?</h2>
                    <p>Créez votre compte gratuitement et recevez les alertes de votre quartier.</p>
                    @guest
                        <a href="{{ route('register') }}" class="ve-btn-white">Inscription gratuite</a>
                    @else
                        <a href="{{ route('profile.edit') }}" class="ve-btn-white">Mon compte</a>
                    @endauth
                </div>
            </div>
        </div>
    </section>

    {{-- Conseil du jour --}}
    @if ($conseilDuJour)
        <section class="ve-section" style="padding-top:0;">
            <div class="container">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4 p-md-5 text-center">
                        <span class="badge badge-info mb-3">
                            <i class="fa fa-lightbulb-o" aria-hidden="true"></i> Conseil du jour
                        </span>
                        <h3 class="mb-3">{{ $conseilDuJour->titre }}</h3>
                        <p class="text-muted mb-4">
                            {{ \Illuminate\Support\Str::limit(strip_tags($conseilDuJour->contenu), 180) }}
                        </p>
                        <a href="{{ route('conseils.show', $conseilDuJour) }}" class="btn btn-primary">
                            Lire le conseil
                        </a>
                        <a href="{{ route('conseils.index') }}" class="btn btn-link">Tous les conseils</a>
                    </div>
                </div>
            </div>
        </section>
    @endif
@endsection
