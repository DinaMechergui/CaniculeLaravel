@extends('back.layouts.back')

@section('title', 'Calendrier des réservations')

@section('content')
    <x-page-header title="Calendrier" subtitle="Vue planning des réservations par jour">
        @slot('actions')
            <a href="{{ route('admin.reservations.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-list-ul me-1"></i> Vue liste
            </a>
            <a href="{{ route('admin.reservations.check-in') }}" class="btn btn-outline-primary">
                <i class="bx bx-log-in me-1"></i> Check-in
            </a>
        @endslot
    </x-page-header>

    <div class="card">
        <div class="card-body">
            <div class="row g-2 align-items-end mb-3">
                <div class="col-md-4">
                    <label class="form-label" for="filtre-point">Point de fraîcheur</label>
                    <select class="form-control" id="filtre-point">
                        <option value="">Tous les points</option>
                        @foreach ($points as $point)
                            <option value="{{ $point->id }}">{{ $point->nom }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div id="calendrier-reservations"></div>

            <div class="mt-3 d-flex gap-3 flex-wrap small">
                <span><span class="badge bg-warning">&nbsp;</span> En attente</span>
                <span><span class="badge bg-success">&nbsp;</span> Confirmée</span>
                <span><span class="badge bg-info">&nbsp;</span> Liste d'attente</span>
                <span><span class="badge bg-primary">&nbsp;</span> Terminée</span>
                <span><span class="badge bg-secondary">&nbsp;</span> Annulée</span>
                <span><span class="badge bg-danger">&nbsp;</span> Non présenté</span>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendrier = new FullCalendar.Calendar(
                document.getElementById('calendrier-reservations'),
                {
                    locale: 'fr',
                    initialView: 'dayGridMonth',
                    height: 'auto',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,listWeek'
                    },
                    events: function (info, success, failure) {
                        const point = document.getElementById('filtre-point').value;
                        let url = @json(route('admin.reservations.events'));
                        if (point) {
                            url += '?point=' + encodeURIComponent(point);
                        }
                        fetch(url, { headers: { 'Accept': 'application/json' } })
                            .then(function (r) { return r.json(); })
                            .then(success)
                            .catch(failure);
                    },
                    eventClick: function (info) {
                        if (info.event.url) {
                            info.jsEvent.preventDefault();
                            window.location.href = info.event.url;
                        }
                    }
                }
            );
            calendrier.render();

            document.getElementById('filtre-point').addEventListener('change', function () {
                calendrier.refetchEvents();
            });
        });
    </script>
@endpush
