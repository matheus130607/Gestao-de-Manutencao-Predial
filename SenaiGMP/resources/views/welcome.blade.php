<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>GMP — Gestão de Manutenção Predial | SENAI</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />
<script>
  tailwind.config = {
    theme: {
      extend: {
        fontFamily: {
          sans: ['Inter', 'system-ui', 'sans-serif'],
          mono: ['JetBrains Mono', 'monospace'],
        },
        colors: {
          senai: {
            red:      '#E30613',
            'red-dk': '#B30410',
            'red-50': '#FEE2E4',
            black:    '#0A0A0A',
            'gray-950': '#0F0F0F',
            'gray-900': '#141414',
            'gray-800': '#1F1F1F',
            'gray-700': '#2A2A2A',
            'gray-600': '#3A3A3A',
            'gray-400': '#9CA3AF',
            'gray-300': '#D1D5DB',
          },
        },
        animation: {
          'fade-in-up': 'fadeInUp 0.8s ease-out forwards',
          'pulse-slow': 'pulse 3s ease-in-out infinite',
        },
        keyframes: {
          fadeInUp: {
            '0%': { opacity: '0', transform: 'translateY(20px)' },
            '100%': { opacity: '1', transform: 'translateY(0)' },
          },
        },
      },
    },
  };
