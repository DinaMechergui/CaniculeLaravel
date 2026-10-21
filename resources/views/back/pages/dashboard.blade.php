@extends('back.layouts.back')

@section('title', 'Tableau de bord')

@section('content')
    <x-page-header
        title="Tableau de bord"
        :subtitle="'Vue d\'ensemble de l\'application ' . config('app.name')"
    />

    {{-- Statistiques --}}
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="app-stat-card">
                        <div class="app-stat-icon"><i class="bx bx-group"></i></div>
                        <div>
                            <div class="app-stat-value">{{ $stats['users'] }}</div>
                            <div class="app-stat-label">Utilisateurs inscrits</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="app-stat-card">
                        <div class="app-stat-icon"><i class="bx bx-shield-quarter"></i></div>
                        <div>
                            <div class="app-stat-value">{{ $stats['admins'] }}</div>
                            <div class="app-stat-label">Administrateurs</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="app-stat-card">
                        <div class="app-stat-icon"><i class="bx bx-user-plus"></i></div>
                        <div>
                            <div class="app-stat-value">{{ $stats['recent_users'] }}</div>
                            <div class="app-stat-label">Inscriptions (30 jours)</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="app-stat-card">
                        <div class="app-stat-icon"><i class="bx bx-mail-send"></i></div>
                        <div>
                            <div class="app-stat-value">{{ $stats['verified_users'] }}</div>
                            <div class="app-stat-label">E-mails vérifiés</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Module « Conseils » --}}
    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="app-stat-card">
                        <div class="app-stat-icon"><i class="bx bx-bulb"></i></div>
                        <div>
                            <div class="app-stat-value">{{ $conseilStats['categories'] }}</div>
                            <div class="app-stat-label">Catégories de conseils</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="app-stat-card">
                        <div class="app-stat-icon"><i class="bx bx-message-square-detail"></i></div>
                        <div>
                            <div class="app-stat-value">{{ $conseilStats['conseils'] }}</div>
                            <div class="app-stat-label">Conseils publiés</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-12 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="app-stat-card">
                        <div class="app-stat-icon"><i class="bx bx-trophy"></i></div>
                        <div>
                            <div class="app-stat-value">{{ $conseilStats['derniere_categorie']?->nom ?? '—' }}</div>
                            <div class="app-stat-label">Catégorie la plus fournie</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Module « Campagnes » --}}
    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="app-stat-card">
                        <div class="app-stat-icon"><i class="bx bx-broadcast"></i></div>
                        <div>
                            <div class="app-stat-value">{{ $campagneStats['total'] }}</div>
                            <div class="app-stat-label">Campagnes totales</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="app-stat-card">
                        <div class="app-stat-icon"><i class="bx bx-check-circle"></i></div>
                        <div>
                            <div class="app-stat-value">{{ $campagneStats['actives'] }}</div>
                            <div class="app-stat-label">Campagnes actives</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="app-stat-card">
                        <div class="app-stat-icon"><i class="bx bx-calendar-exclamation"></i></div>
                        <div>
                            <div class="app-stat-value">{{ $campagneStats['finDans7j'] }}</div>
                            <div class="app-stat-label">Expirent sous 7 jours</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4"><div class="card h-100"><div class="card-body"><div class="app-stat-card"><div class="app-stat-icon"><i class="bx bx-shield-alt-2"></i></div><div><div class="app-stat-value">{{ $planStats['actifs'] }} / {{ $planStats['total'] }}</div><div class="app-stat-label"><a href="{{ route('admin.plans-urgence.index') }}">Plans d'urgence actifs</a></div></div></div></div></div></div>
        {{-- Derniers inscrits --}}
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Derniers inscrits</h5>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary">
                        Voir tous les utilisateurs
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Nom</th>
                                    <th>E-mail</th>
                                    <th>Rôle</th>
                                    <th>Inscrit le</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($latestUsers as $user)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.users.show', $user) }}">
                                                {{ $user->name }}
                                            </a>
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            <span class="badge {{ $user->isAdmin() ? 'app-badge-admin' : 'app-badge-user' }}">
                                                {{ $user->isAdmin() ? 'Administrateur' : 'Utilisateur' }}
                                            </span>
                                        </td>
                                        <td>{{ $user->created_at?->format('d/m/Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            Aucun utilisateur pour le moment.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Encart d'actions rapides --}}
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Actions rapides</h5>
                </div>
                <div class="card-body d-flex flex-column gap-2">
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Ajouter un utilisateur
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary">
                        <i class="bx bx-list-ul me-1"></i> Gérer les utilisateurs
                    </a>
                    <a href="{{ route('front.home') }}" class="btn btn-outline-secondary" target="_blank">
                        <i class="bx bx-world me-1"></i> Voir le site public
                    </a>

                    {{-- Emplacement du futur module « Points de fraîcheur »
                    <a href="{{ route('admin.points-fraicheur.index') }}" class="btn btn-outline-primary">
                        Gérer les points de fraîcheur
                    </a>
                    --}}
                </div>
            </div>

            {{-- Module « Points de fraîcheur » : informations (à remplacer par les statistiques du module)
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Points de fraîcheur</h5>
                </div>
                <div class="card-body text-muted">
                    Les statistiques du module s'afficheront ici.
                </div>
            </div>
            --}}
        </div>
    </div>
@endsection
