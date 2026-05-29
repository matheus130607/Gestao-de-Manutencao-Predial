@php $livewire ??= null; @endphp

<x-filament-panels::layout.base :livewire="$livewire">
    @vite(['resources/css/app.css'])

    <style>
        .gmp-login-shell,
        .gmp-login-canvas-pane {
            background: #05050a;
        }

        .dark .gmp-login-shell,
        .dark .gmp-login-canvas-pane {
            background: #f8fafc;
        }

        .gmp-login-brand {
            color: #d1d5db;
        }

        .gmp-login-icon {
            border-color: rgba(220, 38, 38, .5);
            background: rgba(220, 38, 38, .1);
        }

        .gmp-login-hero-title {
            color: #ffffff;
        }

        .gmp-login-hero-copy {
            color: #94a3b8;
        }

        .gmp-login-form-panel {
            background: #ffffff;
        }

        .gmp-login-logo {
            height: auto;
            max-height: 6rem;
            max-width: 18rem;
            object-fit: contain;
        }

        .gmp-login-logo-frame {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .gmp-login-logo--dark {
            display: none;
        }

        .dark .gmp-login-brand,
        .dark .gmp-login-hero-title {
            color: #0f172a;
        }

        .dark .gmp-login-hero-copy {
            color: #64748b;
        }

        .dark .gmp-login-icon {
            border-color: rgba(220, 38, 38, .25);
            background: rgba(220, 38, 38, .08);
        }

        .dark .gmp-login-form-panel {
            background: #020617;
        }

        .dark .gmp-login-logo--light {
            display: none;
        }

        .dark .gmp-login-logo--dark {
            display: block;
            max-height: 5.5rem;
            max-width: 19rem;
        }

        .gmp-theme-toggle {
            position: fixed;
            top: 1.25rem;
            right: 1.25rem;
            z-index: 60;
            display: inline-flex;
            width: 2.5rem;
            height: 2.5rem;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(15, 23, 42, .1);
            border-radius: .75rem;
            background: rgba(255, 255, 255, .92);
            color: #0f172a;
            box-shadow: 0 12px 32px rgba(15, 23, 42, .12);
            transition: background .2s ease, border-color .2s ease, color .2s ease, transform .2s ease;
        }

        .gmp-theme-toggle:hover {
            transform: translateY(-1px);
        }

        .gmp-theme-toggle__sun {
            display: none;
        }

        .dark .gmp-theme-toggle {
            border-color: rgba(255, 255, 255, .12);
            background: rgba(15, 23, 42, .92);
            color: #ffffff;
            box-shadow: 0 12px 32px rgba(0, 0, 0, .28);
        }

        .dark .gmp-theme-toggle__moon {
            display: none;
        }

        .dark .gmp-theme-toggle__sun {
            display: block;
        }
    </style>

    <div class="gmp-login-shell flex h-screen w-full overflow-hidden">
        <button
            type="button"
            class="gmp-theme-toggle"
            title="Alternar tema"
            aria-label="Alternar tema"
            onclick="
                const root = document.documentElement;
                const shouldUseDarkMode = ! root.classList.contains('dark');

                localStorage.setItem('theme', shouldUseDarkMode ? 'dark' : 'light');
                window.theme = shouldUseDarkMode ? 'dark' : 'light';
                root.classList.toggle('dark', shouldUseDarkMode);
                window.dispatchEvent(new CustomEvent('theme-changed', { detail: window.theme }));
            "
        >
            <svg class="gmp-theme-toggle__moon h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A8.5 8.5 0 1 1 11.21 3a6.75 6.75 0 0 0 9.79 9.79Z" />
            </svg>
            <svg class="gmp-theme-toggle__sun h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5V3m0 18v-1.5m7.5-7.5H21M3 12h1.5m12.8-5.3 1.06-1.06M5.64 18.36l1.06-1.06m0-10.6L5.64 5.64m12.72 12.72-1.06-1.06M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" />
            </svg>
        </button>

        {{-- LADO ESQUERDO: canvas animado --}}
        <div
            id="login-canvas-pane"
            class="gmp-login-canvas-pane relative hidden lg:flex lg:w-9/10 items-stretch overflow-hidden"
        >
            <canvas id="tech-canvas" class="absolute inset-0 block" style="width:100%;height:100%;pointer-events:none;"></canvas>

            <div class="relative z-10 flex w-full flex-col items-center justify-between py-16 px-12 pointer-events-none select-none">
                <div class="flex items-center gap-3">
                    <div class="gmp-login-icon flex h-10 w-10 items-center justify-center rounded-xl border">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                        </svg>
                    </div>

                    <!--|||||||||||||||||||| Logo com nome do sistema ||||||||||||||||||||-->
                    <span class="gmp-login-brand text-sm font-semibold tracking-wide">GMP Sistema</span>
                </div>

                <div class="text-center">
                    <div class="mx-auto mb-5 h-0.5 w-10 bg-red-600"></div>
                    <h1 class="gmp-login-hero-title mb-3 text-3xl font-bold leading-tight tracking-tight xl:text-4xl">
                        Gestão de Manutenção<br />Predial
                    </h1>
                    <p class="gmp-login-hero-copy text-sm leading-relaxed xl:text-base">
                        Sistema integrado para controle de patrimônio,<br />
                        ordens de serviço e equipes de manutenção.
                    </p>
                </div>
            </div>
        </div>

        {{-- LADO DIREITO: painel branco com formulário --}}
        <div class="gmp-login-form-panel relative flex w-full items-center justify-center px-6 py-12 lg:w-1/10">
            <a href="{{ url('/') }}" class="absolute top-6 left-6 inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                Voltar à página inicial
            </a>
            <div class="w-full max-w-sm">
                {{ $slot }}
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    (function () {
        var WHITE_COUNT = 70, RED_COUNT = 28, CONNECT_DIST = 160, PARALLAX = 0.015;
        var canvas, ctx, raf;
        var whites = [], reds = [];
        var mouse = { x: 0, y: 0 };
        var t = 0;

        function isLightPane() {
            return document.documentElement.classList.contains('dark');
        }

        function WhiteParticle(w, h) {
            this.x = Math.random() * w;
            this.y = Math.random() * h;
            this.r = Math.random() * 2 + 0.8;
            this.vx = (Math.random() - 0.5) * 0.9;
            this.vy = (Math.random() - 0.5) * 0.8;
            this.a = Math.random() * 0.4 + 0.2;
        }
        WhiteParticle.prototype.update = function (w, h) {
            this.x += this.vx + mouse.x * PARALLAX * 0.8;
            this.y += this.vy + mouse.y * PARALLAX * 0.8;
            if (this.x < 0) this.x = w;
            if (this.x > w) this.x = 0;
            if (this.y < 0) this.y = h;
            if (this.y > h) this.y = 0;
        };
        WhiteParticle.prototype.draw = function () {
            var lightPane = isLightPane();
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.r, 0, Math.PI * 2);
            ctx.fillStyle = lightPane
                ? 'rgba(71,85,105,' + (this.a * 0.65) + ')'
                : 'rgba(255,255,255,' + this.a + ')';
            ctx.fill();
        };

        function RedPoint(w, h) {
            this.x = Math.random() * w;
            this.y = Math.random() * h;
            this.vx = (Math.random() - 0.5) * 1.7;
            this.vy = (Math.random() - 0.5) * 1.8;
            this.baseR = Math.random() * 2.5 + 1.8;
            this.ps = 0.04 + Math.random() * 0.08;
            this.pp = Math.random() * Math.PI * 2;
        }
        RedPoint.prototype.update = function (w, h) {
            this.x += this.vx + mouse.x * PARALLAX * 0.5;
            this.y += this.vy + mouse.y * PARALLAX * 0.5;
            if (this.x < 0) this.x = w;
            if (this.x > w) this.x = 0;
            if (this.y < 0) this.y = h;
            if (this.y > h) this.y = 0;
        };
        RedPoint.prototype.draw = function () {
            var lightPane = isLightPane();
            var r = Math.max(1.2, this.baseR + Math.sin(t * this.ps + this.pp) * 0.6);
            ctx.shadowColor = lightPane ? 'rgba(220,38,38,0.22)' : 'rgba(255,50,50,0.7)';
            ctx.shadowBlur = lightPane ? 5 : 10;
            ctx.beginPath();
            ctx.arc(this.x, this.y, r, 0, Math.PI * 2);
            ctx.fillStyle = lightPane ? '#dc2626' : '#ff4d4d';
            ctx.fill();
            ctx.shadowBlur = 0;
        };

        function resize() {
            var pane = document.getElementById('login-canvas-pane');
            if (!pane || !pane.offsetWidth) return;
            canvas.width  = pane.offsetWidth;
            canvas.height = pane.offsetHeight;
            var w = canvas.width, h = canvas.height;
            whites = [];
            for (var i = 0; i < WHITE_COUNT; i++) whites.push(new WhiteParticle(w, h));
            reds = [];
            for (var j = 0; j < RED_COUNT; j++) reds.push(new RedPoint(w, h));
        }

        function frame() {
            var w = canvas.width, h = canvas.height;
            if (!w || !h) { raf = requestAnimationFrame(frame); return; }
            t += 0.05;
            var lightPane = isLightPane();

            var g = ctx.createLinearGradient(0, 0, w * 0.7, h);
            g.addColorStop(0, lightPane ? '#ffffff' : '#05050A');
            g.addColorStop(0.6, lightPane ? '#f8fafc' : '#0C0C14');
            g.addColorStop(1, lightPane ? '#eef2f7' : '#12121C');
            ctx.fillStyle = g;
            ctx.fillRect(0, 0, w, h);

            var rg = ctx.createRadialGradient(w / 2, h * 0.15, 0, w / 2, h * 0.15, w * 0.5);
            rg.addColorStop(0, lightPane ? 'rgba(220,38,38,0.08)' : 'rgba(80,80,100,0.12)');
            rg.addColorStop(1, 'transparent');
            ctx.fillStyle = rg;
            ctx.fillRect(0, 0, w, h);

            for (var i = 0; i < whites.length; i++) { whites[i].update(w, h); whites[i].draw(); }
            for (var j = 0; j < reds.length; j++) { reds[j].update(w, h); }

            for (var a = 0; a < reds.length; a++) {
                for (var b = a + 1; b < reds.length; b++) {
                    var dx = reds[a].x - reds[b].x, dy = reds[a].y - reds[b].y;
                    var d  = Math.sqrt(dx * dx + dy * dy);
                    if (d < CONNECT_DIST) {
                        var alpha = (1 - d / CONNECT_DIST) * 1.05;
                        ctx.beginPath();
                        ctx.moveTo(reds[a].x, reds[a].y);
                        ctx.lineTo(reds[b].x, reds[b].y);
                        ctx.strokeStyle = lightPane
                            ? 'rgba(220,38,38,' + (alpha * 0.42) + ')'
                            : 'rgba(220,60,60,' + alpha + ')';
                        ctx.lineWidth = lightPane ? 1.6 : 2.4;
                        ctx.stroke();
                    }
                }
            }

            for (var k = 0; k < reds.length; k++) reds[k].draw();
            raf = requestAnimationFrame(frame);
        }

        function init() {
            canvas = document.getElementById('tech-canvas');
            if (!canvas) return;
            ctx = canvas.getContext('2d');
            resize();
            window.addEventListener('resize', resize);
            window.addEventListener('mousemove', function (e) {
                mouse.x = (e.clientX / window.innerWidth)  * 2 - 1;
                mouse.y = (e.clientY / window.innerHeight) * 2 - 1;
            });
            if (raf) cancelAnimationFrame(raf);
            frame();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    })();
    </script>
    @endpush
</x-filament-panels::layout.base>
