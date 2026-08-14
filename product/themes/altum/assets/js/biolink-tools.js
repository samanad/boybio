'use strict';

(() => {
    const root = document.getElementById('biolink-tools');
    if(!root) return;

    let enabled = [];
    try {
        enabled = JSON.parse(root.getAttribute('data-tools') || '[]');
    } catch(error) {
        enabled = [];
    }

    if(!Array.isArray(enabled) || !enabled.length) return;

    const has = id => enabled.indexOf(id) !== -1;
    const magnifier = root.getAttribute('data-magnifier') || 'light';

    const addClass = name => {
        document.documentElement.classList.add(name);
        document.body && document.body.classList.add(name);
    };

    if(has('magnifier')) {
        addClass('biolink-tool-magnifier-' + (magnifier === 'advanced' ? 'advanced' : 'light'));
    }

    if(has('contrast')) {
        addClass('biolink-tool-contrast');
    }

    if(has('earthquake')) {
        addClass('biolink-tool-earthquake');
        const rumble = () => {
            if(navigator.vibrate) {
                navigator.vibrate([70, 40, 90, 40, 120, 180]);
            }
        };
        rumble();
        window.setInterval(rumble, 1400);
        const start_on_gesture = () => {
            rumble();
            document.removeEventListener('touchstart', start_on_gesture);
            document.removeEventListener('click', start_on_gesture);
        };
        document.addEventListener('touchstart', start_on_gesture, {passive: true});
        document.addEventListener('click', start_on_gesture);
    }

    if(has('caffeine')) {
        let lock = null;
        const request_lock = async () => {
            if(!('wakeLock' in navigator)) return;
            try {
                lock = await navigator.wakeLock.request('screen');
                lock.addEventListener('release', () => {});
            } catch(error) {
                /* Browser may require a visible document */
            }
        };
        request_lock();
        document.addEventListener('visibilitychange', () => {
            if(document.visibilityState === 'visible') {
                request_lock();
            }
        });
    }

    const layer = document.createElement('div');
    layer.className = 'biolink-tools-layer';
    layer.setAttribute('aria-hidden', 'true');
    document.body.appendChild(layer);

    const make_canvas = () => {
        const canvas = document.createElement('canvas');
        canvas.className = 'biolink-tools-canvas';
        layer.appendChild(canvas);
        const ctx = canvas.getContext('2d');
        const resize = () => {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        };
        resize();
        window.addEventListener('resize', resize);
        return {canvas, ctx};
    };

    if(has('rain') || has('snow')) {
        const {canvas, ctx} = make_canvas();
        const count = has('rain') ? 70 : 50;
        const drops = [];
        for(let i = 0; i < count; i++) {
            drops.push({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height,
                s: has('rain') ? (8 + Math.random() * 10) : (1 + Math.random() * 2),
                v: has('rain') ? (6 + Math.random() * 7) : (1 + Math.random() * 1.8)
            });
        }
        const draw = () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.strokeStyle = has('rain') ? 'rgba(160,160,170,0.55)' : 'rgba(230,230,230,0.85)';
            ctx.fillStyle = 'rgba(240,240,240,0.9)';
            ctx.lineWidth = 1;
            drops.forEach(drop => {
                if(has('rain')) {
                    ctx.beginPath();
                    ctx.moveTo(drop.x, drop.y);
                    ctx.lineTo(drop.x, drop.y + drop.s);
                    ctx.stroke();
                    drop.y += drop.v;
                } else {
                    ctx.beginPath();
                    ctx.arc(drop.x, drop.y, drop.s, 0, Math.PI * 2);
                    ctx.fill();
                    drop.y += drop.v;
                    drop.x += Math.sin(drop.y / 40) * 0.4;
                }
                if(drop.y > canvas.height) {
                    drop.y = -10;
                    drop.x = Math.random() * canvas.width;
                }
            });
            window.requestAnimationFrame(draw);
        };
        draw();
    }

    if(has('night')) {
        const veil = document.createElement('div');
        veil.className = 'biolink-tool-night-veil';
        document.body.appendChild(veil);
    }

    if(has('heaven')) {
        const box = document.createElement('div');
        box.className = 'biolink-tool-heaven';
        const text = document.createElement('div');
        text.className = 'biolink-tool-heaven-text';
        const phrases = ['this is heaven', 'part of it', 'in it'];
        let index = 0;
        text.textContent = phrases[0];
        box.appendChild(text);
        document.body.appendChild(box);
        window.setInterval(() => {
            index = (index + 1) % phrases.length;
            text.textContent = phrases[index];
        }, 4000);
    }

    if(has('blocked')) {
        const banner = document.createElement('div');
        banner.className = 'biolink-tool-banner biolink-tool-banner-blocked';
        banner.innerHTML = 'he banned me<br>i blocked him';
        document.body.appendChild(banner);
        document.body.style.paddingTop = '44px';
    }

    if(has('secure')) {
        const banner = document.createElement('div');
        banner.className = 'biolink-tool-banner biolink-tool-banner-secure';
        banner.textContent = 'Secure';
        document.body.appendChild(banner);
        document.body.style.paddingTop = '28px';
    }

    if(has('under_control')) {
        const banner = document.createElement('div');
        banner.className = 'biolink-tool-banner biolink-tool-banner-control';
        banner.textContent = 'Under control';
        document.body.appendChild(banner);
        document.body.style.paddingBottom = '28px';
    }

    if(has('war')) {
        const stamp = document.createElement('div');
        stamp.className = 'biolink-tool-stamp';
        stamp.textContent = 'War';
        document.body.appendChild(stamp);
    }

    if(has('up_to_grow')) {
        const panel = document.createElement('div');
        panel.className = 'biolink-tool-grow';
        panel.innerHTML = `
            <div class="biolink-tool-grow-title">up to grow</div>
            <div class="biolink-tool-grow-row">
                <div class="biolink-tool-grow-item">
                    <svg viewBox="0 0 48 48" aria-hidden="true">
                        <path d="M24 42 V18" stroke="#333" stroke-width="2" fill="none"/>
                        <path d="M24 20 C14 20 12 10 12 6 C20 8 24 14 24 20 Z" fill="#5a5a5a"/>
                        <path d="M24 22 C34 20 38 12 38 8 C28 10 24 16 24 22 Z" fill="#7a7a7a"/>
                    </svg>
                    plant
                </div>
                <div class="biolink-tool-grow-item">
                    <div class="biolink-tool-grow-bars"><span></span><span></span><span></span><span></span></div>
                    chart
                </div>
                <div class="biolink-tool-grow-item">
                    <svg viewBox="0 0 48 48" aria-hidden="true">
                        <polyline points="6,38 16,28 24,30 34,14 42,10" fill="none" stroke="#333" stroke-width="2"/>
                        <polyline points="30,10 42,10 42,22" fill="none" stroke="#333" stroke-width="2"/>
                    </svg>
                    rise
                </div>
            </div>
        `;
        document.body.appendChild(panel);
        document.body.style.paddingBottom = '120px';
    }

    if(has('tap')) {
        const board = document.createElement('div');
        board.className = 'biolink-tool-tap';
        document.body.appendChild(board);

        const spawn = () => {
            if(board.childElementCount > 8) return;
            const dot = document.createElement('button');
            dot.type = 'button';
            dot.className = 'biolink-tool-tap-dot';
            dot.style.left = (10 + Math.random() * 80) + '%';
            dot.style.top = (12 + Math.random() * 76) + '%';
            dot.addEventListener('click', event => {
                event.preventDefault();
                dot.remove();
            });
            board.appendChild(dot);
        };

        spawn();
        window.setInterval(spawn, 1600);
    }

    if(has('screensaver')) {
        const screen = document.createElement('div');
        screen.className = 'biolink-tool-screensaver';
        const canvas = document.createElement('canvas');
        screen.appendChild(canvas);
        document.body.appendChild(screen);
        const ctx = canvas.getContext('2d');

        const resize = () => {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        };
        resize();
        window.addEventListener('resize', resize);

        let mode = 0;
        let tick = 0;
        const box = {x: 40, y: 40, dx: 1.4, dy: 1.1, w: 90, h: 54};
        const stars = Array.from({length: 80}, () => ({
            x: Math.random(),
            y: Math.random(),
            z: 0.4 + Math.random() * 0.6
        }));

        const draw_clock = () => {
            const cx = canvas.width / 2;
            const cy = canvas.height / 2;
            const r = Math.min(canvas.width, canvas.height) * 0.22;
            ctx.strokeStyle = '#d0d0d0';
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.arc(cx, cy, r, 0, Math.PI * 2);
            ctx.stroke();
            const now = new Date();
            const draw_hand = (angle, length, width) => {
                ctx.lineWidth = width;
                ctx.beginPath();
                ctx.moveTo(cx, cy);
                ctx.lineTo(cx + Math.sin(angle) * length, cy - Math.cos(angle) * length);
                ctx.stroke();
            };
            const hours = ((now.getHours() % 12) + now.getMinutes() / 60) * (Math.PI * 2 / 12);
            const minutes = (now.getMinutes() + now.getSeconds() / 60) * (Math.PI * 2 / 60);
            draw_hand(hours, r * 0.5, 3);
            draw_hand(minutes, r * 0.72, 2);
        };

        const draw = () => {
            ctx.fillStyle = '#000';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = '#c8c8c8';
            ctx.strokeStyle = '#c8c8c8';

            if(mode === 0) {
                box.x += box.dx;
                box.y += box.dy;
                if(box.x <= 0 || box.x + box.w >= canvas.width) box.dx *= -1;
                if(box.y <= 0 || box.y + box.h >= canvas.height) box.dy *= -1;
                ctx.strokeRect(box.x, box.y, box.w, box.h);
            } else if(mode === 1) {
                stars.forEach(star => {
                    ctx.globalAlpha = star.z;
                    ctx.fillRect(star.x * canvas.width, star.y * canvas.height, 2, 2);
                });
                ctx.globalAlpha = 1;
            } else {
                draw_clock();
            }

            tick += 1;
            if(tick > 700) {
                tick = 0;
                mode = (mode + 1) % 3;
            }
            window.requestAnimationFrame(draw);
        };
        draw();
    }
})();
