<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="@yield('description', 'Aide les habitants du quartier à anticiper les canicules et les coupures de courant.')">

    {{-- Titre dynamique de la page --}}
    <title>@yield('title', 'Accueil') | {{ config('app.name') }}</title>

    <link rel="icon" href="{{ asset('assets/front/img/core-img/favicon.ico') }}">

    {{-- Styles du template Front Office (aucun @vite : pas de conflit avec Breeze) --}}
    <link rel="stylesheet" href="{{ asset('assets/front/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/front/css/custom-override.css') }}">
    {{-- Styles ajoutés pour l'application (messages flash, formulaires, erreurs) --}}
    <link rel="stylesheet" href="{{ asset('assets/front/css/app-custom.css') }}">

    @stack('styles')
</head>

<body>
    {{-- Préchargeur du template --}}
    <div class="preloader d-flex align-items-center justify-content-center">
        <div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>
    </div>

    {{-- En-tête / menu de navigation --}}
    @include('front.partials.header')

    <main id="contenu-principal">
        {{-- Messages flash (succès / erreur) --}}
        @include('front.partials.flash-messages')

        @yield('content')
    </main>

    {{-- Pied de page --}}
    @include('front.partials.footer')

    {{-- Scripts du template Front Office --}}
    <script src="{{ asset('assets/front/js/jquery/jquery-2.2.4.min.js') }}"></script>
    <script src="{{ asset('assets/front/js/bootstrap/popper.min.js') }}"></script>
    <script src="{{ asset('assets/front/js/bootstrap/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/front/js/plugins/plugins.js') }}"></script>
    <script src="{{ asset('assets/front/js/active.js') }}"></script>
    <script src="{{ asset('assets/front/js/vaultedge.js') }}"></script>

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
