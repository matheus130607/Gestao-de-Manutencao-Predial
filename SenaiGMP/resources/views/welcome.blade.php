{{-- resources/views/filament/pages/welcome.blade.php --}}
{{--
    SENAI · Página Institucional — Filament Custom Page
    Versão adaptada para Laravel 11 + Filament 3.3
--}}

<x-filament-panels::page>
    @push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800;900&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
    
    <style>
        /* ── Tokens ── */
        :root {
            --senai-red:     #E30613;
            --senai-red-dk:  #B5000F;
            --senai-dark:    #1A1A1A;
            --senai-gray:    #F5F5F5;
            --senai-border:  #E0E0E0;
            --font-display:  'Barlow Condensed', sans-serif;
            --font-body:     'Barlow', sans-serif;
        }

        /* ── Reset de layout Filament para esta página ── */
        .fi-main {
            padding: 0 !important;
            background: #fff;
        }
        
        .fi-page-header {
            display: none !important;
        }
        
        /* Esconde sidebar e topbar apenas nesta página */
        .fi-sidebar,
        .fi-topbar {
            display: none !important;
        }
        
        /* Ajuste para o layout simple */
        .fi-layout-simple {
            padding: 0 !important;
        }
        
        /* Remove o wrapper extra do filament */
        .fi-page {
            padding: 0 !important;
        }

        /* ── Tipografia global ── */
        .senai-page,
        .senai-page * {
            font-family: var(--font-body);
        }
        
        .senai-page h1,
        .senai-page h2,
        .senai-page h3,
        .senai-page .display {
            font-family: var(--font-display);
        }

        /* ── Wrappers ── */
        .senai-page {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background: #fff;
            color: var(--senai-dark);
            margin: 0;
            padding: 0;
        }

        /* ════════════════ HEADER ════════════════ */
        .sn-header {
            background: var(--senai-red);
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 12px rgba(0,0,0,.25);
        }

        .sn-header__logo {
            display: flex;
            align-items: center;
            gap: .6rem;
            text-decoration: none;
            flex-shrink: 0;
        }

        .sn-header__logo-box {
            background: #fff;
            color: var(--senai-red);
            font-family: var(--font-display);
            font-weight: 900;
            font-size: 1.5rem;
            letter-spacing: .02em;
            padding: .2rem .7rem;
            border-radius: 2px;
        }

        .sn-header__nav {
            display: flex;
            align-items: center;
            gap: 2.5rem;
            margin: 0 auto;
        }

        .sn-header__nav a {
            color: #fff;
            font-family: var(--font-display);
            font-weight: 600;
            font-size: 1.05rem;
            letter-spacing: .08em;
            text-transform: uppercase;
            text-decoration: none;
            padding: .3rem 0;
            border-bottom: 2px solid transparent;
            transition: border-color .2s;
        }
        
        .sn-header__nav a:hover { 
            border-bottom-color: rgba(255,255,255,.7); 
        }

        .sn-header__cta {
            background: #fff;
            color: var(--senai-red);
            font-family: var(--font-display);
            font-weight: 800;
            font-size: 1rem;
            letter-spacing: .06em;
            text-transform: uppercase;
            text-decoration: none;
            padding: .55rem 1.5rem;
            border-radius: 2px;
            transition: all .2s;
            flex-shrink: 0;
        }
        
        .sn-header__cta:hover {
            background: var(--senai-dark);
            color: #fff;
        }

        /* ════════════════ HERO ════════════════ */
        .sn-hero {
            background: var(--senai-red);
            color: #fff;
            padding: 4rem 2rem 3.5rem;
            position: relative;
            overflow: hidden;
        }

        .sn-hero::after {
            content: '';
            position: absolute;
            right: -80px;
            top: 0;
            bottom: 0;
            width: 520px;
            background: rgba(0,0,0,.08);
            transform: skewX(-8deg);
            pointer-events: none;
        }

        .sn-hero__inner {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .sn-hero__badge {
            display: inline-block;
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.3);
            font-size: .8rem;
            letter-spacing: .12em;
            text-transform: uppercase;
            padding: .35rem .9rem;
            border-radius: 2px;
            margin-bottom: 1.2rem;
            font-weight: 600;
        }

        .sn-hero h1 {
            font-size: clamp(2.4rem, 5vw, 4rem);
            font-weight: 900;
            line-height: 1.05;
            letter-spacing: -.01em;
            margin-bottom: 1.2rem;
        }

        .sn-hero__sub {
            font-size: 1.05rem;
            line-height: 1.65;
            opacity: .88;
            max-width: 460px;
            margin-bottom: 2rem;
        }

        .sn-hero__actions { 
            display: flex; 
            gap: 1rem; 
            flex-wrap: wrap; 
        }

        .sn-btn {
            display: inline-block;
            font-family: var(--font-display);
            font-weight: 700;
            font-size: .95rem;
            letter-spacing: .08em;
            text-transform: uppercase;
            text-decoration: none;
            padding: .7rem 1.8rem;
            border-radius: 2px;
            transition: all .2s;
            border: 2px solid transparent;
            cursor: pointer;
        }
        
        .sn-btn--white { 
            background: #fff; 
            color: var(--senai-red); 
        }
        
        .sn-btn--white:hover { 
            background: var(--senai-dark); 
            color: #fff; 
            border-color: var(--senai-dark); 
        }
        
        .sn-btn--outline { 
            background: transparent; 
            color: #fff; 
            border-color: rgba(255,255,255,.6); 
        }
        
        .sn-btn--outline:hover { 
            background: rgba(255,255,255,.12); 
            border-color: #fff; 
        }
        
        .sn-btn--red { 
            background: var(--senai-red); 
            color: #fff; 
            border-color: var(--senai-red); 
        }
        
        .sn-btn--red:hover { 
            background: var(--senai-red-dk); 
            border-color: var(--senai-red-dk); 
        }

        /* stats strip */
        .sn-hero__stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: .8rem;
            margin-top: 2.5rem;
        }
        
        .sn-hero__stat {
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 4px;
            padding: .9rem 1rem;
        }
        
        .sn-hero__stat-num {
            font-family: var(--font-display);
            font-size: 1.8rem;
            font-weight: 900;
            line-height: 1;
        }
        
        .sn-hero__stat-label { 
            font-size: .78rem; 
            opacity: .78; 
            margin-top: .25rem; 
        }

        /* right panel */
        .sn-hero__panel {
            background: rgba(255,255,255,.1);
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 6px;
            padding: 1.8rem;
            backdrop-filter: blur(4px);
        }
        
        .sn-hero__panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.4rem;
        }
        
        .sn-hero__panel-title { 
            font-family: var(--font-display); 
            font-weight: 700; 
            font-size: 1.15rem; 
        }
        
        .sn-hero__panel-title small { 
            display: block; 
            font-size: .78rem; 
            opacity: .7; 
            font-family: var(--font-body); 
            font-weight: 400; 
        }
        
        .sn-pulse { 
            width: 10px; 
            height: 10px; 
            border-radius: 50%; 
            background: #4ade80; 
            animation: pulse 1.6s infinite; 
        }
        
        @keyframes pulse { 
            0%,100%{opacity:1;transform:scale(1)} 
            50%{opacity:.5;transform:scale(.85)} 
        }

        .sn-panel-item {
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.15);
            border-radius: 4px;
            padding: 1rem 1.2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: .8rem;
        }
        
        .sn-panel-item:last-child { margin-bottom: 0; }
        
        .sn-panel-item__label { 
            font-size: .78rem; 
            opacity: .7; 
        }
        
        .sn-panel-item__title { 
            font-weight: 600; 
            font-size: .95rem; 
            margin-top: .15rem; 
        }
        
        .sn-panel-item__icon {
            background: rgba(255,255,255,.12);
            border-radius: 4px;
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        /* ════════════════ CONTENT AREA ════════════════ */
        .sn-main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 3.5rem 2rem;
            flex: 1;
        }

        /* section header */
        .sn-section-head { 
            margin-bottom: 2.5rem; 
        }
        
        .sn-section-head h2 {
            font-size: clamp(1.8rem, 3.5vw, 2.8rem);
            font-weight: 900;
            letter-spacing: -.01em;
            color: var(--senai-dark);
            line-height: 1.1;
        }
        
        .sn-section-head p {
            margin-top: .6rem;
            color: #555;
            font-size: 1rem;
            line-height: 1.6;
            max-width: 600px;
        }
        
        .sn-section-head__rule {
            width: 48px;
            height: 4px;
            background: var(--senai-red);
            margin-bottom: .9rem;
            border-radius: 2px;
        }

        /* ── Valores grid ── */
        .sn-valores { 
            display: grid; 
            grid-template-columns: repeat(3, 1fr); 
            gap: 1.2rem; 
        }

        .sn-valor-card {
            background: #fff;
            border: 1px solid var(--senai-border);
            border-top: 4px solid var(--senai-red);
            border-radius: 4px;
            padding: 1.8rem 1.6rem;
            transition: box-shadow .25s, transform .25s;
        }
        
        .sn-valor-card:hover {
            box-shadow: 0 8px 32px rgba(227,6,19,.1);
            transform: translateY(-4px);
        }
        
        .sn-valor-card__icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            display: block;
        }
        
        .sn-valor-card h3 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--senai-dark);
            margin-bottom: .5rem;
        }
        
        .sn-valor-card p { 
            font-size: .9rem; 
            color: #666; 
            line-height: 1.55; 
        }

        /* ── Serviços grid ── */
        .sn-servicos { 
            display: grid; 
            grid-template-columns: repeat(4, 1fr); 
            gap: 1rem; 
        }

        .sn-servico-card {
            background: var(--senai-gray);
            border: 1px solid var(--senai-border);
            border-radius: 4px;
            padding: 1.4rem;
            transition: border-color .2s, box-shadow .2s;
        }
        
        .sn-servico-card:hover {
            border-color: var(--senai-red);
            box-shadow: 0 4px 20px rgba(227,6,19,.12);
        }
        
        .sn-servico-card__icon {
            font-size: 1.5rem;
            margin-bottom: .8rem;
            display: block;
        }
        
        .sn-servico-card h3 { 
            font-size: 1rem; 
            font-weight: 700; 
            color: var(--senai-dark); 
            margin-bottom: .35rem; 
        }
        
        .sn-servico-card p { 
            font-size: .82rem; 
            color: #777; 
            line-height: 1.5; 
        }

        /* ── Segurança strip ── */
        .sn-seguranca {
            background: var(--senai-dark);
            border-radius: 4px;
            padding: 3rem 2.5rem;
            color: #fff;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
        }
        
        .sn-seguranca h2 {
            font-size: clamp(1.6rem, 3vw, 2.4rem);
            font-weight: 900;
            line-height: 1.15;
            margin: 1rem 0 1.2rem;
        }
        
        .sn-seguranca__badge {
            display: inline-block;
            background: rgba(227,6,19,.25);
            border: 1px solid rgba(227,6,19,.4);
            color: #ff8080;
            font-size: .78rem;
            font-weight: 600;
            letter-spacing: .1em;
            text-transform: uppercase;
            padding: .3rem .8rem;
            border-radius: 2px;
        }
        
        .sn-seguranca p { 
            color: #aaa; 
            font-size: .95rem; 
            line-height: 1.6; 
            margin-bottom: 1.5rem; 
        }
        
        .sn-check { 
            display: flex; 
            align-items: center; 
            gap: .9rem; 
            margin-bottom: .9rem; 
        }
        
        .sn-check__icon { 
            font-size: 1.2rem; 
            flex-shrink: 0; 
        }
        
        .sn-check span { 
            color: #ccc; 
            font-size: .93rem; 
        }

        .sn-stats-grid { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: .8rem; 
        }
        
        .sn-stat-box {
            background: #111;
            border-radius: 4px;
            padding: 1.4rem;
        }
        
        .sn-stat-box__num {
            font-family: var(--font-display);
            font-size: 2.2rem;
            font-weight: 900;
            color: var(--senai-red);
            line-height: 1;
        }
        
        .sn-stat-box__label { 
            color: #888; 
            font-size: .82rem; 
            margin-top: .3rem; 
        }

        /* ── CTA ── */
        .sn-cta {
            background: var(--senai-red);
            border-radius: 4px;
            padding: 3.5rem 2rem;
            text-align: center;
            color: #fff;
        }
        
        .sn-cta h2 { 
            font-size: clamp(1.8rem, 3.5vw, 2.8rem); 
            font-weight: 900; 
            line-height: 1.15; 
            margin-bottom: 1rem; 
        }
        
        .sn-cta p { 
            font-size: 1rem; 
            opacity: .88; 
            max-width: 520px; 
            margin: 0 auto 2rem; 
            line-height: 1.65; 
        }

        /* ════════════════ FOOTER ════════════════ */
        .sn-footer {
            background: var(--senai-red);
            color: #fff;
            padding: .9rem 2rem;
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 -2px 16px rgba(0,0,0,.2);
        }

        .sn-footer__address { 
            font-size: .82rem; 
            line-height: 1.5; 
        }
        
        .sn-footer__address strong { 
            display: block; 
            font-family: var(--font-display); 
            font-weight: 700; 
            font-size: 1rem; 
            letter-spacing: .03em; 
        }

        .sn-footer__socials { 
            display: flex; 
            gap: .6rem; 
            align-items: center; 
        }
        
        .sn-footer__social-link {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.25);
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: .9rem;
            transition: background .2s, transform .2s;
            color: #fff;
        }
        
        .sn-footer__social-link:hover { 
            background: rgba(255,255,255,.3); 
            transform: translateY(-2px); 
        }

        .sn-footer__contact { 
            text-align: right; 
            font-size: .82rem; 
            line-height: 1.5; 
        }
        
        .sn-footer__contact strong { 
            display: block; 
            font-family: var(--font-display); 
            font-weight: 700; 
            font-size: 1rem; 
            letter-spacing: .03em; 
            margin-bottom: .15rem; 
        }

        /* ════════════════ RESPONSIVE ════════════════ */
        @media (max-width: 900px) {
            .sn-hero__inner { 
                grid-template-columns: 1fr; 
            }
            
            .sn-hero__panel { 
                display: none; 
            }
            
            .sn-hero__stats { 
                grid-template-columns: repeat(2, 1fr); 
            }
            
            .sn-valores { 
                grid-template-columns: 1fr 1fr; 
            }
            
            .sn-servicos { 
                grid-template-columns: 1fr 1fr; 
            }
            
            .sn-seguranca { 
                grid-template-columns: 1fr; 
            }
            
            .sn-footer { 
                grid-template-columns: 1fr; 
                text-align: center; 
            }
            
            .sn-footer__contact { 
                text-align: center; 
            }
            
            .sn-footer__socials { 
                justify-content: center; 
            }
        }

        @media (max-width: 600px) {
            .sn-header__nav { 
                display: none; 
            }
            
            .sn-valores, 
            .sn-servicos { 
                grid-template-columns: 1fr; 
            }
        }
        
        /* Smooth scroll */
        html {
            scroll-behavior: smooth;
        }
    </style>
    @endpush

    <div class="senai-page">

        {{-- ══════════════ HEADER ══════════════ --}}
        <header class="sn-header">
            <a href="{{ url('/') }}" class="sn-header__logo">
                <span class="sn-header__logo-box">SENAI</span>
            </a>

            <nav class="sn-header__nav">
                <a href="#servicos">Serviços</a>
                <a href="#valores">Valores</a>
                <a href="#seguranca">Segurança</a>
                <a href="#contato">Contato</a>
            </nav>

            <a href="{{ route('filament.admin.auth.login') }}" class="sn-header__cta">
                Entrar
            </a>
        </header>

        {{-- ══════════════ HERO ══════════════ --}}
        <section class="sn-hero">
            <div class="sn-hero__inner">

                {{-- Left --}}
                <div>
                    <span class="sn-hero__badge">
                        Segurança · Facilities · Manutenção · Controle Operacional
                    </span>

                    <h1>
                        Excelência em<br>
                        serviços operacionais<br>
                        e segurança institucional
                    </h1>

                    <p class="sn-hero__sub">
                        Atuamos com profissionalismo, integridade e eficiência para proteger
                        patrimônios, otimizar operações e garantir suporte contínuo para
                        empresas e condomínios.
                    </p>

                    <div class="sn-hero__actions">
                        <a href="#servicos" class="sn-btn sn-btn--white">Conhecer Serviços</a>
                        <a href="#contato" class="sn-btn sn-btn--outline">Falar com Especialista</a>
                    </div>

                    <div class="sn-hero__stats">
                        <div class="sn-hero__stat">
                            <div class="sn-hero__stat-num">24h</div>
                            <div class="sn-hero__stat-label">Suporte operacional</div>
                        </div>
                        <div class="sn-hero__stat">
                            <div class="sn-hero__stat-num">100%</div>
                            <div class="sn-hero__stat-label">Equipes treinadas</div>
                        </div>
                        <div class="sn-hero__stat">
                            <div class="sn-hero__stat-num">+500</div>
                            <div class="sn-hero__stat-label">Atendimentos</div>
                        </div>
                        <div class="sn-hero__stat">
                            <div class="sn-hero__stat-num">24/7</div>
                            <div class="sn-hero__stat-label">Monitoramento</div>
                        </div>
                    </div>
                </div>

                {{-- Right Panel --}}
                <div class="sn-hero__panel">
                    <div class="sn-hero__panel-header">
                        <div>
                            <div class="sn-hero__panel-title">
                                Central Operacional
                                <small>Gestão inteligente de operações</small>
                            </div>
                        </div>
                        <div class="sn-pulse"></div>
                    </div>

                    <div class="sn-panel-item">
                        <div>
                            <div class="sn-panel-item__label">Controle de acesso</div>
                            <div class="sn-panel-item__title">Operacional</div>
                        </div>
                        <div class="sn-panel-item__icon">🔒</div>
                    </div>

                    <div class="sn-panel-item">
                        <div>
                            <div class="sn-panel-item__label">Monitoramento contínuo</div>
                            <div class="sn-panel-item__title">Ativo 24h</div>
                        </div>
                        <div class="sn-panel-item__icon">🛡️</div>
                    </div>

                    <div class="sn-panel-item">
                        <div>
                            <div class="sn-panel-item__label">Manutenção predial</div>
                            <div class="sn-panel-item__title">Equipe disponível</div>
                        </div>
                        <div class="sn-panel-item__icon">⚙️</div>
                    </div>
                </div>

            </div>
        </section>

        {{-- ══════════════ CONTEÚDO PRINCIPAL ══════════════ --}}
        <main class="sn-main">

            {{-- ── Nossos Valores ── --}}
            <section id="valores" style="margin-bottom: 4rem;">
                <div class="sn-section-head">
                    <div class="sn-section-head__rule"></div>
                    <h2>Nossos Valores</h2>
                    <p>
                        Atuamos com responsabilidade, ética e eficiência operacional para garantir
                        segurança, estabilidade e confiança aos nossos clientes.
                    </p>
                </div>

                @php
                    $valores = [
                        ['icon' => '🛡️', 'title' => 'Integridade e Ética',       'desc' => 'Conduta transparente, ética profissional e proteção de informações sigilosas em todos os processos.'],
                        ['icon' => '👔', 'title' => 'Profissionalismo',           'desc' => 'Equipes treinadas e preparadas para agir com eficiência, organização e controle operacional.'],
                        ['icon' => '⚖️', 'title' => 'Responsabilidade Legal',     'desc' => 'Compromisso com obrigações trabalhistas, previdenciárias e legais dentro dos padrões vigentes.'],
                        ['icon' => '🤝', 'title' => 'Foco no Cliente',            'desc' => 'Soluções personalizadas e atendimento dedicado conforme as necessidades reais de cada operação.'],
                        ['icon' => '📈', 'title' => 'Confiabilidade',             'desc' => 'Pronta resposta operacional e suporte contínuo para garantir a tranquilidade dos contratantes.'],
                        ['icon' => '🏛️', 'title' => 'Zelo pelo Patrimônio',       'desc' => 'Cuidado e respeito com os ativos, espaços e equipamentos dos nossos clientes parceiros.'],
                    ];
                @endphp

                <div class="sn-valores">
                    @foreach($valores as $valor)
                        <div class="sn-valor-card">
                            <span class="sn-valor-card__icon">{{ $valor['icon'] }}</span>
                            <h3>{{ $valor['title'] }}</h3>
                            <p>{{ $valor['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- ── Serviços Especializados ── --}}
            <section id="servicos" style="margin-bottom: 4rem;">
                <div class="sn-section-head">
                    <div class="sn-section-head__rule"></div>
                    <h2>Serviços Especializados</h2>
                    <p>
                        Estrutura operacional completa para empresas, condomínios e ambientes corporativos.
                    </p>
                </div>

                @php
                    $servicos = [
                        ['icon' => '🚪', 'title' => 'Portaria',              'desc' => 'Controle de entrada e saída com registro e triagem de visitantes.'],
                        ['icon' => '🔐', 'title' => 'Controle de Acesso',    'desc' => 'Sistemas integrados para gestão de credenciais e permissões.'],
                        ['icon' => '🛡️', 'title' => 'Segurança Patrimonial', 'desc' => 'Proteção de bens, instalações e informações estratégicas.'],
                        ['icon' => '🏢', 'title' => 'Facilities',            'desc' => 'Gestão de infraestrutura, limpeza, recepção e apoio geral.'],
                        ['icon' => '📷', 'title' => 'Monitoramento',         'desc' => 'Vigilância eletrônica com câmeras e central de alarmes.'],
                        ['icon' => '🧹', 'title' => 'Zeladoria',             'desc' => 'Conservação e organização de áreas comuns e espaços internos.'],
                        ['icon' => '🔧', 'title' => 'Manutenção Predial',    'desc' => 'Serviços preventivos e corretivos para instalações e equipamentos.'],
                        ['icon' => '🤲', 'title' => 'Apoio Operacional',     'desc' => 'Suporte multidisciplinar para demandas operacionais emergenciais.'],
                    ];
                @endphp

                <div class="sn-servicos">
                    @foreach($servicos as $servico)
                        <div class="sn-servico-card">
                            <span class="sn-servico-card__icon">{{ $servico['icon'] }}</span>
                            <h3>{{ $servico['title'] }}</h3>
                            <p>{{ $servico['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- ── Segurança e Confiabilidade ── --}}
            <section id="seguranca" style="margin-bottom: 4rem;">
                <div class="sn-seguranca">

                    <div>
                        <span class="sn-seguranca__badge">Segurança e Confiabilidade</span>
                        <h2>Proteção operacional com máxima eficiência</h2>
                        <p>
                            Garantimos suporte contínuo, equipes capacitadas e atuação preventiva
                            para reduzir riscos e preservar o patrimônio dos clientes.
                        </p>

                        <div class="sn-check">
                            <span class="sn-check__icon">🔒</span>
                            <span>Proteção de dados e controle de acesso unificado</span>
                        </div>
                        <div class="sn-check">
                            <span class="sn-check__icon">🛡️</span>
                            <span>Equipes treinadas para resposta rápida e eficiente</span>
                        </div>
                        <div class="sn-check">
                            <span class="sn-check__icon">⚡</span>
                            <span>Operação contínua com monitoramento ativo 24h</span>
                        </div>
                    </div>

                    <div class="sn-stats-grid">
                        <div class="sn-stat-box">
                            <div class="sn-stat-box__num">+98%</div>
                            <div class="sn-stat-box__label">Satisfação operacional</div>
                        </div>
                        <div class="sn-stat-box">
                            <div class="sn-stat-box__num">24/7</div>
                            <div class="sn-stat-box__label">Monitoramento ativo</div>
                        </div>
                        <div class="sn-stat-box">
                            <div class="sn-stat-box__num">+1000</div>
                            <div class="sn-stat-box__label">Ocorrências solucionadas</div>
                        </div>
                        <div class="sn-stat-box">
                            <div class="sn-stat-box__num">+10</div>
                            <div class="sn-stat-box__label">Anos de experiência</div>
                        </div>
                    </div>

                </div>
            </section>

            {{-- ── CTA ── --}}
            <section id="contato">
                <div class="sn-cta">
                    <h2>Proteja seu patrimônio com eficiência e confiança</h2>
                    <p>
                        Conte com uma equipe preparada para oferecer segurança, estabilidade
                        operacional e atendimento profissional para sua empresa ou condomínio.
                    </p>
                    <a href="mailto:contato@senai.br" class="sn-btn sn-btn--white">
                        Solicitar Orçamento
                    </a>
                </div>
            </section>

        </main>

        {{-- ══════════════ FOOTER ══════════════ --}}
        <footer class="sn-footer">

            {{-- Endereço --}}
            <div class="sn-footer__address">
                <strong>Edifício SENAI</strong>
                Av. Paulista, 1313, São Paulo/SP<br>
                CEP 01311-923
            </div>

            {{-- Redes sociais --}}
            <div class="sn-footer__socials">
                <a href="#" class="sn-footer__social-link" title="Facebook">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                </a>
                <a href="#" class="sn-footer__social-link" title="Twitter / X">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                </a>
                <a href="#" class="sn-footer__social-link" title="YouTube">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 0 0-1.95 1.96A29 29 0 0 0 1 12a29 29 0 0 0 .46 5.58A2.78 2.78 0 0 0 3.41 19.5C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 0 0 1.95-1.95A29 29 0 0 0 23 12a29 29 0 0 0-.46-5.58zM9.75 15.02V8.98L15.5 12z"/></svg>
                </a>
                <a href="#" class="sn-footer__social-link" title="LinkedIn">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg>
                </a>
                <a href="#" class="sn-footer__social-link" title="Instagram">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                </a>
                <a href="#" class="sn-footer__social-link" title="WhatsApp">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
                </a>
            </div>

            {{-- Central de Relacionamento --}}
            <div class="sn-footer__contact">
                <strong>Central de Relacionamento</strong>
                (11) 3322-0050 (Telefone/WhatsApp)<br>
                0800-055-1000 (Interior de SP)
            </div>

        </footer>

    </div>
</x-filament-panels::page>