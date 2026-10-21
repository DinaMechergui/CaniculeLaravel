{{--
    Panneau de chat IA partagé (moteur OpenRouter) :
    - utilisé seul sur /assistant
    - intégré à la page « Points de fraîcheur » (contexte des filtres actifs)

    Variables :
    - $chatId      : préfixe des identifiants (ex. « ai-embed »)
    - $bienvenue   : message d'accueil de l'assistant
    - $questions   : tableau de questions suggérées (chips)
    - $titre       : (optionnel) titre du panneau intégré
    - $sousTitre   : (optionnel) sous-titre du panneau intégré
    - $avecFiltres : (optionnel) envoie les filtres de la page avec le message
--}}

<div class="ai-panel{{ ! empty($titre) ? ' ai-embed' : '' }}" id="{{ $chatId }}-panel">
    @if (! empty($titre))
        <div class="ai-embed-head">
            <span class="ve-section-tag">
                <i class="fa fa-bolt" aria-hidden="true"></i> Assistant IA
            </span>
            <h3>{{ $titre }}</h3>

            @if (! empty($sousTitre))
                <p>{{ $sousTitre }}</p>
            @endif
        </div>
    @endif

    {{-- Fil de conversation --}}
    <div class="ai-messages" id="{{ $chatId }}-messages" role="log" aria-live="polite" aria-label="Conversation avec l'assistant">
        <div class="ai-msg ai-bot">
            <div class="ai-avatar"><i class="fa fa-bolt" aria-hidden="true"></i></div>
            <div class="ai-bubble">{{ $bienvenue }}</div>
        </div>
    </div>

    {{-- Questions suggérées --}}
    <div class="ai-suggestions" aria-label="Questions suggérées">
        @foreach ($questions as $question)
            <button type="button" class="ai-chip" data-question="{{ $question }}">
                {{ $question }}
            </button>
        @endforeach
    </div>

    {{-- Saisie --}}
    <form class="ai-form" id="{{ $chatId }}-form" method="POST" action="{{ route('assistant.chat') }}" autocomplete="off">
        @csrf

        <label class="sr-only" for="{{ $chatId }}-input">Votre message</label>
        <textarea
            id="{{ $chatId }}-input"
            class="ai-input"
            name="message"
            rows="1"
            maxlength="1000"
            placeholder="Votre question… (Entrée pour envoyer)"
            required
        ></textarea>

        <button type="submit" class="ve-cta-btn ai-send" id="{{ $chatId }}-send">
            Envoyer <i class="fa fa-paper-plane" aria-hidden="true"></i>
        </button>
    </form>

    <p class="ai-hint">
        <i class="fa fa-info-circle" aria-hidden="true"></i>
        {{ $hint ?? 'Réponses générées par une intelligence artificielle : vérifiez toujours les horaires des lieux.' }}
        @if ($lienPleinEcran ?? true)
            <a href="{{ route('assistant.index') }}">Ouvrir l'assistant en pleine page</a>
        @endif
    </p>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var zone = document.getElementById('{{ $chatId }}-messages');
            var formulaire = document.getElementById('{{ $chatId }}-form');
            var champ = document.getElementById('{{ $chatId }}-input');
            var bouton = document.getElementById('{{ $chatId }}-send');
            var avecFiltres = {{ ($avecFiltres ?? false) ? 'true' : 'false' }};
            var historique = [];

            if (!zone || !formulaire || !champ || !bouton) {
                return;
            }

            /** Filtres de la page « Points de fraîcheur » (recherche + type). */
            function construireContexte() {
                if (!avecFiltres) {
                    return null;
                }

                var elements = [];
                var recherche = document.querySelector('.pf-filters input[name="search"]');
                var select = document.querySelector('.pf-filters select[name="type"]');

                if (recherche && recherche.value.trim()) {
                    elements.push('recherche = "' + recherche.value.trim() + '"');
                }

                if (select && select.value) {
                    elements.push('type de point = ' + select.options[select.selectedIndex].text.trim());
                }

                return elements.length ? elements.join(' ; ') : null;
            }

            /** Ajoute une bulle (texte nu : jamais d'innerHTML, pas d'injection). */
            function ajouterMessage(role, texte) {
                var ligne = document.createElement('div');
                ligne.className = 'ai-msg ' + (role === 'user' ? 'ai-user' : 'ai-bot');

                var avatar = document.createElement('div');
                avatar.className = 'ai-avatar';

                var icone = document.createElement('i');
                icone.className = role === 'user' ? 'fa fa-user' : 'fa fa-bolt';
                icone.setAttribute('aria-hidden', 'true');
                avatar.appendChild(icone);

                var bulle = document.createElement('div');
                bulle.className = 'ai-bubble';
                bulle.textContent = texte;

                ligne.appendChild(avatar);
                ligne.appendChild(bulle);
                zone.appendChild(ligne);
                zone.scrollTop = zone.scrollHeight;

                return bulle;
            }

            /** Ajoute l'indicateur « l'assistant écrit… ». */
            function ajouterIndicateur() {
                var ligne = document.createElement('div');
                ligne.className = 'ai-msg ai-bot ai-waiting';

                var avatar = document.createElement('div');
                avatar.className = 'ai-avatar';

                var icone = document.createElement('i');
                icone.className = 'fa fa-bolt';
                icone.setAttribute('aria-hidden', 'true');
                avatar.appendChild(icone);

                var bulle = document.createElement('div');
                bulle.className = 'ai-bubble ai-typing';
                bulle.textContent = "L'assistant écrit…";

                ligne.appendChild(avatar);
                ligne.appendChild(bulle);
                zone.appendChild(ligne);
                zone.scrollTop = zone.scrollHeight;

                return ligne;
            }

            formulaire.addEventListener('submit', function (evenement) {
                evenement.preventDefault();

                var message = champ.value.trim();

                if (message.length < 2 || bouton.disabled) {
                    return;
                }

                ajouterMessage('user', message);
                historique.push({ role: 'user', content: message });
                historique = historique.slice(-12);

                champ.value = '';
                bouton.disabled = true;

                var indicateur = ajouterIndicateur();
                var corps = {
                    _token: formulaire.querySelector('input[name="_token"]').value,
                    message: message,
                    historique: historique.slice(0, -1)
                };
                var contexte = construireContexte();

                if (contexte) {
                    corps.contexte = contexte;
                }

                fetch(formulaire.getAttribute('action'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(corps)
                })
                    .then(function (reponse) {
                        return reponse.json().then(function (donnees) {
                            return { ok: reponse.ok, donnees: donnees };
                        });
                    })
                    .then(function (resultat) {
                        indicateur.remove();

                        var texte = resultat.donnees && resultat.donnees.reponse
                            ? resultat.donnees.reponse
                            : "Désolé, je n'ai pas de réponse pour le moment.";

                        ajouterMessage('assistant', texte);
                        historique.push({ role: 'assistant', content: texte });
                        historique = historique.slice(-12);
                    })
                    .catch(function () {
                        indicateur.remove();
                        ajouterMessage('assistant', "Une erreur est survenue : vérifiez votre connexion puis réessayez.");
                    })
                    .finally(function () {
                        bouton.disabled = false;
                        champ.focus();
                    });
            });

            // Questions suggérées (uniquement celles de CE panneau).
            var panneau = zone.closest('.ai-panel');

            (panneau ? panneau.querySelectorAll('.ai-chip') : []).forEach(function (puce) {
                puce.addEventListener('click', function () {
                    champ.value = puce.getAttribute('data-question') || puce.textContent.trim();

                    if (typeof formulaire.requestSubmit === 'function') {
                        formulaire.requestSubmit();
                    } else {
                        formulaire.dispatchEvent(new Event('submit', { cancelable: true }));
                    }
                });
            });

            // Entrée : envoyer, Maj+Entrée : saut de ligne.
            champ.addEventListener('keydown', function (evenement) {
                if (evenement.key === 'Enter' && !evenement.shiftKey) {
                    evenement.preventDefault();

                    if (typeof formulaire.requestSubmit === 'function') {
                        formulaire.requestSubmit();
                    } else {
                        formulaire.dispatchEvent(new Event('submit', { cancelable: true }));
                    }
                }
            });
        });
    </script>
@endpush
