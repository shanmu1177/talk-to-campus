/* Public chat JS - LAB SAFE VERSION (Ubuntu compatible) */

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
    e = e || window.event;
    if (e.keyCode == 13) {
      sendMessage(input.value, false);
      return false;
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
    var hh = now.getHours();
    var mm = now.getMinutes();
    if (mm < 10) mm = '0' + mm;

    var ampm = hh >= 12 ? 'PM' : 'AM';
    hh = hh % 12;
    if (hh == 0) hh = 12;

    time.innerHTML = hh + ':' + mm + ' ' + ampm;

    inner.appendChild(time);
    wrapper.appendChild(inner);
    messagesEl.appendChild(wrapper);

    messagesEl.scrollTop = messagesEl.scrollHeight;
  }

  function sendMessage(content, isQuick) {

    if (!content) return;

    var userText = isQuick ? content : content.replace(/^\s+|\s+$/g, '');
    if (!userText) return;

    var displayText = isQuick
        ? ('🔘 ' + content.replace(/_/g,' '))
        : escapeHtml(userText);

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