{{-- Formulaire : mise à jour des informations personnelles --}}
<h2>Informations <span>personnelles</span></h2>
<p>Modifiez votre nom et votre adresse e-mail.</p>

<form method="POST" action="{{ route('profile.update') }}" class="ve-contact-form" novalidate>
    @csrf
    @method('PATCH')

    <x-form-input
        name="name"
        label="Nom complet"
        :value="$user->name"
        required
        autocomplete="name"
    />

    <x-form-input
        name="email"
        type="email"
        label="Adresse e-mail"
        :value="$user->email"
        required
        autocomplete="email"
    />

    <button type="submit" class="ve-btn-primary">Enregistrer les modifications</button>
</form>
