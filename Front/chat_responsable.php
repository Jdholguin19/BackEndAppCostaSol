<?php
session_start();
require_once __DIR__ . '/../config/db.php';
$db = DB::getDB();

$token = $_POST['token'] ?? null;
$showAccessDenied = false;
$responsable = null;

if ($token) {
    try {
        $stmt = $db->prepare('SELECT id, nombre, correo FROM responsable WHERE token = ? AND estado = 1');
        $stmt->execute([$token]);
        $responsable = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$responsable) { $showAccessDenied = true; }
    } catch (Exception $e) { $showAccessDenied = true; }
} else {
    $showAccessDenied = true;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chat Responsable</title>
  <link rel="stylesheet" href="assets/css/chat.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,'Helvetica Neue',Arial;} .brand{display:flex;align-items:center;gap:8px;font-weight:700;padding:14px 20px;} .brand i{color:#1677ff;} .topbar{display:flex;justify-content:space-between;align-items:center;padding:8px 20px;border-bottom:1px solid #e5e7eb;background:#fff;}</style>
</head>
<body>
<?php if ($showAccessDenied): ?>
  <div style="padding:40px;text-align:center">
    <h2>Acceso denegado</h2>
    <p>Debes iniciar sesión como responsable.</p>
    <a href="login_front.php">Ir al login</a>
  </div>
<?php else: ?>
  <div class="topbar">
    <div class="brand"><i class="bi bi-chat-dots"></i> Centro de Mensajes</div>
    <div>
      <span style="margin-right:16px;color:#6b7280">Responsable: <?php echo htmlspecialchars($responsable['nombre']); ?></span>
      <a href="menu_front.php" style="text-decoration:none;color:#1677ff">Volver</a>
    </div>
  </div>

  <div class="resp-wrap">
    <!-- Lista de hilos -->
    <div class="resp-panel">
      <div class="panel-header">
        <span>Conversaciones</span>
        <button id="refreshThreads" class="btn btn-sm" style="border:1px solid #e5e7eb">Actualizar</button>
      </div>
      <div style="padding:12px"><input id="searchInput" class="resp-search" placeholder="Buscar por nombre o ID" /></div>
      <div id="threadList" class="thread-list"></div>
    </div>

    <!-- Chat seleccionado -->
    <div class="resp-panel resp-chat">
      <div class="panel-header">
        <div>
          <div id="chatTitle">Selecciona una conversación</div>
          <div id="chatSubtitle" style="color:#6b7280;font-size:12px"></div>
        </div>
        <div class="chat-options">
          <button id="optionsBtn" title="Opciones"><i class="bi bi-three-dots"></i></button>
          <div id="optionsMenu" class="chat-options-menu">
            <div class="item" id="optExpand">Ampliar ventana</div>
            <div class="item" id="optTranscript">Descargar transcripción</div>
          </div>
        </div>
      </div>
      <div id="chatBody" class="chat-body"></div>
      <div class="chat-footer">
        <input id="chatInput" class="chat-input" placeholder="Escribe un mensaje..." />
        <button id="sendBtn" class="chat-send">Enviar</button>
      </div>
    </div>
  </div>
<?php endif; ?>

<script>
(function(){
  const token = localStorage.getItem('cs_token');
  if (!token) { window.location.href = 'login_front.php'; return; }

  // Validar servidor y render si OK
  const formData = new FormData();
  formData.append('token', token);
  fetch(window.location.href, { method:'POST', body:formData })
    .then(r=>r.text())
    .then(html=>{ /* página ya renderizada en PHP; no re-render aquí */ })
    .catch(()=>{});

  const API_THREADS = '../api/chat/threads.php';
  const API_THREAD  = '../api/chat/thread.php';
  const API_MSG     = '../api/chat/messages.php';
  const API_TRANS   = '../api/chat/transcript.php';

  const refs = {
    search: document.getElementById('searchInput'),
    list: document.getElementById('threadList'),
    title: document.getElementById('chatTitle'),
    subtitle: document.getElementById('chatSubtitle'),
    body: document.getElementById('chatBody'),
    input: document.getElementById('chatInput'),
    send: document.getElementById('sendBtn'),
    refresh: document.getElementById('refreshThreads'),
    optBtn: document.getElementById('optionsBtn'),
    optMenu: document.getElementById('optionsMenu'),
    optExpand: document.getElementById('optExpand'),
    optTranscript: document.getElementById('optTranscript'),
  };

  let currentThreadId = null;
  let polling = null;

  async function fetchThreads(){
    const q = refs.search.value.trim();
    const url = new URL(API_THREADS, window.location.origin);
    if(q) url.searchParams.set('q', q);
    const r = await fetch(url, { headers: { 'Authorization': `Bearer ${token}` }});
    const data = await r.json();
    if(!data.ok) return;
    renderThreadList(data.threads || []);
  }

  function renderThreadList(items){
    refs.list.innerHTML = '';
    if(!items.length){
      refs.list.innerHTML = '<div style="padding:14px;color:#6b7280">Sin conversaciones</div>';
      return;
    }
    items.forEach(t=>{
      const li = document.createElement('div');
      li.className = 'thread-item';
      li.innerHTML = `<div class="name">${escapeHtml(t.user_name||('Usuario #'+t.user_id))}</div>
                      <div class="snippet">${escapeHtml((t.last_message||'').slice(0,80))}</div>`;
      li.onclick = ()=> openThread(t);
      refs.list.appendChild(li);
    });
  }

  async function openThread(t){
    currentThreadId = t.id;
    refs.title.textContent = t.user_name || ('Usuario #' + t.user_id);
    refs.subtitle.textContent = 'Hilo #' + t.id;
    refs.body.innerHTML = '';
    await loadMessages();
    if(polling) clearInterval(polling);
    polling = setInterval(loadMessages, 2500);
  }

  async function loadMessages(){
    if(!currentThreadId) return;
    const url = new URL(API_MSG, window.location.origin);
    url.searchParams.set('thread_id', currentThreadId);
    url.searchParams.set('limit', 200);
    const r = await fetch(url, { headers: { 'Authorization': `Bearer ${token}` }});
    const data = await r.json();
    if(!data.ok) return;
    renderMessages(data.messages||[]);
    refs.body.scrollTop = refs.body.scrollHeight;
  }

  function renderMessages(msgs){
    refs.body.innerHTML = '';
    msgs.forEach(m=>{
      const row = document.createElement('div');
      row.className = 'message-row';
      const bubble = document.createElement('div');
      bubble.className = 'message ' + (m.sender_type==='responsable'?'responsable':'user');
      
      // Render reply preview if present
      if (m.reply_to) {
        const replyPreview = document.createElement('div');
        replyPreview.className = 'reply-preview';
        replyPreview.innerHTML = `
          <div class="reply-author">${m.reply_to.sender_type === 'user' ? 'Usuario' : 'Responsable'}</div>
          <div class="reply-text">${m.reply_to.content || ''}</div>
        `;
        bubble.appendChild(replyPreview);
      }
      
      // Render message content (attachments or text)
      const messageContent = document.createElement('div');
      messageContent.className = 'message-content';
      const text = String(m.content||'');
      if (isAttachmentUrl(text)) {
        renderAttachment(messageContent, text);
      } else {
        messageContent.textContent = text;
      }
      bubble.appendChild(messageContent);

      row.appendChild(bubble);
      refs.body.appendChild(row);
    });
  }

  function isAttachmentUrl(url){
    const u = String(url||'');
    if (!/^https?:\/\//i.test(u)) return false;
    return /\/uploads\//i.test(u);
  }

  function renderAttachment(container, url){
    const lower = url.toLowerCase();
    const isImg = /\.(png|jpg|jpeg|gif|webp)$/i.test(lower);
    let isAudio = /\.(mp3|wav|m4a|ogg)$/i.test(lower) || lower.includes('/uploads/audio/');
    let isVideo = /\.(mp4|webm|mov)$/i.test(lower);

    // Tratar .webm como audio si el navegador lo soporta o si proviene de /uploads/chat/
    if (/\.webm$/i.test(lower)) {
      let treatAsAudio = false;
      const probe = document.createElement('audio');
      if (typeof probe.canPlayType === 'function') {
        const support = probe.canPlayType('audio/webm; codecs=opus') || probe.canPlayType('audio/webm');
        if (support) treatAsAudio = true;
      }
      if (lower.includes('/uploads/chat/')) {
        treatAsAudio = true; // tus audios se guardan ahí
      }
      if (treatAsAudio) { isAudio = true; isVideo = false; }
    }

    if (isImg) {
      const img = document.createElement('img');
      img.src = url; container.appendChild(img);
    } else if (isAudio) {
      const audio = document.createElement('audio');
      audio.controls = true; audio.src = url; container.appendChild(audio);
    } else if (isVideo) {
      const video = document.createElement('video');
      video.controls = true; video.src = url; container.appendChild(video);
    } else {
      const a = document.createElement('a'); a.href=url; a.target='_blank'; a.textContent=url; container.appendChild(a);
    }
  }

  async function sendMessage(){
    const content = refs.input.value.trim();
    if(!content || !currentThreadId) return;
    refs.input.value='';
    // Optimista
    const row = document.createElement('div');
    row.className = 'message-row';
    const bubble = document.createElement('div');
    bubble.className = 'message responsable';
    bubble.textContent = content;
    row.appendChild(bubble);
    refs.body.appendChild(row);
    refs.body.scrollTop = refs.body.scrollHeight;

    await fetch(API_MSG, {
      method:'POST',
      headers:{ 'Authorization': `Bearer ${token}`, 'Content-Type':'application/json' },
      body: JSON.stringify({ thread_id: currentThreadId, content })
    });
  }

  function escapeHtml(s){ return (s||'').replace(/[&<>\"]/g, c=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;"}[c])); }

  // Opciones
  refs.optBtn.addEventListener('click', ()=>{
    refs.optMenu.classList.toggle('open');
  });
  document.addEventListener('click', (e)=>{
    if(!refs.optMenu.contains(e.target) && e.target!==refs.optBtn){ refs.optMenu.classList.remove('open'); }
  });
  refs.optExpand.addEventListener('click', ()=>{
    document.querySelector('.resp-chat').classList.toggle('fullscreen');
  });
  refs.optTranscript.addEventListener('click', ()=>{
    if(!currentThreadId) return;
    const url = new URL(API_TRANS, window.location.origin);
    url.searchParams.set('thread_id', currentThreadId);
    fetch(url, { headers:{ 'Authorization': `Bearer ${token}` }})
      .then(r=>r.blob())
      .then(blob=>{
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = `chat_transcript_${currentThreadId}.txt`;
        document.body.appendChild(a); a.click(); a.remove();
      });
  });

  refs.send.addEventListener('click', sendMessage);
  refs.input.addEventListener('keydown', e=>{ if(e.key==='Enter') sendMessage(); });
  refs.refresh.addEventListener('click', fetchThreads);
  refs.search.addEventListener('input', ()=>{ clearTimeout(window.__throttle); window.__throttle=setTimeout(fetchThreads, 250); });

  fetchThreads();
})();
</script>
</body>
</html>