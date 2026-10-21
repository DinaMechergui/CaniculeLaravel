{{-- En-tête du Front Office : logo, menu de navigation et actions utilisateur --}}
<header class="ve-header" id="ve-sticky">
    <div class="container-fluid ve-nav-wrap">
        {{-- Logo --}}
        <div class="ve-logo">
            <a href="{{ route('front.home') }}">
                <span class="ve-logo-icon"><i class="fa fa-thermometer-full" aria-hidden="true"></i></span>
                <span class="ve-logo-text">{{ str_replace(' ', '', config('app.name')) }}</span>
            </a>
        </div>

        {{-- Liens principaux (page courante mise en surbrillance) --}}
        <nav class="ve-nav">
            <ul>
                <li>
                    <a href="{{ route('front.home') }}" class="{{ request()->routeIs('front.home') ? 'active' : '' }}">
                        Accueil
                    </a>
                </li>

                <li>
                    <a href="{{ route('points-fraicheur.index') }}"
                       class="{{ request()->routeIs('points-fraicheur.index', 'points-fraicheur.show') ? 'active' : '' }}">
                        Points de fraîcheur
                    </a>
                </li>

                <li>
                    <a href="{{ route('coupures.index') }}" class="{{ request()->routeIs('coupures.index', 'coupures.show') ? 'active' : '' }}">Coupures</a>
                </li>
                <li>
                    <a href="{{ route('alertes.index') }}" class="{{ request()->routeIs('alertes.*') ? 'active' : '' }}">Alertes</a>
                </li>
                <li>
                    <a href="{{ route('conseils.index') }}" class="{{ request()->routeIs('conseils.index', 'conseils.show') ? 'active' : '' }}">Conseils</a>
                </li>
                <li>
                    <a href="{{ route('campagnes.index') }}" class="{{ request()->routeIs('campagnes.*') ? 'active' : '' }}">Campagnes</a>
                </li>
                <li><a href="{{ route('plans-urgence.index') }}" class="{{ request()->routeIs('plans-urgence.*') ? 'active' : '' }}">Plans d'urgence</a></li>
                <li>
                    <a href="{{ route('signalements.index') }}"
                       class="{{ request()->routeIs('signalements.*') ? 'active' : '' }}">
                        Signalements
                    </a>
                </li>

                @auth
                    <li><a href="{{ route('mes-plans.index') }}" class="{{ request()->routeIs('mes-plans.*') ? 'active' : '' }}">Mes plans</a></li>
                    <li>
                        <a href="{{ route('profile.edit') }}"
                           class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
                            Mon profil
                        </a>
                    </li>
                @endauth
            </ul>
        </nav>

        {{-- Actions : connexion / back office / déconnexion --}}
        <div class="ve-nav-cta">
            @guest
                <a href="{{ route('login') }}" class="ve-btn-ghost">Connexion</a>
                <a href="{{ route('register') }}" class="ve-cta-btn">
                    Inscription <i class="fa fa-arrow-right"></i>
                </a>
            @else
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="ve-btn-ghost">Back Office</a>
                @endif

                <span class="ve-user-name">
                    <i class="fa fa-user"></i> {{ auth()->user()->name }}
                </span>

                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="ve-cta-btn" title="Se déconnecter">
                        Déconnexion
                    </button>
                </form>
            @endauth
        </div>

        {{-- Bouton de menu mobile --}}
        <button class="ve-toggler" id="ve-toggle" aria-label="Ouvrir le menu">
            <span></span><span></span><span></span>
        </button>
    </div>

    {{-- Menu mobile --}}
    <div class="ve-mobile-menu" id="ve-mobile-menu">
        <ul>
            <li>
                <a href="{{ route('front.home') }}" class="{{ request()->routeIs('front.home') ? 'active' : '' }}">
                    Accueil
                </a>
            </li>

            <li>
                <a href="{{ route('points-fraicheur.index') }}"
                   class="{{ request()->routeIs('points-fraicheur.index', 'points-fraicheur.show') ? 'active' : '' }}">
                    Points de fraîcheur
                </a>
            </li>

            <li>
                <a href="{{ route('coupures.index') }}" class="{{ request()->routeIs('coupures.index', 'coupures.show') ? 'active' : '' }}">Coupures</a>
            </li>
            <li>
                <a href="{{ route('alertes.index') }}" class="{{ request()->routeIs('alertes.*') ? 'active' : '' }}">Alertes</a>
            </li>
            <li>
                <a href="{{ route('conseils.index') }}" class="{{ request()->routeIs('conseils.index', 'conseils.show') ? 'active' : '' }}">Conseils</a>
            </li>
            <li>
                <a href="{{ route('campagnes.index') }}" class="{{ request()->routeIs('campagnes.*') ? 'active' : '' }}">Campagnes</a>
            </li>
            <li><a href="{{ route('plans-urgence.index') }}" class="{{ request()->routeIs('plans-urgence.*') ? 'active' : '' }}">Plans d'urgence</a></li>
            <li>
                <a href="{{ route('signalements.index') }}"
                   class="{{ request()->routeIs('signalements.*') ? 'active' : '' }}">
                    Signalements
                </a>
            </li>

            @guest
                <li><a href="{{ route('login') }}">Connexion</a></li>
                <li><a href="{{ route('register') }}">Inscription</a></li>
            @else
                <li><a href="{{ route('mes-plans.index') }}" class="{{ request()->routeIs('mes-plans.*') ? 'active' : '' }}">Mes plans</a></li>
                <li>
                    <a href="{{ route('profile.edit') }}"
                       class="{{ request()->routeIs('profile.*') ? 'active' : '' }}">
                        Mon profil
                    </a>
                </li>
                @if (auth()->user()->isAdmin())
                    <li><a href="{{ route('admin.dashboard') }}">Back Office</a></li>
                @endif
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="ve-mobile-logout">Déconnexion</button>
                    </form>
                </li>
            @endauth
        </ul>
    </div>
</header>
