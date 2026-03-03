<?php
session_start();

$intro_text = "Hello! I am Talk To Campus Bot. How can I assist you today?";
$no_result_text = "I could not find an answer for that. I will ask admin to check.";
$site_title = "Talk To Campus";

$base = __DIR__ . '/includes';

if (file_exists($base . '/config.php')) {
    include_once $base . '/config.php';
}

if (file_exists($base . '/db.php')) {
    include_once $base . '/db.php';
}

if (isset($mysqli)) {

    $sql = "SELECT site_title, intro_msg, no_result_msg FROM system_info LIMIT 1";
    $result = mysqli_query($mysqli, $sql);

    if ($result && mysqli_num_rows($result) > 0) {

        $row = mysqli_fetch_assoc($result);

        if (!empty($row['intro_msg'])) {
            $intro_text = $row['intro_msg'];
        }

        if (!empty($row['no_result_msg'])) {
            $no_result_text = $row['no_result_msg'];
        }

        if (!empty($row['site_title'])) {
            $site_title = $row['site_title'];
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?php echo htmlspecialchars($site_title); ?> — Chat</title>
  <style>
    /* Chat UI styles (simple, responsive) */
    :root {
      --purple-1:#7b4df4; --purple-2:#d94fd0; --bg:#f6f8fb; --muted:#9aa3b2;
      --bubble-user:#6b46ff; --bubble-bot:#ffffff;
    }
    body { margin:0; font-family: "Segoe UI", Roboto, Arial, sans-serif; background:var(--bg); color:#222; }
    .topbar { background: linear-gradient(90deg,var(--purple-1),var(--purple-2)); color:#fff; padding:18px 22px; display:flex;
       align-items:center; justify-content:space-between; box-shadow:0 6px 18px rgba(20,10,40,0.08); }
    .brand { display:flex; align-items:center; }
    .brand .icon { width:44px;height:44px;border-radius:10px;background:#fff3; display:flex;align-items:center;
      justify-content:center;margin-right:12px;font-weight:bold; }
    .brand h2 { margin:0;font-size:18px; }
    .container { max-width:980px;margin:22px auto;padding:0 14px; }
    .chat-wrap { background:#fff;border-radius:14px; box-shadow:0 12px 30px rgba(10,10,30,0.06); overflow:hidden; display:flex;
       flex-direction:column; height:74vh; min-height:520px; }
    .chat-header { padding:18px;border-bottom:1px solid #f1f3f7;
       background:linear-gradient(90deg, rgba(255,255,255,0.02), rgba(255,255,255,0)); display:flex; align-items:center; }
    .chat-header .bot { display:flex; align-items:center; }
    .avatar { width:44px;height:44px;border-radius:10px;background:linear-gradient(90deg,var(--purple-1),var(--purple-2));
       color:#fff;display:flex;align-items:center;justify-content:center;margin-right:12px; }
    .quick { margin-left:20px; display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
    .quick button { background:#f6f8fb;border:0;padding:10px 14px;border-radius:18px; box-shadow:0 6px 14px rgba(40,40,90,0.04);
       cursor:pointer; font-weight:600; }
    .messages { flex:1; padding:22px; overflow:auto; background: linear-gradient(180deg,#fbfdff,#ffffff); }
    .msg { display:flex; margin-bottom:18px; align-items:flex-end; }
    .msg.bot { justify-content:flex-start; }
    .msg.user { justify-content:flex-end; }
    .bubble { max-width:70%; padding:12px 16px; border-radius:14px; box-shadow:0 6px 16px rgba(20,10,40,0.04); }
    .bubble.bot { background:var(--bubble-bot); color:#222; border-radius:14px 14px 14px 4px; }
    .bubble.user { background:var(--bubble-user); color:#fff; border-radius:14px 14px 4px 14px; }
    .time { font-size:11px; color:var(--muted); margin-top:6px; }
    .composer { padding:14px; border-top:1px solid #f1f3f7; display:flex; align-items:center; gap:10px; background:#fff; }
    .composer input { flex:1; padding:12px 14px; border-radius:28px; border:1px solid #eef2f7; outline:none; }
    .send { width:44px; height:44px; border-radius:50%; background:linear-gradient(90deg,var(--purple-1),var(--purple-2)); 
      border:0; color:#fff; cursor:pointer; }
    .option-list { background:#fbfdff; padding:12px; border-radius:12px; box-shadow:0 8px 20px rgba(10,10,30,0.04); }
    .option-item { padding:12px 14px; background:#fff; border-radius:10px; margin-bottom:8px; cursor:pointer;
       border:1px solid #f0f4fb; font-weight:600; }
    @media (max-width:720px){ .chat-wrap{height:80vh;} .bubble{max-width:86%} .quick{margin-left:10px} }
  </style>
</head>
<body>

  <div class="topbar">
    <div class="brand">
      <div class="icon">💬</div>
      <div>
        <h2><?php echo htmlspecialchars($site_title); ?></h2>
        <div style="font-size:12px;opacity:0.9">Your Smart Campus Assistant</div>
      </div>
    </div>
    <div>
      <a style="color:#fff;text-decoration:none;font-weight:600;padding:8px 12px;
      background:rgba(255,255,255,0.12);border-radius:8px" href="/TALK-TO-CAMPUS/">Back to Home</a>
    </div>
  </div>

  <div class="container">
    <div class="chat-wrap">
      <div class="chat-header">
        <div class="bot">
          <div class="avatar">🤖</div>
          <div>
            <div style="font-weight:700">Campus Bot</div>
            <div style="font-size:12px;color:var(--muted)">Let's Talk</div>
          </div>
        </div>
        <div class="quick" style="margin-left:auto;">
          <button data-action="campus">Campus Info</button>
          <button data-action="academics">Academics</button>
          <button data-action="events">Events</button>
          <button data-action="faqs">FAQs</button>
        </div>
      </div>

      <div id="messages" class="messages">
        <!-- initial intro message -->
        <div class="msg bot">
          <div>
            <div class="bubble bot"><?php echo nl2br(htmlspecialchars($intro_text)); ?></div>
            <div class="time"><?php echo date('h:i A'); ?></div>
          </div>
        </div>
      </div>

      <div class="composer">
        <input id="msgInput" placeholder="Type your message here..." autocomplete="off" />
        <button id="sendBtn" class="send">➤</button>
      </div>
    </div>
  </div>

<script>
(function(){

  var messagesEl = document.getElementById('messages');
  var input = document.getElementById('msgInput');
  var sendBtn = document.getElementById('sendBtn');

  if (!messagesEl || !input || !sendBtn) return;

  var quickActions = {
    campus: "campus_info",
    academics: "academics",
    events: "events",
    faqs: "faqs"
  };

  /* QUICK BUTTONS */
  var qbuttons = document.querySelectorAll('.quick button');
  for (var i = 0; i < qbuttons.length; i++) {
    qbuttons[i].onclick = function() {
      var key = this.getAttribute('data-action');
      if (quickActions[key]) {
        sendMessage(quickActions[key], true);
      }
    };
  }

  /* SEND BUTTON */
  sendBtn.onclick = function() {
    sendMessage(input.value, false);
  };

  /* ENTER KEY */
  input.onkeydown = function(e) {
    if (e.keyCode == 13) {
      sendMessage(input.value, false);
    }
  };

  function appendMessage(text, who) {

    var wrapper = document.createElement('div');
    wrapper.className = 'msg ' + (who == 'user' ? 'user' : 'bot');

    var inner = document.createElement('div');
    var bubble = document.createElement('div');

    bubble.className = 'bubble ' + (who == 'user' ? 'user' : 'bot');
    bubble.innerHTML = text;

    inner.appendChild(bubble);

    var time = document.createElement('div');
    time.className = 'time';

    var now = new Date();
    time.innerHTML = now.getHours() + ":" + 
                     ("0" + now.getMinutes()).slice(-2);

    inner.appendChild(time);
    wrapper.appendChild(inner);
    messagesEl.appendChild(wrapper);

    messagesEl.scrollTop = messagesEl.scrollHeight;
  }

  function sendMessage(content, isQuick) {

    if (!content) return;

    var userText = isQuick ? content : content.replace(/^\s+|\s+$/g, '');
    if (!userText) return;

    var icon = '<img src="images/btn.jpeg" width="16" style="vertical-align:middle;margin-right:5px;">';

    var displayText = isQuick ? 
        (icon + content.replace(/_/g,' ')) : 
        escapeHtml(userText);

    appendMessage(displayText, 'user');

    if (!isQuick) input.value = '';

    /* Typing indicator */
    var typing = document.createElement('div');
    typing.className = 'msg bot';
    typing.id = 'typing';
    typing.innerHTML = '<div><div class="bubble bot">Typing...</div></div>';
    messagesEl.appendChild(typing);
    messagesEl.scrollTop = messagesEl.scrollHeight;

    var xhr = new XMLHttpRequest();

    /* IMPORTANT: safer relative path */
    xhr.open('POST', './api/send_message.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onreadystatechange = function() {

      if (xhr.readyState != 4) return;

      var t = document.getElementById('typing');
      if (t) t.parentNode.removeChild(t);

      if (xhr.status == 200) {

        try {
          var res = JSON.parse(xhr.responseText);

          if (res && res.reply) {
            appendMessage(res.reply, 'bot');
          } else {
            appendMessage("Sorry, something went wrong.", 'bot');
          }

        } catch (e) {
          appendMessage("Invalid server response.", 'bot');
        }

      } else {
        appendMessage("Server error. Try again later.", 'bot');
      }
    };

    var params = "q=" + encodeURIComponent(userText) +
                 "&is_quick=" + (isQuick ? 1 : 0);

    xhr.send(params);
  }

  function escapeHtml(text) {
    if (!text) return '';
    return text.replace(/&/g, "&amp;")
               .replace(/</g, "&lt;")
               .replace(/>/g, "&gt;")
               .replace(/\n/g, "<br>");
  }

})();
</script>

</body>
</html>
