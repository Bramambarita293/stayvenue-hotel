// Hero 3D "Golden Hour Lobby" — Three.js via jsDelivr, prosedural, tanpa aset binary.
// Skenario: gold-dust particles + emblem ring + wave floor di atas foto hero existing.
// Gagal aman: canvas tetap transparan, foto hero tetap tampil.
import * as THREE from 'https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.js';

(() => {
    const canvas = document.getElementById('promo-3d-canvas');
    if (!canvas) return;
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    if (!('WebGLRenderingContext' in window)) return;

    const hero = canvas.closest('section');
    const isMobile = window.matchMedia('(max-width: 768px)').matches;
    const DPR = Math.min(window.devicePixelRatio || 1, isMobile ? 1.5 : 2);

    let renderer;
    try {
        renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true });
    } catch {
        return;
    }
    renderer.setPixelRatio(DPR);
    renderer.setClearColor(0x000000, 0);

    const scene = new THREE.Scene();
    scene.fog = new THREE.FogExp2(0x0b1526, 0.045);
    const camera = new THREE.PerspectiveCamera(55, 1, 0.1, 100);
    camera.position.set(0, 1.4, 9);

    const GOLD = 0x5b8def;
    const GOLD_DIM = 0x1e3a8a;

    // 1. Gold dust particles
    const COUNT = isMobile ? 220 : 650;
    const pos = new Float32Array(COUNT * 3);
    const speed = new Float32Array(COUNT);
    for (let i = 0; i < COUNT; i++) {
        pos[i * 3] = (Math.random() - 0.5) * 22;
        pos[i * 3 + 1] = Math.random() * 10 - 2;
        pos[i * 3 + 2] = (Math.random() - 0.5) * 10 - 1;
        speed[i] = 0.15 + Math.random() * 0.5;
    }
    const pGeo = new THREE.BufferGeometry();
    pGeo.setAttribute('position', new THREE.BufferAttribute(pos, 3));
    const pMat = new THREE.PointsMaterial({
        color: GOLD, size: 0.045, transparent: true, opacity: 0.75,
        blending: THREE.AdditiveBlending, depthWrite: false, sizeAttenuation: true,
    });
    const dust = new THREE.Points(pGeo, pMat);
    scene.add(dust);

    // 2. Emblem: 2 torus + icosahedron, kanan hero
    const emblem = new THREE.Group();
    const ring1 = new THREE.Mesh(
        new THREE.TorusGeometry(1.5, 0.02, 12, 90),
        new THREE.MeshBasicMaterial({ color: GOLD, transparent: true, opacity: 0.55 })
    );
    const ring2 = new THREE.Mesh(
        new THREE.TorusGeometry(1.05, 0.015, 12, 80),
        new THREE.MeshBasicMaterial({ color: GOLD_DIM, transparent: true, opacity: 0.5 })
    );
    ring2.rotation.x = Math.PI / 3;
    const core = new THREE.Mesh(
        new THREE.IcosahedronGeometry(0.42, 0),
        new THREE.MeshBasicMaterial({ color: GOLD, wireframe: true, transparent: true, opacity: 0.7 })
    );
    emblem.add(ring1, ring2, core);
    emblem.position.set(isMobile ? 0 : 3.4, 1.6, -1.5);
    scene.add(emblem);

    // 3. Wave floor
    const waveGeo = new THREE.PlaneGeometry(26, 10, 60, 20);
    const waveMat = new THREE.MeshBasicMaterial({ color: GOLD_DIM, wireframe: true, transparent: true, opacity: 0.16 });
    const wave = new THREE.Mesh(waveGeo, waveMat);
    wave.rotation.x = -Math.PI / 2.4;
    wave.position.y = -1.8;
    scene.add(wave);
    const waveBase = waveGeo.attributes.position.array.slice();

    // Lights (untuk MeshStandard bila ditambah nanti; wireframe tak butuh, tapi murah)
    scene.add(new THREE.AmbientLight(0xffffff, 0.7));
    const key = new THREE.DirectionalLight(0xd7e3ff, 1.1);
    key.position.set(4, 6, 6);
    scene.add(key);

    // Parallax mouse (lerp halus)
    let mx = 0, my = 0, tx = 0, ty = 0;
    if (window.matchMedia('(pointer: fine)').matches) {
        window.addEventListener('pointermove', (e) => {
            tx = (e.clientX / window.innerWidth - 0.5) * 2;
            ty = (e.clientY / window.innerHeight - 0.5) * 2;
        }, { passive: true });
    }

    const resize = () => {
        const w = hero.clientWidth, h = hero.clientHeight;
        renderer.setSize(w, h, false);
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
    };
    resize();
    window.addEventListener('resize', resize);

    // Dolly-in intro
    let intro = 0;
    const clock = new THREE.Clock();
    let running = true;
    let rafId = 0;

    // Pause saat hero offscreen / tab hidden
    const io = new IntersectionObserver(([en]) => {
        running = en.isIntersecting && !document.hidden;
        if (running) loop();
    }, { threshold: 0.05 });
    io.observe(hero);
    document.addEventListener('visibilitychange', () => {
        running = !document.hidden;
        if (running) loop();
    });

    canvas.classList.add('is-ready');

    function loop() {
        if (!running) return;
        rafId = requestAnimationFrame(loop);
        const t = clock.getElapsedTime();
        const dt = Math.min(clock.getDelta() || 0.016, 0.05);

        if (intro < 1) intro = Math.min(1, intro + dt / 1.4);
        const ease = 1 - Math.pow(1 - intro, 3);
        camera.position.z = 11 - ease * 2;
        camera.position.x += ((mx * 0.6) - camera.position.x) * 0.04;
        camera.position.y += ((1.4 - my * 0.4) - camera.position.y) * 0.04;
        camera.lookAt(0, 1, 0);

        // Dust naik perlahan
        const arr = pGeo.attributes.position.array;
        for (let i = 0; i < COUNT; i++) {
            arr[i * 3 + 1] += speed[i] * dt * 0.5;
            arr[i * 3] += Math.sin(t * 0.4 + i) * dt * 0.05;
            if (arr[i * 3 + 1] > 8) arr[i * 3 + 1] = -2;
        }
        pGeo.attributes.position.needsUpdate = true;

        ring1.rotation.y = t * 0.12;
        ring1.rotation.x = Math.sin(t * 0.2) * 0.25;
        ring2.rotation.z = -t * 0.18;
        core.rotation.y = t * 0.3;
        core.rotation.x = t * 0.15;
        emblem.position.y = 1.6 + Math.sin(t * 0.5) * 0.18;

        // Wave
        const wp = waveGeo.attributes.position.array;
        for (let i = 0; i < wp.length; i += 3) {
            const x = waveBase[i], y = waveBase[i + 1];
            wp[i + 2] = Math.sin(x * 0.5 + t * 0.7) * 0.22 + Math.cos(y * 0.6 + t * 0.5) * 0.18;
        }
        waveGeo.attributes.position.needsUpdate = true;

        mx += (tx - mx) * 0.05;
        my += (ty - my) * 0.05;

        renderer.render(scene, camera);
    }
    loop();

    window.addEventListener('beforeunload', () => {
        cancelAnimationFrame(rafId);
        io.disconnect();
        scene.traverse((o) => {
            if (o.geometry) o.geometry.dispose();
            if (o.material) (Array.isArray(o.material) ? o.material : [o.material]).forEach((m) => m.dispose());
        });
        renderer.dispose();
    });
})();
