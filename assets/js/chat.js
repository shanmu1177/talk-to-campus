/* Public chat JS - assets/js/chat.js
   Works with chat.php and api/send_message.php (POST q=... & is_quick=0/1)
   Vanilla JS, compatible with older browsers. */

(function(){
  var messagesEl = document.getElementById('messages');
  var input = document.getElementById('msgInput');
  var sendBtn = document.getElementById('sendBtn');

  if (!messagesEl || !input || !sendBtn) return;

  var quickActions = {
    campus: { label: "Campus Info", payload: "campus_info" },
    academics: { label: "Academics", payload: "academics" },
    events: { label: "Events", payload: "events" },
    faqs: { label: "FAQs", payload: "faqs" }
  };

  var qbuttons = document.querySelectorAll('.quick button');
  for(var i=0;i<qbuttons.length;i++){
    qbuttons[i].addEventListener('click', function(e){
      var key = this.getAttribute('data-action');
      if (quickActions[key]) {
        sendMessage( quickActions[key].payload, true );
      }
    }, false);
  }

  sendBtn.addEventListener('click', function(){ sendMessage(input.value || '', false); }, false);
  input.addEventListener('keydown', function(e){ if(e.keyCode===13){ sendMessage(input.value || '', false); } }, false);

  function appendMessage(text, who) {
    var wrapper = document.createElement('div');
    wrapper.className = 'msg ' + (who === 'user' ? 'user' : 'bot');
    var inner = document.createElement('div');
    var bubble = document.createElement('div');
    bubble.className = 'bubble ' + (who === 'user' ? 'user' : 'bot');
    bubble.innerHTML = text;
    inner.appendChild(bubble);
    var time = document.createElement('div');
    time.className = 'time';
    var now = new Date();
    var hh = now.getHours();
    var mm = now.getMinutes(); if (mm < 10) mm = '0' + mm;
    var ampm = hh >= 12 ? 'PM' : 'AM';
    hh = hh % 12; if (hh === 0) hh = 12;
    time.textContent = hh + ':' + mm + ' ' + ampm;
    inner.appendChild(time);
    wrapper.appendChild(inner);
    messagesEl.appendChild(wrapper);
    messagesEl.scrollTop = messagesEl.scrollHeight;
  }

  function sendMessage(content, isQuick) {
    var userText = isQuick ? content : (content || '').trim();
    if (!userText) return;
    var displayText = isQuick ? ('🔘 ' + content.replace(/_/g,' ')) : escapeHtml(userText);
    appendMessage(displayText, 'user');

    if(!isQuick) input.value = '';

    // show typing
    var typing = document.createElement('div');
    typing.className = 'msg bot';
    typing.id = 'typing';
    typing.innerHTML = '<div><div class="bubble bot">Typing...</div></div>';
    messagesEl.appendChild(typing);
    messagesEl.scrollTop = messagesEl.scrollHeight;

    // AJAX
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'api/send_message.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function(){
      if(xhr.readyState !== 4) return;
      var t = document.getElementById('typing');
      if (t) t.parentNode.removeChild(t);
      if(xhr.status === 200){
        try {
          var res = JSON.parse(xhr.responseText);
          if(res && res.reply){
            appendMessage(res.reply, 'bot');
          } else {
            appendMessage("Sorry, something went wrong.", 'bot');
          }
        } catch(e) {
          appendMessage("Invalid server response.", 'bot');
        }
      } else {
        appendMessage("Server error. Try again later.", 'bot');
      }
    };
    var params = 'q=' + encodeURIComponent(userText) + '&is_quick=' + (isQuick?1:0);
    xhr.send(params);
  }

  function escapeHtml(text) {
    if(!text) return '';
    return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>');
  }
})();
