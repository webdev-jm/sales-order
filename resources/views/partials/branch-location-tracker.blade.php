@auth
    {{-- Fills a sign-in form's accuracy, longitude and latitude from the phone. The account and branch sign-in
         forms call it from x-init, which runs whenever Livewire adds the form to the page. --}}
    <script>
        window.SmsGeolocation = {
            fill: function (wire) {
                if (!navigator.geolocation) {
                    console.log('Geolocation is not supported by this browser.');
                    return;
                }
                navigator.geolocation.getCurrentPosition(function (position) {
                    wire.accuracy = position.coords.accuracy.toFixed(3) + ' m';
                    wire.longitude = position.coords.longitude;
                    wire.latitude = position.coords.latitude;
                }, function (error) {
                    wire.accuracy = error.message;
                });
            }
        };
    </script>

    @php
        $tracked_branch_login_id = session()->has('logged_branch') ? session('logged_branch')->id : null;
    @endphp
    {{-- Store location records. While a branch session is open the phone reads its GPS position about once a
         minute and keeps it in this browser first, so the trail is recorded even with no connection. What it
         holds is sent to the branch login (BranchLocationController@store) every few minutes when online, and
         the rest goes with the branch sign-out (AccountBranchLoginForm::logout). Readings still on the phone
         after that, because it was offline, are sent from whichever page the user opens next.
         To keep storage, requests and battery use down it
         - records from one browser tab only, when several are open,
         - skips a reading within 25 m of the last one kept, keeping one every 5 minutes while the user stays put,
         - skips readings vaguer than 200 m (the browser's network-based fallback). --}}
    <script>
        (function () {
            var endpoint = "{{ route('branch-location.store') }}";
            var token = "{{ csrf_token() }}";
            var userId = {{ (int) auth()->id() }};
            var branchLoginId = {{ $tracked_branch_login_id ? (int) $tracked_branch_login_id : 'null' }};
            var readEveryMs = 60000;
            var syncEveryMs = 10 * 60000;
            var stationaryMeters = 25;
            var heartbeatMs = 5 * 60000;
            var worstAccuracyMeters = 200;
            var maxKept = 1500;
            var batchSize = 120;
            var storeKey = 'sms.location.trail';
            var attemptKey = 'sms.location.lastAttempt';
            var syncing = false;

            function load() {
                try {
                    return JSON.parse(localStorage.getItem(storeKey)) || {};
                } catch (e) {
                    return {};
                }
            }

            function save(store) {
                try {
                    localStorage.setItem(storeKey, JSON.stringify(store));
                } catch (e) { /* storage blocked or full: the readings of this page stay in memory only */ }
            }

            function keyFor(loginId) {
                return userId + ':' + loginId;
            }

            function metersBetween(a, b) {
                var rad = Math.PI / 180;
                var dLat = (b.latitude - a.latitude) * rad;
                var dLon = (b.longitude - a.longitude) * rad;
                var h = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                    Math.cos(a.latitude * rad) * Math.cos(b.latitude * rad) * Math.sin(dLon / 2) * Math.sin(dLon / 2);
                return 2 * 6371000 * Math.asin(Math.min(1, Math.sqrt(h)));
            }

            function keep(point) {
                var store = load();
                var entry = store[keyFor(branchLoginId)] || { last: null, points: [] };
                var last = entry.last;
                if (last && metersBetween(last, point) < stationaryMeters && point.recorded_at - last.recorded_at < heartbeatMs) {
                    return;
                }
                entry.points.push(point);
                entry.points = entry.points.slice(-maxKept);
                entry.last = point;
                store[keyFor(branchLoginId)] = entry;
                save(store);
            }

            function post(loginId, points) {
                return fetch(endpoint, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ branch_login_id: loginId, points: points })
                }).then(function (response) {
                    if (response.ok || response.status === 409 || response.status === 422) {
                        return 'done'; // saved, or a branch login this user no longer has: either way drop the batch
                    }
                    return response.status === 401 || response.status === 419 ? 'signed-out' : 'retry';
                }).catch(function () {
                    return 'retry';
                });
            }

            // Send every batch this user's browser holds, oldest branch login first.
            function sync() {
                if (syncing || !navigator.onLine) {
                    return Promise.resolve();
                }
                syncing = true;

                var next = function () {
                    var store = load();
                    var key = Object.keys(store).filter(function (k) {
                        return k.indexOf(userId + ':') === 0 && store[k].points.length;
                    })[0];
                    if (!key) {
                        return Promise.resolve();
                    }
                    var batch = store[key].points.slice(0, batchSize);
                    return post(+key.split(':')[1], batch).then(function (result) {
                        if (result !== 'done') {
                            return;
                        }
                        var fresh = load();
                        if (fresh[key]) {
                            fresh[key].points = fresh[key].points.slice(batch.length);
                            if (!fresh[key].points.length && +key.split(':')[1] !== branchLoginId) {
                                delete fresh[key];
                            }
                            save(fresh);
                        }
                        return next();
                    });
                };

                return next().finally(function () { syncing = false; });
            }

            // Used by the branch sign-out form: the readings still on the phone for this branch login go with the
            // sign-out. They are only removed here once the server has them; if the page leaves first they are sent
            // again later, and the server skips readings it already has.
            window.SmsLocationTrail = {
                pending: function () {
                    var entry = load()[keyFor(branchLoginId)];
                    return entry ? entry.points.slice() : [];
                },
                signedOut: function (sent) {
                    var store = load();
                    var entry = store[keyFor(branchLoginId)];
                    if (entry) {
                        entry.points = entry.points.slice(sent);
                        if (entry.points.length) {
                            store[keyFor(branchLoginId)] = entry;
                        } else {
                            delete store[keyFor(branchLoginId)];
                        }
                        save(store);
                    }
                    branchLoginId = null;
                }
            };

            function record() {
                if (!branchLoginId || !('geolocation' in navigator)) {
                    return;
                }
                var now = Date.now();
                var lastAttempt = +(localStorage.getItem(attemptKey) || 0);
                if (now - lastAttempt < readEveryMs - 15000) {
                    return; // another tab read the position a moment ago
                }
                try { localStorage.setItem(attemptKey, String(now)); } catch (e) {}

                navigator.geolocation.getCurrentPosition(function (position) {
                    var accuracy = position.coords.accuracy;
                    if (!branchLoginId || (accuracy && accuracy > worstAccuracyMeters)) {
                        return;
                    }
                    keep({
                        latitude: position.coords.latitude,
                        longitude: position.coords.longitude,
                        accuracy: accuracy ? Math.round(accuracy * 1000) / 1000 : null,
                        recorded_at: position.timestamp || now
                    });
                }, function () { /* permission denied or no fix: skip this minute */ }, {
                    enableHighAccuracy: true,
                    maximumAge: 20000,
                    timeout: 30000
                });
            }

            record();
            setInterval(record, readEveryMs);
            setTimeout(sync, 5000);
            setInterval(sync, syncEveryMs);
            window.addEventListener('online', sync);
            document.addEventListener('visibilitychange', function () {
                if (document.visibilityState === 'visible') {
                    record();
                }
            });
        })();
    </script>
@endauth
