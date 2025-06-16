document.addEventListener("DOMContentLoaded", () => {
  const input = document.getElementById("message");
  const sendButton = document.getElementById("send");
  const chatBox = document.getElementById("chat");

  function addMessage(content, isUser) {
    const messageDiv = document.createElement("div");
    messageDiv.classList.add("message");
    messageDiv.classList.add(isUser ? "user-message" : "bot-message");
    
    if (typeof content === 'string') {
      messageDiv.textContent = content;
    } else {
      messageDiv.appendChild(content);
    }
    
    chatBox.appendChild(messageDiv);
    chatBox.scrollTop = chatBox.scrollHeight;
  }

function formatResponse(text) {
  const container = document.createElement('div');
  

  let formattedText = text

    .replace(/## (.*?)\n/g, '<h3>$1</h3>')

    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')

    .replace(/\*(.*?)\*/g, '<em>$1</em>')

    .replace(/- (.*?)\n/g, '<li>$1</li>')

    .replace(/(\d+[\+\-\*\/]\d+)/g, '<span class="math-operation">$1</span>')

    .replace(/↓/g, '<div class="math-arrow">↓</div>')

    .replace(/\n/g, '<br>');

  formattedText = formattedText.replace(/(\d{1,3}(?:,\d{3})*)/g, (match) => {
    if (match.length > 6) {
      return `<span class="math-large-number">${match}</span>`;
    }
    return match;
  });

  if (text.includes('Step')) {
    const steps = formattedText.split('<h3>');
    steps.forEach((step, index) => {
      if (index > 0) {
        const stepDiv = document.createElement('div');
        stepDiv.className = 'math-step';
        stepDiv.innerHTML = `<h3>${step}`;
        container.appendChild(stepDiv);
      }
    });
  } else if (text.includes('- ')) {
    const listItems = formattedText.split('<li>').filter(item => item);
    const ul = document.createElement('ul');
    listItems.forEach(item => {
      if (item.includes('</li>')) {
        const li = document.createElement('li');
        li.innerHTML = item.replace('</li>', '');
        ul.appendChild(li);
      }
    });
    container.appendChild(ul);
  } else {
    container.innerHTML = formattedText;
  }

  return container;
}

  function sendMessage() {
    const text = input.value.trim();
    if (text === "") return;

    addMessage(text, true);
    input.value = "";

    fetch("api.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ message: text })
    })
      .then(res => res.json())
      .then(data => {
        const formattedResponse = formatResponse(data.response);
        addMessage(formattedResponse, false);
      })
      .catch(err => {
        console.error("Fetch error:", err);
        addMessage("Error: " + err.message, false);
      });
  }

  sendButton.addEventListener("click", sendMessage);
  input.addEventListener("keypress", (e) => {
    if (e.key === "Enter") sendMessage();
  });
});