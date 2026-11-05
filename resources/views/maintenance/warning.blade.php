{{--
    The strip on every page before scheduled maintenance (MAINTENANCE_STARTS_AT): a live countdown to it,
    then a reload, which shows the maintenance page. The server sends the seconds left, so a wrong clock on
    the computer does not move the time. People let in while maintenance is on see a strip saying so.
--}}
@php
    $maintenanceSchedule = app(\App\Support\MaintenanceSchedule::class);
    $maintenanceSecondsLeft = $maintenanceSchedule->warningSecondsLeft();
    $maintenanceBypassed = request()->attributes->get(\App\Support\MaintenanceSchedule::BYPASS_ATTRIBUTE) === true;
@endphp

@if($maintenanceSecondsLeft !== null || $maintenanceBypassed)
    <style>
        .maintenance-warning {
            position: fixed;
            left: 50%;
            bottom: 1rem;
            transform: translateX(-50%);
            z-index: 2000;
            display: flex;
            align-items: center;
            gap: .75rem;
            width: max-content;
            max-width: calc(100% - 2rem);
            padding: .6rem .75rem .6rem 1rem;
            border-radius: .5rem;
            background: #fff4d6;
            color: #4a3500;
            border: 1px solid #f0c24b;
            box-shadow: 0 .5rem 1.5rem rgba(0, 0, 0, .18);
            font-size: .9rem;
            line-height: 1.35;
        }
        .maintenance-warning[hidden] { display: none; }
        /* Room under the page, so the strip never hides the last buttons on it. */
        body:has(.maintenance-warning:not([hidden])) { padding-bottom: 4.5rem; }
        .maintenance-warning__icon { flex: none; color: #b07800; }
        .maintenance-warning__time {
            font-variant-numeric: tabular-nums;
            font-weight: 700;
            font-size: 1.05rem;
            padding: .05rem .4rem;
            border-radius: .3rem;
            background: #4a3500;
            color: #fff4d6;
            white-space: nowrap;
        }
        .maintenance-warning__close {
            margin-left: .25rem;
            padding: 0 .35rem;
            border: 0;
            background: transparent;
            color: inherit;
            font-size: 1.25rem;
            line-height: 1;
            opacity: .7;
            cursor: pointer;
        }
        .maintenance-warning__close:hover,
        .maintenance-warning__close:focus-visible { opacity: 1; }
        .maintenance-warning.is-urgent { background: #ffe1dc; border-color: #e0705f; color: #5a1309; }
        .maintenance-warning.is-urgent .maintenance-warning__icon { color: #c0392b; }
        .maintenance-warning.is-urgent .maintenance-warning__time { background: #5a1309; color: #ffe1dc; }
        .maintenance-warning--on { background: #e7f0fb; border-color: #8fb3e0; color: #12345a; }
        .maintenance-warning--on .maintenance-warning__icon { color: #2d6cb5; }
        @media (max-width: 575.98px) {
            .maintenance-warning { left: 1rem; right: 1rem; bottom: .75rem; width: auto; max-width: none; transform: none; }
        }
    </style>

    @if($maintenanceSecondsLeft !== null)
        @php
            $maintenanceStartsAt = $maintenanceSchedule->startsAt();
        @endphp
        <div class="maintenance-warning" role="status" data-maintenance-warning data-seconds-left="{{ $maintenanceSecondsLeft }}">
            <svg class="maintenance-warning__icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
            <span data-maintenance-text>
                {{ __('The system goes into maintenance in') }}
                <span class="maintenance-warning__time" data-maintenance-countdown aria-hidden="true">{{ gmdate($maintenanceSecondsLeft >= 3600 ? 'G:i:s' : 'i:s', $maintenanceSecondsLeft) }}</span>
                {{ __('(at :time). Save your work before then.', ['time' => $maintenanceStartsAt->format('g:i A')]) }}
            </span>
            <button type="button" class="maintenance-warning__close" data-maintenance-hide aria-label="{{ __('Hide until the last minute') }}" title="{{ __('Hide until the last minute') }}">&times;</button>
        </div>

        <script>
            (function () {
                var bar = document.querySelector('[data-maintenance-warning]');

                if (!bar) {
                    return;
                }

                if (window.__maintenanceCountdown) {
                    clearInterval(window.__maintenanceCountdown);
                }

                var endsAt = Date.now() + Number(bar.dataset.secondsLeft) * 1000;
                var clock = bar.querySelector('[data-maintenance-countdown]');
                var hiddenByUser = false;

                function pad(n) { return (n < 10 ? '0' : '') + n; }

                function tick() {
                    var left = Math.max(0, Math.ceil((endsAt - Date.now()) / 1000));
                    var hours = Math.floor(left / 3600);
                    var minutes = Math.floor((left % 3600) / 60);
                    var seconds = left % 60;

                    clock.textContent = (hours > 0 ? hours + ':' + pad(minutes) : pad(minutes)) + ':' + pad(seconds);
                    bar.classList.toggle('is-urgent', left <= 60);

                    if (hiddenByUser && left <= 60) {
                        hiddenByUser = false;
                        bar.hidden = false;
                    }

                    if (left === 0) {
                        clearInterval(window.__maintenanceCountdown);
                        bar.hidden = false;
                        bar.querySelector('[data-maintenance-text]').textContent = @json(__('The system is going into maintenance now.'));
                        setTimeout(function () { window.location.reload(); }, 3000);
                    }
                }

                bar.querySelector('[data-maintenance-hide]').addEventListener('click', function () {
                    hiddenByUser = true;
                    bar.hidden = true;
                });

                tick();
                window.__maintenanceCountdown = setInterval(tick, 1000);
            })();
        </script>
    @else
        <div class="maintenance-warning maintenance-warning--on" role="status">
            <svg class="maintenance-warning__icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            <span>{{ __('Maintenance mode is on. Only the team can use the system right now.') }}</span>
        </div>
    @endif
@endif
