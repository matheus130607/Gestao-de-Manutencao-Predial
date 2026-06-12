<style>
    #senai-qr-reader { border: none !important; padding: 0 !important; background: transparent !important; }
    #senai-qr-reader > * { border: none !important; }
    #senai-qr-reader__scan_region video { width: 100% !important; height: auto !important; display: block !important; border-radius: 0.75rem; }
    #senai-qr-reader__dashboard { display: none !important; }
    #senai-qr-reader video:not(:first-of-type) { display: none !important; }
    #senai-qr-reader__header_message { display: none !important; }
    .qr-shaded-region { display: none !important; }
</style>

<div
    x-data="{
        scanner: null,
        estado: 'iniciando',
        codigo: '',
        iniciado: false,

        init() {
            setTimeout(() => this.iniciar(), 450);
        },

        destroy() {
            this.parar();
        },

        async iniciar() {
            if (this.iniciado) return;
            this.iniciado = true;
            try {
                const cameras = await Html5Qrcode.getCameras();
                if (!cameras || cameras.length === 0) {
                    this.estado = 'erro';
                    return;
                }
                // pega a última câmera da lista (em laptops é a única; em celulares costuma ser a traseira)
                const fisica = cameras.find(c =>
                    c.label && !/(virtual|obs|snap|manycam|droid|ivcam|epoc|splitcam)/i.test(c.label)
                ) ?? cameras[0];
                const camId = fisica.id;
                this.scanner = new Html5Qrcode('senai-qr-reader');
                await this.scanner.start(
                    camId,
                    { fps: 10 },
                    (texto) => {
                        this.codigo = texto;
                        this.estado = 'detectado';
                        this.parar();
                        setTimeout(() => $wire.buscarPatrimonio(texto), 600);
                    },
                    () => {}
                );
                this.estado = 'lendo';
            } catch (e) {
                console.error('[QR] iniciar:', e);
                this.iniciado = false;
                this.estado = 'erro';
            }
        },

        parar() {
            if (this.scanner) {
                this.scanner.stop().catch(() => {}).finally(() => {
                    try { this.scanner.clear(); } catch(e) {}
                    this.scanner = null;
                });
            }
        },

        buscarManual() {
            const c = this.codigo.trim();
            if (!c) return;
            $wire.buscarPatrimonio(c);
        },
    }"
    x-init="init()"
    class="w-full space-y-4"
>
    {{-- Container da câmera — sempre no DOM para html5-qrcode renderizar corretamente --}}
    <div class="relative w-full rounded-xl bg-gray-900" style="min-height: 260px;">

        <div id="senai-qr-reader" class="w-full"></div>

        {{-- Overlay: iniciando --}}
        <div
            x-show="estado === 'iniciando'"
            class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-gray-900"
        >
            <svg class="animate-spin h-8 w-8 text-red-500" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
            <span class="text-sm text-gray-400">Abrindo câmera...</span>
        </div>

        {{-- Overlay: detectado --}}
        <div
            x-show="estado === 'detectado'"
            class="absolute inset-0 flex items-center justify-center bg-black/70"
        >
            <div class="flex items-center gap-2 bg-green-500 text-white px-5 py-3 rounded-full text-sm font-semibold shadow-lg">
                <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                <span x-text="'Código: ' + codigo" class="font-mono"></span>
            </div>
        </div>

        {{-- Overlay: erro --}}
        <div
            x-show="estado === 'erro'"
            class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-gray-900"
        >
            <svg class="h-8 w-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-sm text-gray-400">Câmera não disponível.</span>
            <span class="text-xs text-gray-500">Use o campo abaixo.</span>
        </div>
    </div>

    {{-- Digitação manual --}}
    <div class="flex gap-2">
        <input
            x-model="codigo"
            @keydown.enter="buscarManual()"
            type="text"
            placeholder="Ou digite o código da etiqueta"
            class="flex-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
        />
        <x-filament::button type="button" @click="buscarManual()">
            Buscar
        </x-filament::button>
    </div>
</div>
