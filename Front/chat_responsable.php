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
    // Si no hay token en POST, verificar si hay uno en localStorage via JavaScript
    if (!isset($_SESSION['responsable_id'])) {
        $showAccessDenied = true;
    }
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
  <style>
    body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,'Helvetica Neue',Arial;} 
    .brand{display:flex;align-items:center;gap:8px;font-weight:700;padding:14px 20px;} 
    .brand i{color:#1677ff;} 
    .topbar{display:flex;justify-content:space-between;align-items:center;padding:8px 20px;border-bottom:1px solid #e5e7eb;background:#fff;}
    
    /* Chat input container styles */
    .chat-input-container {
      display: flex;
      align-items: center;
      gap: 8px;
      width: 100%;
    }
    
    .chat-input {
      flex: 1;
      min-width: 0;
    }
    
    .chat-buttons {
      display: flex;
      align-items: center;
      gap: 4px;
    }
    
    .chat-btn {
      background: none;
      border: none;
      padding: 8px;
      border-radius: 6px;
      color: #6b7280;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s;
    }
    
    .chat-btn:hover {
      background: #f3f4f6;
      color: #374151;
    }
    
    .chat-btn i {
      font-size: 16px;
    }
    
    /* Image size limitations */
    .message img {
      max-width: 300px;
      max-height: 200px;
      width: auto;
      height: auto;
      border-radius: 8px;
      object-fit: cover;
    }
    
    /* Reply container styles */
    .reply-container {
      background: #f8f9fa;
      border-left: 3px solid #1677ff;
      padding: 8px 12px;
      margin-bottom: 8px;
      border-radius: 6px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .reply-preview {
      flex: 1;
    }
    
    .reply-author {
      font-size: 12px;
      font-weight: 600;
      color: #1677ff;
      margin-bottom: 2px;
    }
    
    .reply-text {
      font-size: 13px;
      color: #6b7280;
      max-width: 300px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
    
    .cancel-reply {
      background: none;
      border: none;
      color: #6b7280;
      cursor: pointer;
      font-size: 18px;
      padding: 4px;
      border-radius: 4px;
      transition: all 0.2s;
    }
    
    .cancel-reply:hover {
      background: #e5e7eb;
      color: #374151;
    }
    
    /* Reply button in messages */
    .reply-btn {
      position: absolute;
      top: 8px;
      right: 8px;
      background: rgba(255, 255, 255, 0.9);
      border: 1px solid #e5e7eb;
      border-radius: 4px;
      padding: 4px 6px;
      font-size: 12px;
      color: #6b7280;
      cursor: pointer;
      opacity: 0;
      transition: all 0.2s;
    }
    
    .message:hover .reply-btn {
      opacity: 1;
    }
    
    .reply-btn:hover {
      background: #f3f4f6;
      color: #374151;
    }
    
    .message {
      position: relative;
    }
  </style>
</head>
<body>

<div id="accessDenied" style="padding:40px;text-align:center;display:none;">
  <h2>Acceso denegado</h2>
  <p>Debes iniciar sesión como responsable.</p>
  <a href="login_front.php">Ir al login</a>
</div>

<div id="chatInterface" style="display:none;">
  <div class="topbar">
    <div class="brand"><i class="bi bi-chat-dots"></i> Centro de Mensajes</div>
    <div>
      <span id="responsableName" style="margin-right:16px;color:#6b7280"></span>
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
        <div id="replyContainer" class="reply-container" style="display:none;">
          <div class="reply-preview">
            <div class="reply-author" id="replyAuthor"></div>
            <div class="reply-text" id="replyText"></div>
          </div>
          <button id="cancelReply" class="cancel-reply">&times;</button>
        </div>
        <div class="chat-input-container">
          <button id="btnEmoji" class="chat-btn" title="Emoji"><i class="bi bi-emoji-smile"></i></button>
          <input id="chatInput" class="chat-input" placeholder="Escribe un mensaje..." />
          <button id="btnAttach" class="chat-btn" title="Adjuntar archivo"><i class="bi bi-paperclip"></i></button>
          <button id="btnCamera" class="chat-btn" title="Tomar foto"><i class="bi bi-camera"></i></button>
          <button id="btnAudio" class="chat-btn" title="Grabar audio"><i class="bi bi-mic"></i></button>
          <button id="sendBtn" class="chat-send" style="display: none;"><i class="bi bi-send-fill"></i></button>
        </div>
        <div id="emojiPanel" class="emoji-panel" style="display:none">
          <div class="emoji-grid"></div>
        </div>
        <input type="file" id="fileInput" style="display:none" accept="image/*,audio/*,video/*,.pdf,.doc,.docx,.txt" />
        <input type="file" id="cameraInput" style="display:none" accept="image/*" capture="environment" />
      </div>
    </div>
  </div>
</div>

<!-- Image Modal -->
<div id="imageModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 10002; justify-content: center; align-items: center; padding: 20px; box-sizing: border-box;">
  <div style="position: relative; max-width: min(90vw, 800px); max-height: min(90vh, 600px); display: flex; flex-direction: column; align-items: center;">
    <!-- Close button -->
    <button id="closeModal" style="position: absolute; top: -50px; right: 0; background: rgba(255,255,255,0.9); color: #333; border: none; border-radius: 50%; width: 40px; height: 40px; cursor: pointer; font-size: 20px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.3); z-index: 10003;">&times;</button>
    
    <!-- Image container -->
    <div style="position: relative; width: 100%; height: 100%; display: flex; justify-content: center; align-items: center; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
      <img id="modalImage" style="max-width: 100%; max-height: 100%; width: auto; height: auto; object-fit: contain; display: block;">
    </div>
    
    <!-- Download button -->
    <div style="margin-top: 15px;">
      <button id="downloadModal" style="background: rgba(255,255,255,0.9); color: #333; border: none; border-radius: 20px; padding: 8px 16px; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); font-size: 14px;">
        <i class="bi bi-download"></i> Descargar
      </button>
    </div>
  </div>
</div>

<style>
/* Responsive rules for image modal */
@media (max-width: 768px) {
  #imageModal > div {
    max-width: 95vw !important;
    max-height: 85vh !important;
  }
  
  #closeModal {
    top: -45px !important;
    width: 36px !important;
    height: 36px !important;
    font-size: 18px !important;
  }
  
  #downloadModal {
    padding: 10px 20px !important;
    font-size: 16px !important;
  }
}

@media (max-width: 480px) {
  #imageModal {
    padding: 15px !important;
  }
  
  #imageModal > div {
    max-width: 98vw !important;
    max-height: 80vh !important;
  }
  
  #closeModal {
    top: -40px !important;
    right: -5px !important;
  }
}

