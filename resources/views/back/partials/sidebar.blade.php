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
        <li class="menu-item {{ request()->routeIs('admin.interventions.*') ? 'active' : '' }}">
            <a href="{{ route('admin.interventions.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-wrench"></i>
                <div data-i18n="Interventions">Interventions</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.impacts-coupures.*') ? 'active' : '' }}">
            <a href="{{ route('admin.impacts-coupures.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-error-circle"></i>
                <div data-i18n="Impacts des coupures">Impacts des coupures</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.smartgrid.*') ? 'active' : '' }}">
            <a href="{{ route('admin.smartgrid.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-map-pin"></i>
                <div data-i18n="SmartGrid AI">SmartGrid AI</div>
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
