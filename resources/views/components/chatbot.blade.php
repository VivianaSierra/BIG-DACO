<div x-data="chatbot('{{ route('chatbot') }}')" class="fixed bottom-5 right-5 z-50">
    <div x-show="open" x-cloak class="mb-3 flex h-[28rem] w-80 flex-col overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200 sm:w-96">
        <div class="bg-gradient-to-r from-sky-600 to-blue-700 px-4 py-3 text-sm font-bold text-white">Asistente BIG DACO</div>

        <div class="flex-1 space-y-3 overflow-y-auto p-3 text-sm" x-ref="box">
            <p class="rounded-xl bg-slate-100 p-2 text-slate-600">Hola, pregunta por productos, stock, inventario o ventas.</p>
            <template x-for="(m, i) in messages" :key="i">
                <div :class="m.role === 'user' ? 'text-right' : ''">
                    <span class="inline-block max-w-[85%] whitespace-pre-wrap rounded-xl px-3 py-2"
                          :class="m.role === 'user' ? 'bg-sky-600 text-white' : 'bg-slate-100 text-slate-800'"
                          x-text="m.text"></span>
                </div>
            </template>
            <p x-show="loading" class="text-slate-400">Escribiendo...</p>
        </div>

        <form @submit.prevent="send" class="flex gap-2 border-t border-slate-200 p-2">
            <input x-model="input" type="text" maxlength="1000" placeholder="Escribe tu pregunta"
                   class="flex-1 rounded-lg border-slate-300 text-sm focus:border-sky-500 focus:ring-sky-500">
            <button :disabled="loading" class="rounded-lg bg-sky-600 px-3 text-sm font-semibold text-white disabled:opacity-50">Enviar</button>
        </form>
    </div>

    <button @click="open = !open" class="ml-auto flex h-14 w-14 items-center justify-center rounded-full bg-sky-600 text-2xl text-white shadow-lg hover:bg-sky-700">
        <span x-text="open ? '×' : '💬'"></span>
    </button>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('chatbot', (url) => ({
            open: false, input: '', loading: false, messages: [],
            async send() {
                const text = this.input.trim();
                if (!text || this.loading) return;
                const history = this.messages.slice(-10);
                this.messages.push({ role: 'user', text });
                this.input = '';
                this.loading = true;
                this.scroll();
                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        },
                        body: JSON.stringify({ message: text, history }),
                    });
                    const data = await res.json();
                    this.messages.push({ role: 'model', text: data.reply ?? 'Error al procesar la consulta.' });
                } catch (e) {
                    this.messages.push({ role: 'model', text: 'Error de conexion.' });
                }
                this.loading = false;
                this.scroll();
            },
            scroll() { this.$nextTick(() => this.$refs.box.scrollTop = this.$refs.box.scrollHeight); },
        }));
    });
</script>