@media (min-width: 1200px) {
  #imageModal > div {
    max-width: 70vw !important;
    max-height: 70vh !important;
  }
}
</style>

<script>
(function(){
  const token = localStorage.getItem('cs_token');
  if (!token) { 
    document.getElementById('accessDenied').style.display = 'block';
    return; 
  }

  // Validar token con el servidor
  const formData = new FormData();
  formData.append('token', token);
  fetch(window.location.href, { method:'POST', body:formData })
    .then(r=>r.text())
    .then(html=>{
      // Si llegamos aquí, el token es válido
      document.getElementById('chatInterface').style.display = 'block';
      initializeChat();
    })
    .catch(()=>{
      document.getElementById('accessDenied').style.display = 'block';
    });

  function initializeChat() {
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
      btnEmoji: document.getElementById('btnEmoji'),
      emojiPanel: document.getElementById('emojiPanel'),
      btnAttach: document.getElementById('btnAttach'),
      btnCamera: document.getElementById('btnCamera'),
      btnAudio: document.getElementById('btnAudio'),
      sendBtn: document.getElementById('sendBtn'),
      fileInput: document.getElementById('fileInput'),
      cameraInput: document.getElementById('cameraInput'),
      replyContainer: document.getElementById('replyContainer'),
      imageModal: document.getElementById('imageModal'),
      modalImage: document.getElementById('modalImage'),
      closeModal: document.getElementById('closeModal'),
      downloadModal: document.getElementById('downloadModal'),
      replyAuthor: document.getElementById('replyAuthor'),
      replyText: document.getElementById('replyText'),
      cancelReply: document.getElementById('cancelReply'),
    };

    let currentThreadId = null;
    let polling = null;
    let replyingTo = null;

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

    let isFirstLoad = true; // Track if this is the first load of messages

    async function openThread(t){
      currentThreadId = t.id;
      refs.title.textContent = t.user_name || ('Usuario #' + t.user_id);
      refs.subtitle.textContent = 'Hilo #' + t.id;
      refs.body.innerHTML = '';
      
      // Clear any existing reply state when opening a new thread
      if (replyingTo) {
        cancelReply();
      }
      
      // Reset first load flag when opening a new thread
      isFirstLoad = true;
      
      await loadMessages();
      if(polling) clearInterval(polling);
      polling = setInterval(loadMessages, 3000);
    }

    async function loadMessages(){
      if(!currentThreadId) return;
      const url = new URL(API_MSG, window.location.origin);
      url.searchParams.set('thread_id', currentThreadId);
      const r = await fetch(url, { headers: { 'Authorization': `Bearer ${token}` }});
      const data = await r.json();
      if(!data.ok) return;
      renderMessages(data.messages || []);
      
      // Only auto-scroll on first load (when opening thread or page reload)
      if (isFirstLoad) {
        setTimeout(() => scrollToBottom(), 100);
        isFirstLoad = false;
      }
    }

    async function sendMessage(){
      const text = refs.input.value.trim();
      if(!text || !currentThreadId) return;
      refs.input.value = '';
      const payload = {
        thread_id: currentThreadId,
        content: text,
        sender_type: 'responsable'
      };
      
      // Add reply information if replying to a message
      if (replyingTo) {
        payload.reply_to_id = replyingTo.id;
      }
      
      await fetch(API_MSG, { 
        method:'POST', 
        headers:{ 
          'Content-Type':'application/json', 
          'Authorization': `Bearer ${token}` 
        }, 
        body: JSON.stringify(payload) 
      });
      
      // Clear reply state after sending
      if (replyingTo) {
        cancelReply();
      }
      
      loadMessages();
    }

    // File upload handler
    async function handleFileUpload(e) {
      const file = e.target.files[0];
      if (!file || !currentThreadId) return;
      
      const form = new FormData();
      form.append('file', file);
      form.append('thread_id', currentThreadId);
      
      try {
        const r = await fetch('../api/chat/upload.php', { 
          method:'POST', 
          headers:{ 'Authorization': `Bearer ${token}` }, 
          body: form 
        });
        const data = await r.json();
        if (data.ok && data.url) {
          await fetch(API_MSG, { 
            method:'POST', 
            headers:{ 
              'Content-Type':'application/json', 
              'Authorization': `Bearer ${token}` 
            }, 
            body: JSON.stringify({ 
              thread_id: currentThreadId, 
              content: data.url,
              sender_type: 'responsable'
            }) 
          });
          loadMessages();
        }
      } catch (err) {
        console.error('Upload error:', err);
      }
      e.target.value = '';
    }

    // Audio recording variables
    let mediaRecorder = null;
    let audioChunks = [];
    let isRecording = false;

    async function toggleAudioRecording() {
      if (!currentThreadId) return;
      
      if (!isRecording) {
        try {
          const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
          mediaRecorder = new MediaRecorder(stream);
          audioChunks = [];
          
          mediaRecorder.ondataavailable = e => audioChunks.push(e.data);
          mediaRecorder.onstop = async () => {
            const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
            const form = new FormData();
            form.append('file', audioBlob, 'audio.webm');
            form.append('thread_id', currentThreadId);
            
            try {
              const r = await fetch('../api/chat/upload.php', { 
                method:'POST', 
                headers:{ 'Authorization': `Bearer ${token}` }, 
                body: form 
              });
              const data = await r.json();
              if (data.ok && data.url) {
                await fetch(API_MSG, { 
                  method:'POST', 
                  headers:{ 
                    'Content-Type':'application/json', 
                    'Authorization': `Bearer ${token}` 
                  }, 
                  body: JSON.stringify({ 
                    thread_id: currentThreadId, 
                    content: data.url,
                    sender_type: 'responsable'
                  }) 
                });
                loadMessages();
              }
            } catch (err) {
              console.error('Audio upload error:', err);
            }
            
            stream.getTracks().forEach(track => track.stop());
          };
          
          mediaRecorder.start();
          isRecording = true;
          refs.btnAudio.innerHTML = '<i class="bi bi-stop-fill"></i>';
          refs.btnAudio.style.color = '#ef4444';
        } catch (err) {
          console.error('Audio recording error:', err);
        }
      } else {
        mediaRecorder.stop();
        isRecording = false;
        refs.btnAudio.innerHTML = '<i class="bi bi-mic"></i>';
        refs.btnAudio.style.color = '';
      }
    }

    function renderMessages(messages){
      refs.body.innerHTML = '';
      messages.forEach(m=>{
        const row = document.createElement('div');
        row.className = 'message-row';
        
        const bubble = document.createElement('div');
        bubble.className = `message ${m.sender_type}`;
        bubble.setAttribute('data-message-id', m.id); // Add message ID for scrolling
        
        // Reply preview if exists
        if (m.reply_to && m.reply_to.content) {
          const replyPreview = document.createElement('div');
          replyPreview.className = 'reply-preview';
          replyPreview.style.cursor = 'pointer';
          
          const replyAuthor = document.createElement('div');
          replyAuthor.className = 'reply-author';
          replyAuthor.textContent = m.reply_to.sender_type === 'user' ? 'Usuario' : 'Responsable';
          
          const replyText = document.createElement('div');
          replyText.className = 'reply-text';
          
          // Check if reply content is a multimedia file
          if (isAttachmentUrl(m.reply_to.content)) {
            const replyContent = getReplyPreviewContent(m.reply_to.content);
            replyText.innerHTML = replyContent;
          } else {
            replyText.textContent = m.reply_to.content || '';
          }
          
          replyPreview.appendChild(replyAuthor);
          replyPreview.appendChild(replyText);
          
          // Add click event to scroll to original message
          replyPreview.addEventListener('click', () => {
            scrollToMessage(m.reply_to.id);
          });
          
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

        // Add reply button
        const replyBtn = document.createElement('button');
        replyBtn.className = 'reply-btn';
        replyBtn.innerHTML = '<i class="bi bi-reply"></i>';
        replyBtn.title = 'Responder';
        replyBtn.onclick = () => setReplyTo(m);
        bubble.appendChild(replyBtn);

        row.appendChild(bubble);
        refs.body.appendChild(row);
      });
      // Remove auto-scroll from renderMessages - it should only happen on first load
    }

    function isAttachmentUrl(url){
      const u = String(url||'');
      // Detectar URLs que empiecen con http/https o rutas relativas que contengan /uploads/
      return /^https?:\/\//i.test(u) || /^\/?uploads\//i.test(u);
    }

    // Function to generate preview content for multimedia files in replies
    function getReplyPreviewContent(url) {
      const lower = String(url).toLowerCase();
      const isImg = /(\.png|\.jpg|\.jpeg|\.gif|\.webp|\.bmp|\.svg)(\?.*)?$/i.test(lower);
      let isAudio = /(\.mp3|\.wav|\.ogg|\.webm|\.m4a)(\?.*)?$/i.test(lower) || url.startsWith('blob:') || /^data:audio\//i.test(url);
      let isVideo = /(\.mp4|\.webm|\.mov)(\?.*)?$/i.test(lower);

      // Forzar .webm como audio si proviene de /uploads/chat/
      if (/\.webm(\?.*)?$/i.test(lower) && /\/uploads\/chat\//i.test(lower)) {
        isAudio = true;
        isVideo = false;
      }

      if (isImg) {
        return '<i class="bi bi-image" style="margin-right: 4px;"></i>Imagen';
      } else if (isAudio) {
        return '<i class="bi bi-mic-fill" style="margin-right: 4px;"></i>Audio';
      } else if (isVideo) {
        return '<i class="bi bi-play-circle" style="margin-right: 4px;"></i>Video';
      } else {
        return '<i class="bi bi-paperclip" style="margin-right: 4px;"></i>Archivo';
      }
    }

    function renderAttachment(container, url){
      const lower = String(url).toLowerCase();
      const isImg = /(\.png|\.jpg|\.jpeg|\.gif|\.webp|\.bmp|\.svg)(\?.*)?$/i.test(lower);
      let isAudio = /(\.mp3|\.wav|\.ogg|\.webm|\.m4a)(\?.*)?$/i.test(lower) || url.startsWith('blob:') || /^data:audio\//i.test(url);
      let isVideo = /(\.mp4|\.webm|\.mov)(\?.*)?$/i.test(lower);

      // Forzar .webm como audio si proviene de /uploads/chat/
      if (/\.webm(\?.*)?$/i.test(lower) && /\/uploads\/chat\//i.test(lower)) {
        isAudio = true;
        isVideo = false;
      }

      if (isImg) {
        const imgContainer = document.createElement('div');
        imgContainer.style.position = 'relative';
        imgContainer.style.display = 'inline-block';
        
        const img = document.createElement('img');
        img.src = url;
        img.alt = 'imagen';
        img.style.maxWidth = '100%';
        img.style.borderRadius = '12px';
        img.style.cursor = 'pointer';
        
        // Add click event to open image in modal
        img.addEventListener('click', () => {
          openImageModal(url);
        });
        
        // Add download button overlay
        const downloadBtn = document.createElement('button');
        downloadBtn.innerHTML = '<i class="bi bi-download"></i>';
        downloadBtn.style.position = 'absolute';
        downloadBtn.style.top = '8px';
        downloadBtn.style.right = '8px';
        downloadBtn.style.background = 'rgba(0,0,0,0.7)';
        downloadBtn.style.color = 'white';
        downloadBtn.style.border = 'none';
        downloadBtn.style.borderRadius = '50%';
        downloadBtn.style.width = '32px';
        downloadBtn.style.height = '32px';
        downloadBtn.style.cursor = 'pointer';
        downloadBtn.style.display = 'none';
        downloadBtn.style.alignItems = 'center';
        downloadBtn.style.justifyContent = 'center';
        downloadBtn.title = 'Descargar imagen';
        
        downloadBtn.addEventListener('click', (e) => {
          e.stopPropagation();
          const a = document.createElement('a');
          a.href = url;
          a.download = url.split('/').pop() || 'imagen';
          document.body.appendChild(a);
          a.click();
          document.body.removeChild(a);
        });
        
        // Show/hide download button on hover
        imgContainer.addEventListener('mouseenter', () => {
          downloadBtn.style.display = 'flex';
        });
        imgContainer.addEventListener('mouseleave', () => {
          downloadBtn.style.display = 'none';
        });
        
        imgContainer.appendChild(img);
        imgContainer.appendChild(downloadBtn);
        container.appendChild(imgContainer);
      } else if (isAudio) {
        const audio = document.createElement('audio');
        audio.controls = true;
        audio.src = url;
        audio.style.width = '100%';
        audio.style.height = '40px';
        audio.style.display = 'block';
        audio.setAttribute('preload', 'metadata');
        container.appendChild(audio);
      } else if (isVideo) {
        const video = document.createElement('video');
        video.controls = true;
        video.src = url;
        video.style.maxWidth = '100%';
        video.style.borderRadius = '12px';
        container.appendChild(video);
      } else {
        const a = document.createElement('a');
        a.href = url;
        a.target = '_blank';
        a.textContent = 'Abrir archivo';
        container.appendChild(a);
      }
    }

    // Reply functionality
    function setReplyTo(message) {
      replyingTo = message;
      showReplyPreview();
      refs.input.focus();
    }
    
    function showReplyPreview() {
      if (!replyingTo) return;
      refs.replyAuthor.textContent = replyingTo.sender_type === 'user' ? 'Usuario' : 'Responsable';
      refs.replyText.textContent = replyingTo.content || '';
      refs.replyContainer.style.display = 'block';
    }
    
    function cancelReply() {
      replyingTo = null;
      refs.replyContainer.style.display = 'none';
    }
    
    // Auto-scroll function
    function scrollToBottom() {
      refs.body.scrollTop = refs.body.scrollHeight;
    }

    function escapeHtml(s){ return (s||'').replace(/[&<>\"]/g, c=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;"}[c])); }

    // Opciones - verificar que los elementos existen antes de agregar listeners
    if (refs.optBtn) {
      refs.optBtn.addEventListener('click', ()=>{
        refs.optMenu.classList.toggle('open');
      });
    }
    
    document.addEventListener('click', (e)=>{
      if(refs.optMenu && !refs.optMenu.contains(e.target) && e.target!==refs.optBtn){ 
        refs.optMenu.classList.remove('open'); 
      }
    });
    
    if (refs.optExpand) {
      refs.optExpand.addEventListener('click', ()=>{
        document.querySelector('.resp-chat').classList.toggle('fullscreen');
      });
    }
    
    if (refs.optTranscript) {
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
    }

    if (refs.send) refs.send.addEventListener('click', sendMessage);
    if (refs.input) refs.input.addEventListener('keydown', e=>{ if(e.key==='Enter') sendMessage(); });
    if (refs.refresh) refs.refresh.addEventListener('click', fetchThreads);
    if (refs.search) refs.search.addEventListener('input', ()=>{ clearTimeout(window.__throttle); window.__throttle=setTimeout(fetchThreads, 250); });

    // Reply functionality
    if (refs.cancelReply) refs.cancelReply.addEventListener('click', cancelReply);

    // Emoji functionality
    const emojiList = '😀 😁 😂 🤣 😊 😇 🙂 🙃 😉 😍 😘 😗 😙 😚 🥰 😋 😛 😜 🤪 😝 🤗 🤭 🤫 🤔 🤐 🤨 😐 😑 😶 🙄 😏 😣 😥 😮 😯 😪 😫 🥱 😴 😌 🥳 🤓 😎 🤩 🥺 😤 😢 😭 😱 😳 🤯 😬 😰 🤥 🤧 🤒 🤕 🤑 🤠 🤡 👋 👍 👎 🙏 💪 ✌️ 👀 ❤️ 🧡 💛 💚 💙 💜 🤎 🖤 🤍 💔 ⭐ 🌟 🔥 🎉 🎂 🎁 🍻 ☕ 🍔 🍕 🍟 🍣 🍓 🍎 🥑 ⚽ 🏀 🎮 🎧'.split(' ');
    
    function initializeEmojiPanel() {
      if (refs.emojiPanel) {
        refs.emojiPanel.innerHTML = '<div class="emoji-grid">'+emojiList.map(e=>`<button class="emoji-btn" type="button">${e}</button>`).join('')+'</div>';
        refs.emojiPanel.querySelectorAll('.emoji-btn').forEach(el=>{ 
          el.addEventListener('click', ()=>{ 
            refs.input.value += el.textContent; 
            refs.input.focus();
          }); 
        });
      }
    }
    
    if (refs.btnEmoji) {
      refs.btnEmoji.addEventListener('click', ()=>{
        const isVisible = refs.emojiPanel.style.display === 'block';
        if (isVisible) {
          refs.emojiPanel.style.display = 'none';
        } else {
          initializeEmojiPanel();
          refs.emojiPanel.style.display = 'block';
        }
      });
    }
    
    // Initialize emoji panel on load
    initializeEmojiPanel();

    // Dynamic button switching (WhatsApp style)
    if (refs.input) {
      refs.input.addEventListener('input', () => {
        const hasText = refs.input.value.trim().length > 0;
        if (hasText) {
          refs.btnAudio.style.display = 'none';
          refs.sendBtn.style.display = 'block';
          if (refs.btnCamera) refs.btnCamera.style.display = 'none';
        } else {
          refs.btnAudio.style.display = 'block';
          refs.sendBtn.style.display = 'none';
          if (refs.btnCamera) refs.btnCamera.style.display = 'block';
        }
      });
    }

    // Scroll to message function
    function scrollToMessage(messageId) {
      const targetMessage = document.querySelector(`[data-message-id="${messageId}"]`);
      if (targetMessage) {
        targetMessage.scrollIntoView({ 
          behavior: 'smooth', 
          block: 'center' 
        });
        
        // Add highlight effect
        targetMessage.style.backgroundColor = 'rgba(22, 119, 255, 0.1)';
        setTimeout(() => {
          targetMessage.style.backgroundColor = '';
        }, 2000);
      }
    }

    // Image modal functions
    function openImageModal(imageUrl) {
      if (refs.modalImage && refs.imageModal) {
        refs.modalImage.src = imageUrl;
        refs.imageModal.style.display = 'flex';
        
        // Set download functionality
        refs.downloadModal.onclick = () => {
          const a = document.createElement('a');
          a.href = imageUrl;
          a.download = 'imagen.jpg';
          a.click();
        };
      }
    }

    function closeImageModal() {
      if (refs.imageModal) {
        refs.imageModal.style.display = 'none';
      }
    }

    // File upload handlers
    if (refs.btnAttach) refs.btnAttach.addEventListener('click', ()=>refs.fileInput.click());
    if (refs.btnCamera) refs.btnCamera.addEventListener('click', ()=>refs.cameraInput.click());
    if (refs.fileInput) refs.fileInput.addEventListener('change', handleFileUpload);
    if (refs.cameraInput) refs.cameraInput.addEventListener('change', handleFileUpload);
    if (refs.btnAudio) refs.btnAudio.addEventListener('click', toggleAudioRecording);
    if (refs.sendBtn) refs.sendBtn.addEventListener('click', sendMessage);
    
    // Image modal event listeners
    if (refs.closeModal) refs.closeModal.addEventListener('click', closeImageModal);
    if (refs.imageModal) {
      refs.imageModal.addEventListener('click', (e) => {
        if (e.target === refs.imageModal) closeImageModal();
      });
    }

    fetchThreads();
  }
})();
</script>
</body>
</html>