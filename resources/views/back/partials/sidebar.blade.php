{{-- Menu latéral du Back Office --}}
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route('admin.dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo app-brand-icon">
                <i class="bx bx-hot"></i>
            </span>
            <span class="app-brand-text demo menu-text fw-bolder ms-2">Back office</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        {{-- Tableau de bord --}}
        <li class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <a href="{{ route('admin.dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Tableau de bord">Tableau de bord</div>
            </a>
        </li>

        {{-- Utilisateurs (tâche commune à l'équipe) --}}
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Gestion</span>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <a href="{{ route('admin.users.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-group"></i>
                <div data-i18n="Utilisateurs">Utilisateurs</div>
            </a>
        </li>

        {{-- Module « Points de fraîcheur » --}}
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Points de fraîcheur</span>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.types-point.*') ? 'active' : '' }}">
            <a href="{{ route('admin.types-point.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-category"></i>
                <div data-i18n="Types de points">Types de points</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.points-fraicheur.*') ? 'active' : '' }}">
            <a href="{{ route('admin.points-fraicheur.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-map-alt"></i>
                <div data-i18n="Points de fraîcheur">Points de fraîcheur</div>
            </a>
        </li>

        {{-- Module « Réservations » --}}
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Réservations</span>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.reservations.*') ? 'active' : '' }}">
            <a href="{{ route('admin.reservations.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-calendar-check"></i>
                <div data-i18n="Réservations">Réservations</div>
            </a>
        </li>

        {{-- Module « Coupures » --}}
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Coupures</span>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.quartiers.*') ? 'active' : '' }}">
            <a href="{{ route('admin.quartiers.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-map"></i>
                <div data-i18n="Quartiers">Quartiers</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.coupures.*') ? 'active' : '' }}">
            <a href="{{ route('admin.coupures.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-bolt-circle"></i>
                <div data-i18n="Coupures">Coupures</div>
            </a>
        </li>

        {{-- Module « Alertes météo » --}}
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Alertes météo</span>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.niveaux-alerte.*') ? 'active' : '' }}">
            <a href="{{ route('admin.niveaux-alerte.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-signal-5"></i>
                <div data-i18n="Niveaux d'alerte">Niveaux d'alerte</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.alertes-meteo.*') ? 'active' : '' }}">
            <a href="{{ route('admin.alertes-meteo.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-cloud-rain"></i>
                <div data-i18n="Alertes météo">Alertes météo</div>
            </a>
        </li>

        {{-- Module « Conseils » --}}
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Conseils</span>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.categories-conseil.*') ? 'active' : '' }}">
            <a href="{{ route('admin.categories-conseil.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-bulb"></i>
                <div data-i18n="Catégories de conseils">Catégories de conseils</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.conseils.*') ? 'active' : '' }}">
            <a href="{{ route('admin.conseils.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-message-square-detail"></i>
                <div data-i18n="Conseils">Conseils</div>
            </a>
        </li>

        {{-- Module « Signalements » --}}
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Signalements</span>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.types-signalement.*') ? 'active' : '' }}">
            <a href="{{ route('admin.types-signalement.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-category-alt"></i>
                <div data-i18n="Types de signalements">Types de signalements</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.signalements.*') ? 'active' : '' }}">
            <a href="{{ route('admin.signalements.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-error-alt"></i>
                <div data-i18n="Signalements">Signalements</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.diagnostics.*') ? 'active' : '' }}">
            <a href="{{ route('admin.diagnostics.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-wrench"></i>
                <div data-i18n="Diagnostics">Diagnostics</div>
            </a>
        </li>

        {{-- Accès au site public --}}
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Site</span>
        </li>
        <li class="menu-item {{ request()->routeIs('front.*') ? 'active' : '' }}">
            <a href="{{ route('front.home') }}" class="menu-link" target="_blank">
                <i class="menu-icon tf-icons bx bx-world"></i>
                <div data-i18n="Voir le site">Voir le site</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <a href="{{ route('profile.edit') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user-circle"></i>
                <div data-i18n="Mon profil">Mon profil</div>
            </a>
        </li>
    </ul>
</aside>
<!-- / Menu -->
