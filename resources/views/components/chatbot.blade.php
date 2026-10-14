<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    #bd-chat-btn{position:fixed;bottom:20px;right:20px;z-index:9999;width:56px;height:56px;border-radius:9999px;border:0;background:#0284c7;color:#fff;font-size:26px;cursor:pointer;box-shadow:0 6px 18px rgba(0,0,0,.3)}
    #bd-chat-btn:hover{background:#0369a1}
    #bd-chat{position:fixed;bottom:88px;right:20px;z-index:9999;width:360px;max-width:calc(100vw - 40px);height:28rem;max-height:calc(100vh - 110px);display:none;flex-direction:column;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 12px 40px rgba(0,0,0,.3);font:14px system-ui,sans-serif}
    #bd-chat.open{display:flex}
    #bd-chat header{background:linear-gradient(90deg,#0284c7,#1d4ed8);color:#fff;padding:12px 16px;font-weight:700}
    #bd-chat-msgs{flex:1;overflow-y:auto;padding:12px;display:flex;flex-direction:column;gap:8px}
    #bd-chat-msgs div{max-width:85%;padding:8px 12px;border-radius:12px;white-space:pre-wrap;background:#f1f5f9;color:#1e293b;align-self:flex-start}
    #bd-chat-msgs div.user{background:#0284c7;color:#fff;align-self:flex-end}
    #bd-chat form{display:flex;gap:8px;padding:8px;border-top:1px solid #e2e8f0}
    #bd-chat input{flex:1;padding:8px 10px;border:1px solid #cbd5e1;border-radius:8px;font:inherit}
    #bd-chat form button{padding:0 14px;border:0;border-radius:8px;background:#0284c7;color:#fff;font-weight:600;cursor:pointer}
    #bd-chat form button:disabled{opacity:.5}
</style>

<div id="bd-chat" aria-live="polite">
    <header>Asistente BIG DACO</header>
    <div id="bd-chat-msgs"><div>Hola, pregunta por productos, stock, inventario o ventas.&#10;Hi! Ask me about products, stock, inventory or sales.</div></div>
    <form id="bd-chat-form">
            <input id="bd-chat-input" type="text" maxlength="1000" placeholder="Escribe tu pregunta / Type your question" autocomplete="off">
            <button type="submit">Enviar / Send</button>
    </form>
</div>
<button id="bd-chat-btn" type="button" aria-label="Abrir asistente">💬</button>

<script>
    (function () {
        const url = @json(route('chatbot'));
        const token = document.querySelector('meta[name=csrf-token]').content;
        const panel = document.getElementById('bd-chat');
        const msgs = document.getElementById('bd-chat-msgs');
        const form = document.getElementById('bd-chat-form');
        const input = document.getElementById('bd-chat-input');
        const btn = form.querySelector('button');
        const history = [];

        document.getElementById('bd-chat-btn').addEventListener('click', () => {
            panel.classList.toggle('open');
            if (panel.classList.contains('open')) input.focus();
        });

        function add(role, text) {
            const el = document.createElement('div');
            el.className = role === 'user' ? 'user' : '';
            el.textContent = text;
            msgs.appendChild(el);
            msgs.scrollTop = msgs.scrollHeight;
            return el;
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const text = input.value.trim();
            if (!text || btn.disabled) return;
            input.value = '';
            btn.disabled = true;
            const sent = history.slice(-10);
            add('user', text);
            const wait = add('model', 'Escribiendo... / Typing...');
            let reply;
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token},
                    body: JSON.stringify({message: text, history: sent}),
                });
                reply = (await res.json()).reply ?? 'Error al procesar la consulta.';
            } catch (err) {
                reply = 'Error de conexión / Connection error.';
            }
            wait.textContent = reply;
            history.push({role: 'user', text}, {role: 'model', text: reply});
            btn.disabled = false;
            msgs.scrollTop = msgs.scrollHeight;
        });
    })();
</script>
