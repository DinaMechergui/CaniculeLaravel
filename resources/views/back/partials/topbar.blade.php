{{-- Barre supérieure du Back Office --}}
<nav
    class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
    id="layout-navbar"
>
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="bx bx-menu bx-sm"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <div class="navbar-nav flex-row align-items-center ms-auto">
            {{-- Lien vers le site public --}}
            <li class="nav-item d-none d-xl-flex me-3">
                <a class="nav-link" href="{{ route('front.home') }}" target="_blank">
                    <i class="bx bx-world me-1"></i>
                    <span class="align-middle">Voir le site</span>
                </a>
            </li>

            {{-- Notifications d'alertes administratives --}}
            @php
                $user = auth()->user();
                $unreadNotifications = $user ? $user->unreadNotifications()->take(5)->get() : collect();
                $unreadCount = $user ? $user->unreadNotifications()->count() : 0;
            @endphp
            <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3">
                <a class="nav-link dropdown-toggle hide-arrow position-relative" href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false" title="Alertes">
                    <i class="bx bx-bell bx-sm"></i>
                    @if ($unreadCount > 0)
                        <span class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle" style="font-size: 0.65rem; padding: 0.25em 0.5em;">
                            {{ $unreadCount }}
                        </span>
                    @endif
                </a>
                <ul class="dropdown-menu dropdown-menu-end py-0 shadow" style="min-width: 320px;">
                    <li class="dropdown-menu-header border-bottom">
                        <div class="dropdown-header d-flex align-items-center py-3">
                            <h6 class="mb-0 me-auto">Alertes & Notifications</h6>
                            <span class="badge bg-label-danger">{{ $unreadCount }} nouvelle(s)</span>
                        </div>
                    </li>
                    <li class="dropdown-notifications-list scrollable-container">
                        <ul class="list-group list-group-flush">
                            @forelse ($unreadNotifications as $notification)
                                <li class="list-group-item list-group-item-action dropdown-notifications-item p-3 border-bottom">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-3">
                                            <span class="avatar-initial rounded-circle bg-label-danger p-2 d-inline-flex align-items-center justify-content-center">
                                                <i class="bx bx-error text-danger"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1" style="font-size: 0.85rem;">{{ $notification->data['titre'] ?? 'Alerte Réseau' }}</h6>
                                            <p class="mb-1 small text-muted" style="line-height: 1.3;">{{ $notification->data['message'] ?? '' }}</p>
                                            <small class="text-secondary" style="font-size: 0.75rem;">{{ $notification->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="list-group-item text-center py-4 text-muted">
                                    <i class="bx bx-check-circle bx-sm d-block mb-1 text-success"></i>
                                    Aucune alerte en attente.
                                </li>
                            @endforelse
                        </ul>
                    </li>
                    @if ($unreadCount > 0)
                        <li class="dropdown-menu-footer border-top p-2 text-center">
                            <a href="{{ route('admin.coupures.index') }}" class="btn btn-sm btn-link text-primary w-100">
                                Gérer les coupures du réseau
                            </a>
                        </li>
                    @endif
                </ul>
            </li>

            {{-- Utilisateur connecté --}}
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <img src="{{ asset('assets/back/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle" />
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-online">
                                        <img src="{{ asset('assets/back/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle" />
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="fw-semibold d-block">{{ auth()->user()->name }}</span>
                                    <small class="text-muted">
                                        {{ auth()->user()->isAdmin() ? 'Administrateur' : 'Utilisateur' }}
                                    </small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="bx bx-user me-2"></i>
                            <span class="align-middle">Mon profil</span>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="bx bx-power-off me-2"></i>
                                <span class="align-middle">Déconnexion</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </li>
            <!--/ User -->
        </div>
    </div>
</nav>
<!-- / Navbar -->