</script>
<style>
  html { scroll-behavior: smooth; }
  body { font-family: 'Inter', system-ui, sans-serif; background: #0A0A0A; color: #fff; }

  /* Grid pattern background for hero */
  .grid-pattern {
    background-image:
      linear-gradient(rgba(227, 6, 19, 0.06) 1px, transparent 1px),
      linear-gradient(90deg, rgba(227, 6, 19, 0.06) 1px, transparent 1px);
    background-size: 60px 60px;
  }

  /* Radial vignette on hero */
  .hero-vignette {
    background:
      radial-gradient(ellipse at top, rgba(227, 6, 19, 0.18) 0%, transparent 50%),
      radial-gradient(ellipse at bottom right, rgba(227, 6, 19, 0.08) 0%, transparent 60%);
  }

  /* Glow accent on key elements */
  .red-glow { box-shadow: 0 0 40px rgba(227, 6, 19, 0.25); }
  .red-glow-sm { box-shadow: 0 0 20px rgba(227, 6, 19, 0.18); }

  /* Card hover lift */
  .card-lift { transition: transform 0.3s ease, border-color 0.3s ease, background 0.3s ease; }
  .card-lift:hover { transform: translateY(-4px); border-color: rgba(227, 6, 19, 0.4); }

  /* Section reveal on scroll */
  .reveal { opacity: 0; transform: translateY(20px); transition: opacity 0.8s, transform 0.8s; }
  .reveal.visible { opacity: 1; transform: translateY(0); }

  /* Diagonal accent stripe */
  .diagonal-stripe {
    background: linear-gradient(135deg, transparent 49.5%, #E30613 49.5%, #E30613 50.5%, transparent 50.5%);
  }

  /* Custom scrollbar */
  ::-webkit-scrollbar { width: 10px; }
  ::-webkit-scrollbar-track { background: #0A0A0A; }
  ::-webkit-scrollbar-thumb { background: #2A2A2A; border-radius: 5px; }
  ::-webkit-scrollbar-thumb:hover { background: #E30613; }

  /* Nav backdrop blur */
  .nav-blur { backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }
</style>
</head>
<body class="antialiased">

<!-- ============== NAV ============== -->
<nav class="fixed top-0 left-0 right-0 z-50 nav-blur bg-senai-black/80 border-b border-senai-gray-800">
  <div class="max-w-7xl mx-auto px-6 lg:px-8 h-16 flex items-center justify-between">
    <a href="#top" class="flex items-center gap-3">
      <div class="w-9 h-9 bg-senai-red rounded-md flex items-center justify-center red-glow-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg>
      </div>
      <div class="leading-tight">
        <div class="font-bold text-base tracking-tight">GMP</div>
        <div class="text-[10px] uppercase tracking-widest text-senai-gray-400">SENAI · 2026</div>
      </div>
    </a>

    <div class="hidden md:flex items-center gap-8 text-sm text-senai-gray-300">
      <a href="#sobre" class="hover:text-white transition">Sobre</a>
      <a href="#stack" class="hover:text-white transition">Stack</a>
      <a href="#seguranca" class="hover:text-white transition">Segurança</a>
      <a href="#persistencia" class="hover:text-white transition">Dados</a>
      <a href="#equipe" class="hover:text-white transition">Equipe</a>
    </div>

    <a href="{{ url('/admin/login') }}" class="group inline-flex items-center gap-2 bg-senai-red hover:bg-senai-red-dk transition px-5 py-2.5 rounded-md text-sm font-semibold red-glow-sm">
      Acessar Sistema
      <svg class="w-4 h-4 group-hover:translate-x-0.5 transition" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
    </a>
  </div>
</nav>

<!-- ============== HERO ============== -->
<section id="top" class="relative min-h-screen flex items-center pt-24 pb-16 overflow-hidden">
  <div class="absolute inset-0 grid-pattern"></div>
  <div class="absolute inset-0 hero-vignette"></div>

  <!-- Diagonal stripe accent -->
  <div class="absolute top-0 right-0 w-1 h-32 bg-senai-red"></div>
  <div class="absolute bottom-0 left-0 w-32 h-1 bg-senai-red"></div>

  <div class="relative max-w-7xl mx-auto px-6 lg:px-8 w-full">
    <div class="max-w-4xl">
      <!-- Badge -->
      <div class="inline-flex items-center gap-2 bg-senai-red/10 border border-senai-red/30 px-4 py-1.5 rounded-full mb-8">
        <span class="w-2 h-2 bg-senai-red rounded-full animate-pulse-slow"></span>
        <span class="text-xs font-medium uppercase tracking-widest text-senai-red">SENAI · Projeto Acadêmico · 3º DEV</span>
      </div>

      <h1 class="text-5xl md:text-7xl font-extrabold tracking-tight leading-[1.05] mb-6">
        Gestão de Manutenção
        <span class="block text-senai-red">Predial</span>
      </h1>

      <p class="text-lg md:text-xl text-senai-gray-300 max-w-2xl mb-10 leading-relaxed">
        Sistema web para centralizar o controle patrimonial e as atividades de manutenção em instalações prediais do SENAI. Construído com tecnologias modernas, foco em segurança e auditoria.
      </p>

      <div class="flex flex-wrap items-center gap-4 mb-16">
        <a href="{{ url('/admin/login') }}" class="group inline-flex items-center gap-2 bg-senai-red hover:bg-senai-red-dk transition px-7 py-3.5 rounded-md font-semibold red-glow">
          Acessar Painel Administrativo
          <svg class="w-4 h-4 group-hover:translate-x-1 transition" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </a>
        <a href="{{ url('/procedimentos-operacionais') }}" class="inline-flex items-center gap-2 border border-senai-gray-700 hover:border-senai-red/60 hover:text-senai-red transition px-7 py-3.5 rounded-md font-medium">
          Procedimentos Operacionais
        </a>
        <a href="#sobre" class="inline-flex items-center gap-2 border border-senai-gray-700 hover:border-senai-gray-400 transition px-7 py-3.5 rounded-md font-medium">
          Conhecer o sistema
        </a>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-px bg-senai-gray-800 border border-senai-gray-800 rounded-lg overflow-hidden">
        <div class="bg-senai-gray-950 p-6">
          <div class="text-3xl md:text-4xl font-bold text-senai-red mb-1">6</div>
          <div class="text-xs uppercase tracking-wider text-senai-gray-400">Módulos CRUD</div>
        </div>
        <div class="bg-senai-gray-950 p-6">
          <div class="text-3xl md:text-4xl font-bold text-senai-red mb-1">4</div>
          <div class="text-xs uppercase tracking-wider text-senai-gray-400">Entidades principais</div>
        </div>
        <div class="bg-senai-gray-950 p-6">
          <div class="text-3xl md:text-4xl font-bold text-senai-red mb-1">21</div>
          <div class="text-xs uppercase tracking-wider text-senai-gray-400">Rotas do painel</div>
        </div>
        <div class="bg-senai-gray-950 p-6">
          <div class="text-3xl md:text-4xl font-bold text-senai-red mb-1">100<span class="text-2xl">%</span></div>
          <div class="text-xs uppercase tracking-wider text-senai-gray-400">Auditável</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Scroll hint -->
  <div class="absolute bottom-6 left-1/2 -translate-x-1/2 text-senai-gray-400 text-xs flex flex-col items-center gap-2 animate-pulse-slow">
    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
  </div>
</section>

<!-- ============== SOBRE ============== -->
<section id="sobre" class="py-24 px-6 lg:px-8 border-t border-senai-gray-800">
  <div class="max-w-7xl mx-auto reveal">
    <div class="grid lg:grid-cols-12 gap-12">
      <div class="lg:col-span-5">
        <div class="text-xs uppercase tracking-widest text-senai-red font-semibold mb-4">/ Sobre o sistema</div>
        <h2 class="text-4xl md:text-5xl font-bold mb-6 leading-tight">
          Substituindo planilhas por um <span class="text-senai-red">painel administrativo</span> moderno.
        </h2>
      </div>
      <div class="lg:col-span-7 space-y-5 text-senai-gray-300 leading-relaxed text-base">
        <p>
          O GMP centraliza o cadastro de <strong class="text-white">empresas, setores, patrimônios, colaboradores e responsáveis</strong>, oferecendo uma visão integrada de todos os recursos envolvidos na manutenção das instalações prediais do SENAI.
        </p>
        <p>
          Desenvolvido pela turma de Desenvolvimento de Sistemas (3º DEV) do SENAI Limeira, o sistema digitaliza processos antes descentralizados, padronizando o fluxo de cadastro, classificação por prioridade e rastreabilidade de cada chamado.
        </p>
        <div class="grid sm:grid-cols-2 gap-4 pt-4">
          <div class="bg-senai-gray-900 border border-senai-gray-800 p-5 rounded-lg card-lift">
            <div class="text-senai-red mb-2">
              <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <h3 class="font-semibold mb-1">Para quem é</h3>
            <p class="text-sm text-senai-gray-400">Administradores, diretores, professores, responsáveis e colaboradores técnicos.</p>
          </div>
          <div class="bg-senai-gray-900 border border-senai-gray-800 p-5 rounded-lg card-lift">
            <div class="text-senai-red mb-2">
              <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
            </div>
            <h3 class="font-semibold mb-1">Onde nasceu</h3>
            <p class="text-sm text-senai-gray-400">Curso Técnico em Desenvolvimento de Sistemas · SENAI São Paulo · Limeira.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============== STACK ============== -->
<section id="stack" class="py-24 px-6 lg:px-8 bg-senai-gray-950 border-y border-senai-gray-800">
  <div class="max-w-7xl mx-auto reveal">
    <div class="max-w-2xl mb-16">
      <div class="text-xs uppercase tracking-widest text-senai-red font-semibold mb-4">/ Stack tecnológica</div>
      <h2 class="text-4xl md:text-5xl font-bold mb-4 leading-tight">Construído com ferramentas <span class="text-senai-red">consolidadas no mercado</span>.</h2>
      <p class="text-senai-gray-400">Cada peça foi escolhida pela maturidade, comunidade ativa e adequação ao escopo acadêmico do projeto.</p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
      <!-- Tech card template -->
      <div class="bg-senai-gray-900 border border-senai-gray-800 p-6 rounded-lg card-lift">
        <div class="flex items-start justify-between mb-3">
          <div class="w-10 h-10 bg-senai-red/10 rounded-md flex items-center justify-center text-senai-red font-mono font-bold text-sm">PHP</div>
          <span class="text-[10px] font-mono uppercase tracking-wider text-senai-gray-400 bg-senai-gray-800 px-2 py-0.5 rounded">8.2+</span>
        </div>
        <h3 class="font-semibold mb-1">PHP</h3>
        <p class="text-xs text-senai-gray-400 leading-relaxed">Linguagem server-side. Base de toda a lógica de negócio.</p>
      </div>

      <div class="bg-senai-gray-900 border border-senai-gray-800 p-6 rounded-lg card-lift">
        <div class="flex items-start justify-between mb-3">
          <div class="w-10 h-10 bg-senai-red/10 rounded-md flex items-center justify-center text-senai-red">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor"><path d="M21.5 11.6 14.4 4.5l-1.4 1.4 2.5 2.5L4 19.5l1.4 1.4 11.5-11.5 2.5 2.5z"/></svg>
          </div>
          <span class="text-[10px] font-mono uppercase tracking-wider text-senai-gray-400 bg-senai-gray-800 px-2 py-0.5 rounded">12.0</span>
        </div>
        <h3 class="font-semibold mb-1">Laravel</h3>
        <p class="text-xs text-senai-gray-400 leading-relaxed">Framework MVC. Rotas, Eloquent ORM, migrations e autenticação.</p>
      </div>

      <div class="bg-senai-gray-900 border border-senai-gray-800 p-6 rounded-lg card-lift">
        <div class="flex items-start justify-between mb-3">
          <div class="w-10 h-10 bg-senai-red/10 rounded-md flex items-center justify-center text-senai-red">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
          </div>
          <span class="text-[10px] font-mono uppercase tracking-wider text-senai-gray-400 bg-senai-gray-800 px-2 py-0.5 rounded">3.3</span>
        </div>
        <h3 class="font-semibold mb-1">Filament</h3>
        <p class="text-xs text-senai-gray-400 leading-relaxed">Painéis admin (CRUD) gerados a partir de Resources PHP.</p>
      </div>

      <div class="bg-senai-gray-900 border border-senai-gray-800 p-6 rounded-lg card-lift">
        <div class="flex items-start justify-between mb-3">
          <div class="w-10 h-10 bg-senai-red/10 rounded-md flex items-center justify-center text-senai-red">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14a9 3 0 0 0 18 0V5"/><path d="M3 12a9 3 0 0 0 18 0"/></svg>
          </div>
          <span class="text-[10px] font-mono uppercase tracking-wider text-senai-gray-400 bg-senai-gray-800 px-2 py-0.5 rounded">8.0+</span>
        </div>
        <h3 class="font-semibold mb-1">MySQL</h3>
        <p class="text-xs text-senai-gray-400 leading-relaxed">Banco relacional. Persistência dos cadastros e auditoria.</p>
      </div>

      <div class="bg-senai-gray-900 border border-senai-gray-800 p-6 rounded-lg card-lift">
        <div class="flex items-start justify-between mb-3">
          <div class="w-10 h-10 bg-senai-red/10 rounded-md flex items-center justify-center text-senai-red">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M4 12h16M4 18h7"/></svg>
          </div>
          <span class="text-[10px] font-mono uppercase tracking-wider text-senai-gray-400 bg-senai-gray-800 px-2 py-0.5 rounded">4.0</span>
        </div>
        <h3 class="font-semibold mb-1">Tailwind CSS</h3>
        <p class="text-xs text-senai-gray-400 leading-relaxed">Utility-first CSS. Estilização do painel Filament.</p>
      </div>

      <div class="bg-senai-gray-900 border border-senai-gray-800 p-6 rounded-lg card-lift">
        <div class="flex items-start justify-between mb-3">
          <div class="w-10 h-10 bg-senai-red/10 rounded-md flex items-center justify-center text-senai-red">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m13 2-2 2.5h3L12 7"/><path d="M19 9A7 7 0 1 1 5 9c0-2 1-3.5 3-5.5L12 6l4-2.5C18 5.5 19 7 19 9z"/></svg>
          </div>
          <span class="text-[10px] font-mono uppercase tracking-wider text-senai-gray-400 bg-senai-gray-800 px-2 py-0.5 rounded">6.0</span>
        </div>
        <h3 class="font-semibold mb-1">Vite</h3>
        <p class="text-xs text-senai-gray-400 leading-relaxed">Bundler moderno. Compilação otimizada de assets.</p>
      </div>

      <div class="bg-senai-gray-900 border border-senai-gray-800 p-6 rounded-lg card-lift">
        <div class="flex items-start justify-between mb-3">
          <div class="w-10 h-10 bg-senai-red/10 rounded-md flex items-center justify-center text-senai-red">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          </div>
          <span class="text-[10px] font-mono uppercase tracking-wider text-senai-gray-400 bg-senai-gray-800 px-2 py-0.5 rounded">REST</span>
        </div>
        <h3 class="font-semibold mb-1">ViaCEP API</h3>
        <p class="text-xs text-senai-gray-400 leading-relaxed">Auto-preenchimento de endereços a partir do CEP.</p>
      </div>

      <div class="bg-senai-gray-900 border border-senai-gray-800 p-6 rounded-lg card-lift">
        <div class="flex items-start justify-between mb-3">
          <div class="w-10 h-10 bg-senai-red/10 rounded-md flex items-center justify-center text-senai-red">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 18 22 12 16 6"/><path d="M8 6 2 12 8 18"/></svg>
          </div>
          <span class="text-[10px] font-mono uppercase tracking-wider text-senai-gray-400 bg-senai-gray-800 px-2 py-0.5 rounded">11.x</span>
        </div>
        <h3 class="font-semibold mb-1">PHPUnit</h3>
        <p class="text-xs text-senai-gray-400 leading-relaxed">Testes automatizados garantindo a confiabilidade.</p>
      </div>
    </div>
  </div>
</section>

<!-- ============== SEGURANÇA ============== -->
<section id="seguranca" class="py-24 px-6 lg:px-8 relative overflow-hidden">
  <!-- accent stripe -->
  <div class="absolute right-0 top-1/2 -translate-y-1/2 w-1 h-40 bg-senai-red"></div>

  <div class="max-w-7xl mx-auto reveal">
    <div class="max-w-2xl mb-16">
      <div class="text-xs uppercase tracking-widest text-senai-red font-semibold mb-4">/ Segurança em camadas</div>
      <h2 class="text-4xl md:text-5xl font-bold mb-4 leading-tight">A proteção dos dados é <span class="text-senai-red">inegociável</span>.</h2>
      <p class="text-senai-gray-400">Padrões consolidados do Laravel e do Filament, aplicados de ponta a ponta — do hash de senha à proteção CSRF de cada formulário.</p>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div class="bg-senai-gray-900 border border-senai-gray-800 p-6 rounded-lg card-lift">
        <div class="flex items-center gap-3 mb-3">
          <div class="w-10 h-10 bg-senai-red rounded-md flex items-center justify-center red-glow-sm">
            <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><circle cx="12" cy="16" r="1"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          </div>
          <h3 class="font-semibold">Autenticação Laravel</h3>
        </div>
        <p class="text-sm text-senai-gray-400 leading-relaxed">Middleware <span class="font-mono text-senai-red">auth</span> protege todas as rotas <span class="font-mono text-xs">/admin/*</span>. Sessões revalidadas a cada requisição.</p>
      </div>

      <div class="bg-senai-gray-900 border border-senai-gray-800 p-6 rounded-lg card-lift">
        <div class="flex items-center gap-3 mb-3">
          <div class="w-10 h-10 bg-senai-red rounded-md flex items-center justify-center red-glow-sm">
            <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
          </div>
          <h3 class="font-semibold">Hashing bcrypt</h3>
        </div>
        <p class="text-sm text-senai-gray-400 leading-relaxed">Senhas nunca em texto puro. Cast <span class="font-mono text-senai-red">hashed</span> com 12 rounds padrão Laravel.</p>
      </div>

      <div class="bg-senai-gray-900 border border-senai-gray-800 p-6 rounded-lg card-lift">
        <div class="flex items-center gap-3 mb-3">
          <div class="w-10 h-10 bg-senai-red rounded-md flex items-center justify-center red-glow-sm">
            <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          </div>
          <h3 class="font-semibold">Proteção CSRF</h3>
        </div>
        <p class="text-sm text-senai-gray-400 leading-relaxed">Todos os formulários protegidos por token. Livewire injeta o token automaticamente.</p>
      </div>

      <div class="bg-senai-gray-900 border border-senai-gray-800 p-6 rounded-lg card-lift">
        <div class="flex items-center gap-3 mb-3">
          <div class="w-10 h-10 bg-senai-red rounded-md flex items-center justify-center red-glow-sm">
            <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="12" x2="2" y2="12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/><line x1="6" y1="16" x2="6.01" y2="16"/><line x1="10" y1="16" x2="10.01" y2="16"/></svg>
          </div>
          <h3 class="font-semibold">Cookies criptografados</h3>
        </div>
        <p class="text-sm text-senai-gray-400 leading-relaxed">EncryptCookies middleware criptografa todos os cookies antes de enviar ao navegador.</p>
      </div>

      <div class="bg-senai-gray-900 border border-senai-gray-800 p-6 rounded-lg card-lift">
        <div class="flex items-center gap-3 mb-3">
          <div class="w-10 h-10 bg-senai-red rounded-md flex items-center justify-center red-glow-sm">
            <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><path d="M20 8v6M23 11h-6"/></svg>
          </div>
          <h3 class="font-semibold">Controle por cargo</h3>
        </div>
        <p class="text-sm text-senai-gray-400 leading-relaxed">RBAC via campo <span class="font-mono text-senai-red">cargo</span>: admin, diretor, professor, suporte, colaborador, responsável.</p>
      </div>

      <div class="bg-senai-gray-900 border border-senai-gray-800 p-6 rounded-lg card-lift">
        <div class="flex items-center gap-3 mb-3">
          <div class="w-10 h-10 bg-senai-red rounded-md flex items-center justify-center red-glow-sm">
            <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          </div>
          <h3 class="font-semibold">Sessão controlada</h3>
        </div>
        <p class="text-sm text-senai-gray-400 leading-relaxed">Driver <span class="font-mono text-senai-red">database</span>. Tempo de vida 120 min com auto-revalidação.</p>
      </div>
    </div>
  </div>
</section>

<!-- ============== PERSISTÊNCIA ============== -->
<section id="persistencia" class="py-24 px-6 lg:px-8 bg-senai-gray-950 border-y border-senai-gray-800">
  <div class="max-w-7xl mx-auto reveal">
    <div class="grid lg:grid-cols-12 gap-12 items-center">
      <div class="lg:col-span-5">
        <div class="text-xs uppercase tracking-widest text-senai-red font-semibold mb-4">/ Persistência de dados</div>
        <h2 class="text-4xl md:text-5xl font-bold mb-6 leading-tight">Dados <span class="text-senai-red">íntegros</span>, schema versionado.</h2>
        <p class="text-senai-gray-400 leading-relaxed mb-6">
          A estrutura do banco é descrita por migrations versionadas no Git — qualquer mudança é rastreável e reversível. O Eloquent ORM abstrai SQL e padroniza relacionamentos entre todas as entidades.
        </p>
        <p class="text-senai-gray-400 leading-relaxed">
          Política de backup automático diário (RNF07), com integridade verificada e retenção configurável para evitar perda de informações operacionais.
        </p>
      </div>
      <div class="lg:col-span-7">
        <!-- Pseudo-terminal showing migrations -->
        <div class="bg-senai-black border border-senai-gray-800 rounded-lg overflow-hidden shadow-2xl">
          <div class="flex items-center gap-2 px-4 py-3 border-b border-senai-gray-800 bg-senai-gray-900">
            <div class="flex gap-1.5">
              <div class="w-3 h-3 rounded-full bg-senai-red"></div>
              <div class="w-3 h-3 rounded-full bg-senai-gray-700"></div>
              <div class="w-3 h-3 rounded-full bg-senai-gray-700"></div>
            </div>
            <span class="text-xs text-senai-gray-400 ml-2 font-mono">artisan migrate</span>
          </div>
          <div class="p-6 font-mono text-xs leading-relaxed space-y-1.5">
            <div class="text-senai-gray-400">$ <span class="text-white">php artisan migrate</span></div>
            <div class="text-senai-gray-400">&nbsp;</div>
            <div><span class="text-senai-gray-400">  INFO</span>  <span class="text-white">Running migrations.</span></div>
            <div class="text-senai-gray-400">&nbsp;</div>
            <div class="flex justify-between"><span class="text-senai-gray-300">  2024_01_01_000000_create_users_table</span><span class="text-senai-red font-semibold">12.4ms DONE</span></div>
            <div class="flex justify-between"><span class="text-senai-gray-300">  2024_01_02_000000_create_empresas_table</span><span class="text-senai-red font-semibold">8.1ms DONE</span></div>
            <div class="flex justify-between"><span class="text-senai-gray-300">  2024_01_03_000000_create_setors_table</span><span class="text-senai-red font-semibold">6.7ms DONE</span></div>
            <div class="flex justify-between"><span class="text-senai-gray-300">  2024_01_04_000000_create_patrimonios_table</span><span class="text-senai-red font-semibold">9.2ms DONE</span></div>
            <div class="flex justify-between"><span class="text-senai-gray-300">  2024_01_05_000000_add_cargo_to_users</span><span class="text-senai-red font-semibold">4.8ms DONE</span></div>
            <div class="flex justify-between"><span class="text-senai-gray-300">  2024_01_06_000000_create_sessions_table</span><span class="text-senai-red font-semibold">5.3ms DONE</span></div>
            <div class="text-senai-gray-400">&nbsp;</div>
            <div class="text-white">  ✓ <span class="text-senai-gray-300">Schema migrado. Backup configurado.</span></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============== EQUIPE ============== -->
<section id="equipe" class="py-24 px-6 lg:px-8">
  <div class="max-w-7xl mx-auto reveal">
    <div class="max-w-2xl mb-16">
      <div class="text-xs uppercase tracking-widest text-senai-red font-semibold mb-4">/ A equipe</div>
      <h2 class="text-4xl md:text-5xl font-bold mb-4 leading-tight">Três desenvolvedores, <span class="text-senai-red">um produto</span>.</h2>
      <p class="text-senai-gray-400">Alunos da turma 3º DEV — A do Curso Técnico em Desenvolvimento de Sistemas do SENAI Limeira.</p>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
      <div class="group bg-senai-gray-900 border border-senai-gray-800 p-8 rounded-lg card-lift relative overflow-hidden">
        <div class="absolute top-0 right-0 w-24 h-24 bg-senai-red/5 rounded-full -translate-y-12 translate-x-12 group-hover:scale-150 transition-transform duration-500"></div>
        <div class="relative">
          <div class="w-16 h-16 bg-gradient-to-br from-senai-red to-senai-red-dk rounded-full flex items-center justify-center text-2xl font-bold mb-5 red-glow-sm">JM</div>
          <h3 class="text-lg font-semibold mb-1">Jefferson Bruno C. Miguel</h3>
          <p class="text-sm text-senai-gray-400 mb-4">Desenvolvedor</p>
          <div class="text-xs font-mono text-senai-gray-600 uppercase tracking-wider">3º DEV · A</div>
        </div>
      </div>

      <div class="group bg-senai-gray-900 border border-senai-gray-800 p-8 rounded-lg card-lift relative overflow-hidden">
        <div class="absolute top-0 right-0 w-24 h-24 bg-senai-red/5 rounded-full -translate-y-12 translate-x-12 group-hover:scale-150 transition-transform duration-500"></div>
        <div class="relative">
          <div class="w-16 h-16 bg-gradient-to-br from-senai-red to-senai-red-dk rounded-full flex items-center justify-center text-2xl font-bold mb-5 red-glow-sm">LT</div>
          <h3 class="text-lg font-semibold mb-1">Lucas Terminiello</h3>
          <p class="text-sm text-senai-gray-400 mb-4">Desenvolvedor</p>
          <div class="text-xs font-mono text-senai-gray-600 uppercase tracking-wider">3º DEV · A</div>
        </div>
      </div>

      <div class="group bg-senai-gray-900 border border-senai-gray-800 p-8 rounded-lg card-lift relative overflow-hidden">
        <div class="absolute top-0 right-0 w-24 h-24 bg-senai-red/5 rounded-full -translate-y-12 translate-x-12 group-hover:scale-150 transition-transform duration-500"></div>
        <div class="relative">
          <div class="w-16 h-16 bg-gradient-to-br from-senai-red to-senai-red-dk rounded-full flex items-center justify-center text-2xl font-bold mb-5 red-glow-sm">MM</div>
          <h3 class="text-lg font-semibold mb-1">Matheus Malaman</h3>
          <p class="text-sm text-senai-gray-400 mb-4">Desenvolvedor</p>
          <div class="text-xs font-mono text-senai-gray-600 uppercase tracking-wider">3º DEV · A</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============== CTA FINAL ============== -->
<section class="py-24 px-6 lg:px-8 relative overflow-hidden border-t border-senai-gray-800">
  <div class="absolute inset-0 grid-pattern opacity-50"></div>
  <div class="absolute inset-0 hero-vignette"></div>
  <div class="relative max-w-4xl mx-auto text-center reveal">
    <h2 class="text-4xl md:text-6xl font-bold mb-6 leading-tight">
      Pronto para começar?
    </h2>
    <p class="text-lg text-senai-gray-300 mb-10 max-w-2xl mx-auto">
      Acesse o painel administrativo e comece a gerenciar empresas, setores, patrimônios e equipes em um só lugar.
    </p>
    <a href="{{ url('/admin/login') }}" class="group inline-flex items-center gap-3 bg-senai-red hover:bg-senai-red-dk transition px-9 py-4 rounded-md font-semibold text-lg red-glow">
      Acessar Painel Administrativo
      <svg class="w-5 h-5 group-hover:translate-x-1 transition" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
    </a>
  </div>
</section>

<!-- ============== FOOTER ============== -->
<footer class="border-t border-senai-gray-800 py-12 px-6 lg:px-8">
  <div class="max-w-7xl mx-auto">
    <div class="flex flex-col md:flex-row items-center justify-between gap-6">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 bg-senai-red rounded-md flex items-center justify-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg>
        </div>
        <div class="leading-tight">
          <div class="font-bold">Gestão de Manutenção Predial</div>
          <div class="text-xs text-senai-gray-400">SENAI São Paulo · Limeira · 2026</div>
        </div>
      </div>
      <div class="text-xs text-senai-gray-600 text-center md:text-right">
        Trabalho acadêmico · 3º DEV — A · Curso Técnico em Desenvolvimento de Sistemas<br/>
        Construído com Laravel 12, Filament 3.3 e MySQL 8
      </div>
    </div>
  </div>
</footer>

<script>
  // Reveal-on-scroll
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) entry.target.classList.add('visible');
    });
  }, { threshold: 0.1 });
  document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>

</body>
</html>
