<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="light-style layout-menu-fixed"
    dir="ltr"
    data-theme="theme-default"
    data-assets-path="{{ asset('assets/back') }}/"
    data-template="vertical-menu-template-free"
>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum=1.0" />
    <meta name="description" content="Back office de {{ config('app.name') }}" />

    {{-- Titre dynamique de la page --}}
    <title>@yield('title', 'Tableau de bord') | Back office | {{ config('app.name') }}</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/back/img/favicon/favicon.ico') }}" />

    {{-- Police du template --}}
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet"
    />

    {{-- Icônes --}}
    <link rel="stylesheet" href="{{ asset('assets/back/vendor/fonts/boxicons.css') }}" />

    {{-- CSS de base du template --}}
    <link rel="stylesheet" href="{{ asset('assets/back/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/back/vendor/css/theme-default.css') }}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/back/css/demo.css') }}" />

    {{-- CSS des vendors --}}
    <link rel="stylesheet" href="{{ asset('assets/back/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/back/vendor/libs/apex-charts/apex-charts.css') }}" />

    {{-- Styles ajoutés pour l'application (aucun @vite : pas de conflit avec Breeze) --}}
    <link rel="stylesheet" href="{{ asset('assets/back/css/app-custom.css') }}" />

    {{-- Helpers obligatoires du template --}}
    <script src="{{ asset('assets/back/vendor/js/helpers.js') }}"></script>

    {{-- Fichier de configuration du thème --}}
    <script src="{{ asset('assets/back/js/config.js') }}"></script>

    @stack('styles')
</head>

<body>
    {{-- Conteneur principal du back office --}}
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            {{-- Menu latéral --}}
            @include('back.partials.sidebar')

            {{-- Page --}}
            <div class="layout-page">
                {{-- Barre supérieure --}}
                @include('back.partials.topbar')

                {{-- Contenu --}}
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        {{-- Messages flash (succès / erreur) --}}
                        @include('back.partials.flash-messages')

                        @yield('content')
                    </div>

                    {{-- Pied de page --}}
                    @include('back.partials.footer')

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- / Content wrapper -->
            </div>
            <!-- / Layout page -->

            {{-- Overlay du menu mobile --}}
            <div class="layout-overlay layout-menu-toggle"></div>
        </div>
        <!-- / Layout wrapper -->
    </div>

    {{-- Scripts de base du template --}}
    <script src="{{ asset('assets/back/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/back/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/back/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/back/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/back/vendor/js/menu.js') }}"></script>

    {{-- Scripts des vendors --}}
    <script src="{{ asset('assets/back/vendor/libs/apex-charts/apexcharts.js') }}"></script>

    {{-- Script principal --}}
    <script src="{{ asset('assets/back/js/main.js') }}"></script>

    {{-- Désactivation globale de la validation native HTML5 pour laisser le contrôle aux validations serveur Laravel --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('form').forEach(function(form) {
                form.setAttribute('novalidate', 'novalidate');
            });
        });
    </script>

    @stack('scripts')
</body>

</html>
