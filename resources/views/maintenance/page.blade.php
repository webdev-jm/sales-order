{{--
    The maintenance page, shown by EnvMaintenanceMode (MAINTENANCE_MODE in
    .env) and by `php artisan down` (errors/503). It stands alone: no layout,
    session or database, only files under public/.

    A three.js scene of a network hub moves behind the message, and
    "Squash the bugs" can be played while waiting: tap the red bugs before
    they reach the hub, leave the blue data packets alone. The page checks
    every 30 seconds and reloads once the site is back.

    @param string $message
    @param \Illuminate\Support\Carbon|null $until
--}}
@php
    $words = [
        'best' => __('New best score!'),
        'score' => __('Best so far: :best'),
        'checking' => __('Checking if the system is back…'),
        'still' => __('Still being updated. Checking again in 30 seconds.'),
        'back' => __('The system is back. Reloading…'),
        'now' => __('any moment now'),
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex">
    <title>{{ __('Under maintenance') }} · {{ config('adminlte.title', 'Sales Management System') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <style>
        :root {
            --bg: #050b1a;
            --panel: rgba(13, 26, 56, 0.72);
            --line: rgba(147, 197, 253, 0.22);
            --text: #e8eefb;
            --muted: #9fb2d3;
            --accent: #3b82f6;
            --amber: #fbbf24;
            --danger: #f87171;
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; height: 100%; background: var(--bg); color: var(--text); font-family: Inter, 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, 'Helvetica Neue', Arial, sans-serif; overflow: hidden; }
        #scene { position: fixed; inset: 0; width: 100%; height: 100%; display: block; touch-action: none; }
        body.playing #scene { cursor: crosshair; }

        .panel { position: fixed; left: max(16px, env(safe-area-inset-left)); top: max(16px, env(safe-area-inset-top)); width: min(420px, calc(100% - 32px)); padding: 22px 24px; border-radius: 18px;
            background: var(--panel); border: 1px solid var(--line); backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px); box-shadow: 0 24px 60px rgba(0, 0, 0, 0.45); transition: opacity .35s, transform .35s; }
        body.playing .panel { opacity: 0; transform: translateY(-12px); pointer-events: none; }
        .logo { height: 64px; display: block; margin: 0 0 12px; }
        .eyebrow { display: inline-flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 600; letter-spacing: .12em; text-transform: uppercase; color: var(--amber); }
        .eyebrow::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: var(--amber); box-shadow: 0 0 0 0 rgba(251, 191, 36, .7); animation: ping 1.8s infinite; }
        h1 { font-size: clamp(24px, 4vw, 30px); line-height: 1.2; margin: 10px 0 8px; font-weight: 750; letter-spacing: -.01em; }
        p { margin: 0 0 12px; color: var(--muted); line-height: 1.55; font-size: 15px; }
        .until { display: flex; gap: 10px; align-items: baseline; flex-wrap: wrap; margin: 4px 0 16px; padding: 10px 12px; border-radius: 12px; background: rgba(59, 130, 246, .12); border: 1px solid var(--line); font-size: 14px; }
        .until strong { font-variant-numeric: tabular-nums; font-size: 18px; color: #fff; }
        .actions { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
        button { font: inherit; cursor: pointer; border: 0; border-radius: 12px; padding: 11px 18px; font-weight: 650; font-size: 15px; transition: transform .15s, box-shadow .15s, background .15s; }
        button:focus-visible { outline: 3px solid var(--amber); outline-offset: 2px; }
        .primary { background: linear-gradient(135deg, #2563eb, #3b82f6); color: #fff; box-shadow: 0 8px 22px rgba(37, 99, 235, .45); }
        .primary:hover { transform: translateY(-1px); box-shadow: 0 12px 28px rgba(37, 99, 235, .55); }
        .ghost { background: transparent; color: var(--muted); border: 1px solid var(--line); }
        .ghost:hover { color: #fff; border-color: rgba(147, 197, 253, .5); }
        .status { margin-top: 14px; font-size: 12.5px; color: var(--muted); }

        .hud { position: fixed; top: max(14px, env(safe-area-inset-top)); left: 50%; transform: translate(-50%, -20px); display: flex; gap: 10px; align-items: center; opacity: 0; pointer-events: none; transition: opacity .3s, transform .3s; }
        body.playing .hud { opacity: 1; transform: translate(-50%, 0); pointer-events: auto; }
        .chip { padding: 8px 14px; border-radius: 999px; background: var(--panel); border: 1px solid var(--line); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); font-size: 14px; font-variant-numeric: tabular-nums; white-space: nowrap; }
        .chip b { color: #fff; }
        .lives { letter-spacing: 2px; color: var(--danger); }
        .hud .ghost { padding: 7px 12px; font-size: 13px; background: var(--panel); }
        .hint { position: fixed; bottom: max(18px, env(safe-area-inset-bottom)); left: 50%; transform: translateX(-50%); font-size: 13px; color: var(--muted); opacity: 0; transition: opacity .4s; text-align: center; padding: 0 16px; pointer-events: none; }
        body.playing .hint { opacity: 1; }
        .float { position: fixed; pointer-events: none; font-weight: 800; font-size: 16px; color: #fff; text-shadow: 0 2px 8px rgba(0,0,0,.6); transform: translate(-50%, -50%); animation: rise .8s ease-out forwards; }
        .float.bad { color: var(--danger); }

        .over { position: fixed; inset: 0; display: none; align-items: center; justify-content: center; padding: 16px; background: rgba(5, 11, 26, .55); }
        .over.show { display: flex; }
        .over .card { width: min(360px, 100%); text-align: center; padding: 26px 24px; border-radius: 18px; background: var(--panel); border: 1px solid var(--line); backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px); }
        .over .score { font-size: 46px; font-weight: 800; margin: 6px 0 2px; font-variant-numeric: tabular-nums; }
        .over .actions { justify-content: center; margin-top: 16px; }
        .fallback { display: none; position: fixed; inset: 0; background: radial-gradient(circle at 70% 40%, #1d4ed8 0%, transparent 45%), var(--bg); }
        body.no-webgl .fallback { display: block; }
        body.no-webgl #play { display: none; }

        @keyframes ping { 0% { box-shadow: 0 0 0 0 rgba(251, 191, 36, .7); } 80%, 100% { box-shadow: 0 0 0 10px rgba(251, 191, 36, 0); } }
        @keyframes rise { from { opacity: 1; margin-top: 0; } to { opacity: 0; margin-top: -42px; } }
        @media (max-width: 560px) {
            .panel { top: auto; bottom: max(16px, env(safe-area-inset-bottom)); padding: 18px; }
            .logo { height: 52px; }
            p { font-size: 14px; }
        }
        @media (prefers-reduced-motion: reduce) { .eyebrow::before { animation: none; } }
    </style>
</head>
<body>
    <div class="fallback" aria-hidden="true"></div>
    <canvas id="scene" aria-hidden="true"></canvas>

    <main class="panel" aria-live="polite">
        <img class="logo" src="{{ asset('images/sales-order-logo2.png') }}" alt="Sales Management System">
        <span class="eyebrow">{{ __('Under maintenance') }}</span>
        <h1>{{ __('We are making the Sales Management System better.') }}</h1>
        <p>{{ $message }}</p>
        @if ($until)
            <div class="until" data-until="{{ $until->toIso8601String() }}">
                <span>{{ __('Expected back') }} {{ $until->format('D, d M Y g:i A') }}</span>
                <strong id="countdown"></strong>
            </div>
        @endif
        <div class="actions">
            <button type="button" class="primary" id="play">{{ __('Play while you wait') }}</button>
            <button type="button" class="ghost" id="retry">{{ __('Try again') }}</button>
        </div>
        <div class="status" id="status">{{ __('This page reloads by itself once the system is back.') }}</div>
    </main>

    <div class="hud" role="status">
        <span class="chip">{{ __('Score') }} <b id="score">0</b></span>
        <span class="chip lives" id="lives" aria-label="{{ __('Lives') }}">♥♥♥</span>
        <span class="chip">{{ __('Best') }} <b id="best">0</b></span>
        <button type="button" class="ghost" id="quit">{{ __('Stop') }}</button>
    </div>
    <div class="hint">{{ __('Tap the red bugs before they reach the hub. Leave the blue data packets alone.') }}</div>

    <div class="over" id="over" role="dialog" aria-modal="true" aria-labelledby="over-title">
        <div class="card">
            <div class="eyebrow" id="over-title">{{ __('The hub was overrun') }}</div>
            <div class="score" id="final">0</div>
            <p id="final-note"></p>
            <div class="actions">
                <button type="button" class="primary" id="again">{{ __('Play again') }}</button>
                <button type="button" class="ghost" id="leave">{{ __('Back') }}</button>
            </div>
        </div>
    </div>

    <script type="importmap">{"imports": {"three": "{{ asset('vendor/three/three.module.min.js') }}"}}</script>
    <script type="module">
        import * as THREE from 'three';

        const words = {{ \Illuminate\Support\Js::from($words) }};
        const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const $ = (id) => document.getElementById(id);
        const store = {
            get(key) { try { return Number(localStorage.getItem(key)) || 0; } catch (e) { return 0; } },
            set(key, value) { try { localStorage.setItem(key, String(value)); } catch (e) { /* private window */ } },
        };

        // ── Back yet? ─────────────────────────────────────────────────────
        async function check() {
            $('status').textContent = words.checking;
            try {
                const response = await fetch(window.location.href, { cache: 'no-store', headers: { Accept: 'text/html' } });
                if (response.status !== 503) {
                    $('status').textContent = words.back;
                    window.location.reload();
                    return;
                }
            } catch (e) { /* offline: try again later */ }
            $('status').textContent = words.still;
        }
        setInterval(check, 30000);
        $('retry').addEventListener('click', check);

        const untilBox = document.querySelector('[data-until]');
        if (untilBox) {
            const target = new Date(untilBox.dataset.until).getTime();
            const tick = () => {
                const left = Math.max(0, Math.round((target - Date.now()) / 1000));
                const h = Math.floor(left / 3600), m = Math.floor((left % 3600) / 60), s = left % 60;
                $('countdown').textContent = left === 0 ? words.now : (h ? h + 'h ' : '') + String(m).padStart(2, '0') + 'm ' + String(s).padStart(2, '0') + 's';
            };
            tick();
            setInterval(tick, 1000);
        }

        // ── The hub ───────────────────────────────────────────────────────
        const canvas = $('scene');
        let renderer;
        try {
            renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: false });
        } catch (e) {
            document.body.classList.add('no-webgl');
            throw e;
        }
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        renderer.setClearColor(0x050b1a, 1);

        const scene = new THREE.Scene();
        scene.fog = new THREE.FogExp2(0x050b1a, 0.045);
        const camera = new THREE.PerspectiveCamera(50, 1, 0.1, 100);
        camera.position.set(0, 0, 11);

        scene.add(new THREE.AmbientLight(0x6b8cff, 0.6));
        const key = new THREE.PointLight(0x93c5fd, 60, 40);
        key.position.set(4, 5, 8);
        scene.add(key);

        const network = new THREE.Group();
        scene.add(network);

        const hubMaterial = new THREE.MeshStandardMaterial({ color: 0x1d4ed8, emissive: 0x1e40af, emissiveIntensity: 0.9, roughness: 0.35, metalness: 0.3 });
        const hub = new THREE.Mesh(new THREE.IcosahedronGeometry(0.85, 1), hubMaterial);
        network.add(hub);
        const shell = new THREE.Mesh(new THREE.IcosahedronGeometry(1.25, 1), new THREE.MeshBasicMaterial({ color: 0x93c5fd, wireframe: true, transparent: true, opacity: 0.25 }));
        network.add(shell);

        const RADIUS = 3.1;
        const nodes = [];
        const nodeSpots = [];
        for (let i = 0; i < 6; i++) {
            const angle = -Math.PI / 2 + i * Math.PI / 3;
            const spot = new THREE.Vector3(Math.cos(angle) * RADIUS, Math.sin(angle) * RADIUS, 0);
            nodeSpots.push(spot);
            const amber = i === 1;
            const node = new THREE.Mesh(new THREE.SphereGeometry(amber ? 0.32 : 0.28, 24, 16), new THREE.MeshStandardMaterial({
                color: amber ? 0xfbbf24 : 0xffffff, emissive: amber ? 0xb45309 : 0x3b82f6, emissiveIntensity: amber ? 0.8 : 0.35, roughness: 0.4,
            }));
            node.position.copy(spot);
            node.userData.phase = i;
            network.add(node);
            nodes.push(node);
        }

        const lineMaterial = new THREE.LineBasicMaterial({ color: 0x60a5fa, transparent: true, opacity: 0.55 });
        nodeSpots.forEach((spot) => network.add(new THREE.Line(new THREE.BufferGeometry().setFromPoints([new THREE.Vector3(), spot]), lineMaterial)));
        network.add(new THREE.LineLoop(new THREE.BufferGeometry().setFromPoints(nodeSpots), new THREE.LineBasicMaterial({ color: 0x93c5fd, transparent: true, opacity: 0.25 })));

        // Data flowing between the apps and the hub.
        const flows = [];
        const flowGeometry = new THREE.SphereGeometry(0.07, 8, 8);
        for (let i = 0; i < 18; i++) {
            const flow = new THREE.Mesh(flowGeometry, new THREE.MeshBasicMaterial({ color: i % 5 === 0 ? 0xfbbf24 : 0xbfdbfe }));
            flow.userData = { spoke: i % 6, t: Math.random(), speed: 0.12 + Math.random() * 0.18, out: i % 2 === 0 };
            network.add(flow);
            flows.push(flow);
        }

        // Stars.
        const starCount = 900;
        const starPositions = new Float32Array(starCount * 3);
        for (let i = 0; i < starCount; i++) {
            const r = 18 + Math.random() * 30, theta = Math.random() * Math.PI * 2, phi = Math.acos(2 * Math.random() - 1);
            starPositions.set([r * Math.sin(phi) * Math.cos(theta), r * Math.sin(phi) * Math.sin(theta), r * Math.cos(phi) - 10], i * 3);
        }
        const starGeometry = new THREE.BufferGeometry();
        starGeometry.setAttribute('position', new THREE.BufferAttribute(starPositions, 3));
        const stars = new THREE.Points(starGeometry, new THREE.PointsMaterial({ color: 0x9fb2d3, size: 0.08, transparent: true, opacity: 0.8 }));
        scene.add(stars);

        // ── Pointer ───────────────────────────────────────────────────────
        const pointer = new THREE.Vector2();
        const tilt = { x: 0, y: 0 };
        window.addEventListener('pointermove', (event) => {
            pointer.x = (event.clientX / window.innerWidth) * 2 - 1;
            pointer.y = -(event.clientY / window.innerHeight) * 2 + 1;
        });

        function resize() {
            const w = window.innerWidth, h = window.innerHeight;
            renderer.setSize(w, h, false);
            camera.aspect = w / h;
            // Keep the whole board in view on a narrow phone.
            camera.position.z = w < h ? 11 * Math.min(1.7, h / w * 0.85) : 11;
            camera.updateProjectionMatrix();
            // Leave room for the message on a wide screen.
            network.position.x = w > 900 && !game.on ? 2.2 : 0;
            // On a phone the message sits at the bottom, so the hub moves up until a game starts.
            network.position.y = w < h && !game.on ? 2.6 : 0;
        }

        // ── Squash the bugs ───────────────────────────────────────────────
        const game = { on: false, score: 0, lives: 3, squashed: 0, spawnIn: 0, things: [], bursts: [], shake: 0 };
        const bugGeometry = new THREE.OctahedronGeometry(0.26, 0);
        const packetGeometry = new THREE.BoxGeometry(0.3, 0.3, 0.3);
        const hitGeometry = new THREE.SphereGeometry(0.6, 8, 8);
        const hitMaterial = new THREE.MeshBasicMaterial({ visible: false });
        const raycaster = new THREE.Raycaster();
        $('best').textContent = store.get('bevi-maintenance-best');

        function speed() { return 0.9 + Math.min(2.2, game.squashed * 0.06); }

        function spawn() {
            const bug = Math.random() < 0.72;
            const angle = Math.random() * Math.PI * 2;
            const holder = new THREE.Mesh(hitGeometry, hitMaterial);
            holder.position.set(Math.cos(angle) * 8.5, Math.sin(angle) * 6, (Math.random() - 0.5) * 2);
            const body = new THREE.Mesh(bug ? bugGeometry : packetGeometry, new THREE.MeshStandardMaterial({
                color: bug ? 0xef4444 : 0x60a5fa, emissive: bug ? 0x991b1b : 0x1d4ed8, emissiveIntensity: 0.8, roughness: 0.3,
            }));
            holder.add(body);
            holder.userData = { bug, body, wobble: Math.random() * 10, speed: speed() * (bug ? 1 : 0.8) };
            scene.add(holder);
            game.things.push(holder);
        }

        function burst(at, color) {
            const count = 26, positions = new Float32Array(count * 3), velocities = [];
            for (let i = 0; i < count; i++) {
                positions.set([at.x, at.y, at.z], i * 3);
                velocities.push(new THREE.Vector3((Math.random() - 0.5) * 6, (Math.random() - 0.5) * 6, (Math.random() - 0.5) * 6));
            }
            const geometry = new THREE.BufferGeometry();
            geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
            const points = new THREE.Points(geometry, new THREE.PointsMaterial({ color, size: 0.14, transparent: true, opacity: 1 }));
            points.userData = { velocities, life: 0.8 };
            scene.add(points);
            game.bursts.push(points);
        }

        function float(text, x, y, bad = false) {
            const label = document.createElement('div');
            label.className = 'float' + (bad ? ' bad' : '');
            label.textContent = text;
            label.style.left = x + 'px';
            label.style.top = y + 'px';
            document.body.appendChild(label);
            setTimeout(() => label.remove(), 800);
        }

        function remove(thing) {
            scene.remove(thing);
            thing.userData.body.material.dispose();
            game.things = game.things.filter((other) => other !== thing);
        }

        function hud() {
            $('score').textContent = game.score;
            $('lives').textContent = '♥'.repeat(game.lives) + '♡'.repeat(Math.max(0, 3 - game.lives));
        }

        function hurt() {
            game.lives -= 1;
            game.shake = 0.5;
            hubMaterial.emissive.setHex(0xb91c1c);
            setTimeout(() => hubMaterial.emissive.setHex(0x1e40af), 220);
            hud();
            if (game.lives <= 0) end();
        }

        function start() {
            game.things.slice().forEach(remove);
            Object.assign(game, { on: true, score: 0, lives: 3, squashed: 0, spawnIn: 0.6 });
            $('over').classList.remove('show');
            document.body.classList.add('playing');
            hud();
            resize();
        }

        function end() {
            game.on = false;
            const best = store.get('bevi-maintenance-best');
            const beaten = game.score > best;
            if (beaten) store.set('bevi-maintenance-best', game.score);
            $('best').textContent = Math.max(best, game.score);
            $('final').textContent = game.score;
            $('final-note').textContent = beaten ? words.best : words.score.replace(':best', best);
            $('over').classList.add('show');
            $('again').focus();
        }

        function stop() {
            game.on = false;
            game.things.slice().forEach(remove);
            $('over').classList.remove('show');
            document.body.classList.remove('playing');
            resize();
            $('play').focus();
        }

        canvas.addEventListener('pointerdown', (event) => {
            if (!game.on) return;
            const at = new THREE.Vector2((event.clientX / window.innerWidth) * 2 - 1, -(event.clientY / window.innerHeight) * 2 + 1);
            raycaster.setFromCamera(at, camera);
            const hit = raycaster.intersectObjects(game.things, false)[0];
            if (!hit) return;
            const thing = hit.object;
            if (thing.userData.bug) {
                game.squashed += 1;
                const points = 10 + Math.floor(game.squashed / 10) * 5;
                game.score += points;
                burst(thing.position, 0xf87171);
                float('+' + points, event.clientX, event.clientY);
            } else {
                burst(thing.position, 0x60a5fa);
                float('−♥', event.clientX, event.clientY, true);
                hurt();
            }
            remove(thing);
            hud();
        });

        $('play').addEventListener('click', start);
        $('again').addEventListener('click', start);
        $('quit').addEventListener('click', stop);
        $('leave').addEventListener('click', stop);
        window.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && document.body.classList.contains('playing')) stop();
        });

        // ── Loop ──────────────────────────────────────────────────────────
        const clock = new THREE.Clock();
        const hubCenter = new THREE.Vector3();
        let paused = false;
        document.addEventListener('visibilitychange', () => { paused = document.hidden; clock.getDelta(); });

        function frame() {
            requestAnimationFrame(frame);
            if (paused) return;
            const dt = Math.min(clock.getDelta(), 0.05);
            const t = clock.elapsedTime;
            const calm = reduced ? 0.25 : 1;

            tilt.x += ((game.on ? 0 : pointer.y * 0.35) - tilt.x) * 0.05;
            tilt.y += ((game.on ? 0 : pointer.x * 0.5) - tilt.y) * 0.05;
            network.rotation.x = tilt.x;
            network.rotation.y = tilt.y;
            network.rotation.z += dt * 0.08 * calm * (game.on ? 0.3 : 1);
            hub.rotation.x += dt * 0.4 * calm;
            hub.rotation.y += dt * 0.6 * calm;
            shell.rotation.y -= dt * 0.25 * calm;
            stars.rotation.y += dt * 0.01 * calm;
            nodes.forEach((node) => node.scale.setScalar(1 + Math.sin(t * 2 + node.userData.phase) * 0.08 * calm));

            flows.forEach((flow) => {
                const data = flow.userData;
                data.t += dt * data.speed * calm;
                if (data.t > 1) { data.t = 0; data.spoke = Math.floor(Math.random() * 6); }
                const along = data.out ? data.t : 1 - data.t;
                flow.position.copy(nodeSpots[data.spoke]).multiplyScalar(along);
            });

            if (game.on) {
                game.spawnIn -= dt;
                if (game.spawnIn <= 0) {
                    spawn();
                    game.spawnIn = Math.max(0.35, 1.3 - game.squashed * 0.02) * (0.7 + Math.random() * 0.6);
                }
                network.getWorldPosition(hubCenter);
                for (const thing of game.things.slice()) {
                    const data = thing.userData;
                    const towards = hubCenter.clone().sub(thing.position);
                    const distance = towards.length();
                    if (distance < 1.1) {
                        remove(thing);
                        if (data.bug) {
                            hurt();
                        } else {
                            game.score += 1;
                            hud();
                        }
                        if (!game.on) break;
                        continue;
                    }
                    thing.position.add(towards.normalize().multiplyScalar(data.speed * dt));
                    data.wobble += dt * 6;
                    data.body.rotation.x += dt * 2.4;
                    data.body.rotation.y += dt * 3.1;
                    if (data.bug) data.body.position.y = Math.sin(data.wobble) * 0.12;
                }
            }

            for (const points of game.bursts.slice()) {
                const data = points.userData;
                data.life -= dt;
                const positions = points.geometry.attributes.position;
                data.velocities.forEach((velocity, i) => {
                    positions.setXYZ(i, positions.getX(i) + velocity.x * dt, positions.getY(i) + velocity.y * dt, positions.getZ(i) + velocity.z * dt);
                });
                positions.needsUpdate = true;
                points.material.opacity = Math.max(0, data.life / 0.8);
                if (data.life <= 0) {
                    scene.remove(points);
                    points.geometry.dispose();
                    points.material.dispose();
                    game.bursts = game.bursts.filter((other) => other !== points);
                }
            }

            if (game.shake > 0) {
                game.shake = Math.max(0, game.shake - dt);
                camera.position.x = (Math.random() - 0.5) * game.shake * 0.6;
                camera.position.y = (Math.random() - 0.5) * game.shake * 0.6;
            } else {
                camera.position.x += (0 - camera.position.x) * 0.1;
                camera.position.y += (0 - camera.position.y) * 0.1;
            }

            renderer.render(scene, camera);
        }

        window.addEventListener('resize', resize);
        resize();
        frame();
    </script>
</body>
</html>
