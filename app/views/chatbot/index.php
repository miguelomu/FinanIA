<?php include __DIR__ . '/../../../includes/header.php'; ?>

<style>
  .chat-container {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 200px);
    max-height: 700px;
    background: rgba(19, 47, 76, 0.5);
    border-radius: 15px;
    border: 1px solid rgba(212, 175, 55, 0.2);
    overflow: hidden;
  }
  
  .chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 25px;
    display: flex;
    flex-direction: column;
    gap: 15px;
  }
  
  .message {
    display: flex;
    gap: 10px;
    max-width: 80%;
    animation: fadeIn 0.3s;
  }
  
  .message.user {
    align-self: flex-end;
    flex-direction: row-reverse;
  }
  
  .message .avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
  }
  
  .message.bot .avatar {
    background: linear-gradient(135deg, #d4af37, #b8860b);
  }
  
  .message.user .avatar {
    background: linear-gradient(135deg, #3498db, #2874a6);
  }
  
  .message .bubble {
    padding: 12px 18px;
    border-radius: 15px;
    font-size: 0.9rem;
    line-height: 1.6;
    white-space: pre-wrap;
  }
  
  .message.bot .bubble {
    background: rgba(212, 175, 55, 0.15);
    border: 1px solid rgba(212, 175, 55, 0.3);
    color: #e6edf3;
    border-top-left-radius: 4px;
  }
  
  .message.user .bubble {
    background: linear-gradient(135deg, #3498db, #2874a6);
    color: #fff;
    border-top-right-radius: 4px;
  }
  
  .chat-input-container {
    padding: 20px;
    background: rgba(10, 25, 41, 0.7);
    border-top: 1px solid rgba(212, 175, 55, 0.2);
    display: flex;
    gap: 10px;
  }
  
  .chat-input-container input {
    flex: 1;
    padding: 14px 20px;
    background: rgba(10, 25, 41, 0.9);
    border: 1px solid rgba(212, 175, 55, 0.3);
    border-radius: 25px;
    color: #e6edf3;
    font-size: 0.95rem;
    font-family: inherit;
  }
  
  .chat-input-container input:focus {
    outline: none;
    border-color: #d4af37;
    box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.15);
  }
  
  .chat-input-container button {
    padding: 14px 30px;
    border-radius: 25px;
    background: linear-gradient(135deg, #d4af37, #b8860b);
    color: #0a1929;
    border: none;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s;
  }
  
  .chat-input-container button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(212, 175, 55, 0.4);
  }
  
  .typing {
    display: inline-flex;
    gap: 4px;
    padding: 12px 18px;
  }
  
  .typing span {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #d4af37;
    animation: typing 1.4s infinite;
  }
  
  .typing span:nth-child(2) { animation-delay: 0.2s; }
  .typing span:nth-child(3) { animation-delay: 0.4s; }
  
  @keyframes typing {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-8px); }
  }
  
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }
  
  .sugerencias {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    padding: 0 20px 15px 20px;
  }
  
  .sugerencias button {
    padding: 8px 15px;
    background: rgba(212, 175, 55, 0.15);
    border: 1px solid rgba(212, 175, 55, 0.3);
    border-radius: 20px;
    color: #d4af37;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.2s;
  }
  
  .sugerencias button:hover {
    background: rgba(212, 175, 55, 0.3);
  }

  .message .bubble strong {
  color: #d4af37;
  font-weight: 700;
}

[data-tema="claro"] .message .bubble strong {
  color: #b8860b;
}

.message .bubble em {
  font-style: italic;
  color: #c9d6e3;
}

.message .bubble br {
  margin-bottom: 4px;
}
</style>

<div class="layout">
  <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <h1>🤖 FinanIA - Asistente Financiero</h1>
      <form method="POST" action="index.php?accion=limpiar" style="margin:0;" id="formLimpiar">
        <?= csrfField() ?>
        <button type="button" class="btn btn-secondary" 
                onclick="confirmar(event, {
                  titulo: '¿Limpiar historial?',
                  mensaje: 'Se eliminarán todas las conversaciones con el asistente. Esta acción no se puede deshacer.',
                  tipo: 'warning',
                  icono: '🗑️',
                  confirmar: 'Limpiar todo',
                  form: '#formLimpiar'
                })">
          🗑️ Limpiar historial
        </button>
      </form>
    </div>

    <div class="chat-container">
      <div class="chat-messages" id="chatMessages">
        <?php if (empty($historial)): ?>
          <!-- Mensaje de bienvenida -->
          <div class="message bot">
            <div class="avatar">🤖</div>
            <div class="bubble">¡Hola! Soy <strong>FinanIA</strong>, tu asistente financiero personal. 👋

