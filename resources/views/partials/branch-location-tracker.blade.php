@auth
    @if(session()->has('logged_branch'))
        {{-- Store location records: while a branch session is open, capture the
             user's GPS position once a minute and append it to the active branch
             login's location trail. Recording stops when the user signs out. --}}
        <script>
            (function () {
                var endpoint = "{{ route('branch-location.store') }}";
                var token = "{{ csrf_token() }}";
                var intervalMs = 60000;

                if (!('geolocation' in navigator)) {
                    return;
                }

                function recordLocation() {
                    navigator.geolocation.getCurrentPosition(function (position) {
                        fetch(endpoint, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                latitude: position.coords.latitude,
                                longitude: position.coords.longitude,
                                accuracy: position.coords.accuracy ? position.coords.accuracy.toFixed(3) + ' m' : null
                            })
                        }).catch(function () { 
                            console.log('location record failed to send to server');
                         });
                    }, function () { /* permission denied or unavailable - skip this tick */ }, {
                        enableHighAccuracy: true,
                        maximumAge: 0,
                        timeout: 30000
                    });
                }

                recordLocation();
                setInterval(recordLocation, intervalMs);
            })();
        </script>
    @endif
@endauth
