<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>

<div
    x-data="{
        aba: 'camera',
        estado: 'idle',
        codigoDetectado: '',
        codigoDigitado: '',
        stream: null,
        animFrame: null,

        init() {
            this.$watch('aba', (valor) => {
                if (valor === 'camera') {
                    this.iniciarCamera();
                } else {
                    this.pararStream();
                    this.estado = 'idle';
                }
            });
        },

        destroy() {
            this.pararStream();
        },

        async iniciarCamera() {
            if (!this.$refs.video) return;
            this.estado = 'lendo';
            try {
                this.stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'environment' }
                });
                const video = this.$refs.video;
                if (!video) { this.pararStream(); return; }
                video.srcObject = this.stream;
                await video.play();
                this.lerFrames();
            } catch (e) {
                this.estado = 'erro';
                this.aba = 'digitar';
            }
        },

        lerFrames() {
            const video = this.$refs.video;
            const canvas = this.$refs.canvas;
            const ctx = canvas.getContext('2d');

            const tick = () => {
                if (video.readyState === video.HAVE_ENOUGH_DATA) {
                    canvas.width  = video.videoWidth;
                    canvas.height = video.videoHeight;
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    const resultado = jsQR(imageData.data, imageData.width, imageData.height);

                    if (resultado && resultado.data) {
                        this.pararStream();
                        this.codigoDetectado = resultado.data;
                        this.estado = 'detectado';
                        setTimeout(() => {
                            $wire.buscarPatrimonio(resultado.data);
                        }, 800);
                        return;
                    }
                }
                this.animFrame = requestAnimationFrame(tick);
            };

            this.animFrame = requestAnimationFrame(tick);
        },

        pararStream() {
            if (this.animFrame) {
                cancelAnimationFrame(this.animFrame);
                this.animFrame = null;
            }
            if (this.stream) {
                this.stream.getTracks().forEach(t => t.stop());
                this.stream = null;
            }
        },

        buscarManual() {
            const codigo = this.codigoDigitado.trim();
            if (!codigo) return;
            $wire.buscarPatrimonio(codigo);
        },
    }"
    x-init="init()"
    @keydown.escape.window="pararStream()"
    class="w-full"
>
    {{-- Título --}}
    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3">
        Identificar patrimônio
    </p>

    {{-- Abas --}}
    <div class="flex gap-2 mb-4 border-b border-gray-200 dark:border-gray-700">
        <button
            type="button"
            @click="aba = 'camera'"
            :class="aba === 'camera'
                ? 'border-b-2 border-primary-500 text-primary-600 dark:text-primary-400'
                : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
            class="pb-2 px-1 text-sm font-medium transition-colors"
        >
            Câmera
        </button>
        <button
            type="button"
            @click="aba = 'digitar'"
            :class="aba === 'digitar'
                ? 'border-b-2 border-primary-500 text-primary-600 dark:text-primary-400'
                : 'text-gray-500 dark:text-gray-400 hover:text-gray-700'"
            class="pb-2 px-1 text-sm font-medium transition-colors"
        >
            Digitar
        </button>
    </div>

    {{-- Aba Câmera --}}
    <div x-show="aba === 'camera'" x-intersect.once="iniciarCamera()" class="flex flex-col items-center gap-3">

        {{-- Feedback de estado --}}
        <div class="w-full text-center text-sm">
            <span x-show="estado === 'idle'" class="text-gray-500 dark:text-gray-400">
                Aponte a câmera para o QR Code da etiqueta
            </span>
            <span x-show="estado === 'lendo'" class="flex items-center justify-center gap-2 text-gray-500 dark:text-gray-400">
                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                Lendo...
            </span>
            <span x-show="estado === 'detectado'" class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-success-100 text-success-700 dark:bg-success-900 dark:text-success-300 font-mono text-xs">
                ✓ Código detectado: <span x-text="codigoDetectado"></span>
            </span>
            <span x-show="estado === 'erro'" class="text-danger-600 dark:text-danger-400">
                Câmera não disponível — use a aba Digitar
            </span>
        </div>

        {{-- Vídeo --}}
        <div class="relative w-full max-w-sm rounded-lg overflow-hidden bg-black">
            <video
                x-ref="video"
                muted
                playsinline
                :class="estado === 'detectado' ? 'ring-4 ring-success-500' : ''"
                class="w-full rounded-lg transition-all"
            ></video>
        </div>

        {{-- Canvas oculto para captura de frames --}}
        <canvas x-ref="canvas" class="hidden"></canvas>
    </div>

    {{-- Aba Digitar --}}
    <div x-show="aba === 'digitar'" class="flex flex-col gap-3">
        <div class="flex gap-2">
            <input
                x-model="codigoDigitado"
                @keydown.enter="buscarManual()"
                type="text"
                placeholder="Cole ou digite o código da etiqueta"
                class="flex-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
            />
            <x-filament::button
                type="button"
                @click="buscarManual()"
            >
                Buscar
            </x-filament::button>
        </div>
        <p class="text-xs text-gray-400 dark:text-gray-500">
            Pressione Enter ou clique em Buscar para localizar o patrimônio.
        </p>
    </div>
</div>