Puedo ayudarte a:
• 💰 Consultar tu patrimonio, deudas y ahorros
• 📊 Analizar tus finanzas del mes
• 🔮 Hacer proyecciones y predicciones
• 💡 Darte recomendaciones financieras
• 📚 Explicarte conceptos financieros

Escribe tu pregunta o prueba uno de los ejemplos de abajo. 👇</div>
          </div>
        <?php else: ?>
          <?php foreach ($historial as $h): ?>
            <div class="message <?= $h['rol'] === 'user' ? 'user' : 'bot' ?>">
              <div class="avatar"><?= $h['rol'] === 'user' ? '👤' : '🤖' ?></div>
              <div class="bubble"><?= nl2br(e($h['mensaje'])) ?></div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Sugerencias -->
      <div class="sugerencias">
        <button onclick="enviarSugerencia('¿Cuánto tengo en total?')">💰 Mi patrimonio</button>
        <button onclick="enviarSugerencia('¿Cuánto debo?')">💳 Mis deudas</button>
        <button onclick="enviarSugerencia('¿Cómo van mis finanzas este mes?')">📊 Balance del mes</button>
        <button onclick="enviarSugerencia('¿Podré ahorrar 5 millones en 6 meses?')">🔮 Proyección</button>
        <button onclick="enviarSugerencia('¿Debería invertir?')">💡 Inversión</button>
        <button onclick="enviarSugerencia('¿Qué puedes hacer?')">❓ Ayuda</button>
      </div>

      <div class="chat-input-container">
        <input type="text" id="chatInput" placeholder="Escribe tu pregunta..." 
               autocomplete="off" autofocus>
        <button type="button" onclick="enviarMensaje()">Enviar 📤</button>
      </div>
    </div>
  </main>
</div>

<script>
const chatMessages = document.getElementById('chatMessages');
const chatInput = document.getElementById('chatInput');
const csrfToken = '<?= csrfToken() ?>';

// Scroll al final
function scrollAbajo() {
  chatMessages.scrollTop = chatMessages.scrollHeight;
}
scrollAbajo();

// Enviar con Enter
chatInput.addEventListener('keydown', (e) => {
  if (e.key === 'Enter') {
    e.preventDefault();
    enviarMensaje();
  }
});

function enviarSugerencia(texto) {
  chatInput.value = texto;
  enviarMensaje();
}

async function enviarMensaje() {
  const mensaje = chatInput.value.trim();
  if (!mensaje) return;

  // Mostrar mensaje del usuario
  agregarMensaje('user', mensaje);
  chatInput.value = '';
  chatInput.disabled = true;

  // Mostrar indicador de escritura
  const typing = document.createElement('div');
  typing.className = 'message bot';
  typing.id = 'typingIndicator';
  typing.innerHTML = `
    <div class="avatar">🤖</div>
    <div class="bubble typing">
      <span></span><span></span><span></span>
    </div>
  `;
  chatMessages.appendChild(typing);
  scrollAbajo();

  try {
    const formData = new FormData();
    formData.append('mensaje', mensaje);
    formData.append('csrf_token', csrfToken);

    const response = await fetch('index.php?accion=enviar', {
      method: 'POST',
      body: formData
    });

    const data = await response.json();

    // Remover indicador de escritura
    typing.remove();

    if (data.success) {
      agregarMensaje('bot', data.respuesta);
    } else {
      agregarMensaje('bot', '❌ Error: ' + (data.error || 'No pude procesar tu mensaje'));
    }
  } catch (error) {
    typing.remove();
    agregarMensaje('bot', '❌ Error de conexión. Intenta de nuevo.');
  } finally {
    chatInput.disabled = false;
    chatInput.focus();
  }
}

function agregarMensaje(rol, texto) {
  const div = document.createElement('div');
  div.className = 'message ' + rol;
  
  // Convertir markdown a HTML si es mensaje del bot
  const contenidoHTML = rol === 'bot' ? convertirMarkdown(texto) : texto.replace(/\n/g, '<br>');
  
  div.innerHTML = `
    <div class="avatar">${rol === 'user' ? '👤' : '🤖'}</div>
    <div class="bubble">${contenidoHTML}</div>
  `;
  chatMessages.appendChild(div);
  scrollAbajo();
}

/**
 * Convierte Markdown básico a HTML:
 * **negrita** → <strong>negrita</strong>
 * *cursiva* → <em>cursiva</em>
 * \n → <br>
 */
function convertirMarkdown(texto) {
  let html = texto;
  
  // Escapar HTML para seguridad (evitar XSS)
  html = html.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  
  // Convertir **negrita** a <strong>
  html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
  
  // Convertir *cursiva* a <em> (después de las negritas para no interferir)
  html = html.replace(/\*([^*]+?)\*/g, '<em>$1</em>');
  
  // Convertir saltos de línea
  html = html.replace(/\n/g, '<br>');
  
  return html;
}
</script>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>