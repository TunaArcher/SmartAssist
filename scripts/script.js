document.getElementById("send-btn").addEventListener("click", sendMessage);
document
  .getElementById("user-input")
  .addEventListener("keypress", function (e) {
    if (e.key === "Enter") {
      e.preventDefault(); // ป้องกันการ action แบบ default
      sendMessage();
    }
  });

function sendMessage() {
  const userInput = document.getElementById("user-input");
  const message = userInput.value.trim();
  if (!message) return;

  // แสดงข้อความของผู้ใช้ใน chat box
  addMessageToChatBox("user-message", message);
  userInput.value = "";

  // แสดงสถานะการกำลังประมวลผล
  showTypingIndicator();

  $("#box-typing").addClass("disabled");

  // ส่งคำถามไปยัง SmartAssist API
  fetch("api/smartassist.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ question: message }),
  })
    .then((response) => response.json())
    .then((data) => {
      removeTypingIndicator();
      addMessageToChatBox(
        "assist-message",
        data.answer || "ไม่มีคำตอบที่เหมาะสม"
      );
    })
    .catch((error) => {
      console.error("Error:", error);
      removeTypingIndicator();
      addMessageToChatBox(
        "assist-message",
        "ขออภัยค่ะ เกิดข้อผิดพลาดในการประมวลผล"
      );
    });
}

function addMessageToChatBox(className, message) {
  const chatBox = document.getElementById("chat-box");
  const messageTemplate = {
    "user-message": `
      <div class="tyn-qa-item">
          <div class="tyn-qa-avatar">
              <div class="tyn-media tyn-size-md">
                  <img src="images/avatar/1.jpg" alt="">
              </div>
          </div>
          <div class="tyn-qa-message tyn-text-block">${message}</div>
      </div>`,
    "assist-message": `
      <div class="tyn-qa-item">
          <div class="tyn-qa-avatar">
              <div class="tyn-qa-avatar-wrap">
                  <div class="tyn-media tyn-size-md">
                      <img src="https://cdn-icons-png.flaticon.com/512/11865/11865338.png" alt="">
                  </div>
                  <ul class="d-flex flex-column mt-2">
                      <li><button class="btn btn-icon btn-md btn-pill btn-transparent">${renderIcon(
                        "thumbs-up"
                      )}</button></li>
                      <li><button class="btn btn-icon btn-md btn-pill btn-transparent">${renderIcon(
                        "thumbs-down"
                      )}</button></li>
                  </ul>
              </div>
          </div>
          <div class="tyn-qa-message tyn-text-block"></div>
      </div>`,
  };

  const messageElement = document.createElement("div");
  messageElement.innerHTML = messageTemplate[className] || "";
  chatBox.appendChild(messageElement);
  scrollToBottom(chatBox);

  if (className == "assist-message") {
    var items = document.querySelectorAll(".tyn-qa-message");
    var lastchild = items[items.length - 1];
    // $(lastchild).typed({
    //   strings: [message],
    //   typeSpeed: -20,
    //   onComplete: (self) => {
    //     $("#box-typing").removeClass("disabled")
    //   }
    // });

    var typed = new Typed(lastchild, {
      strings: [message],
      typeSpeed: -20,
      onComplete: (self) => {
        console.log('done')
        $("#box-typing").removeClass("disabled")
      }
    });
  }
}

function showTypingIndicator() {
  const chatBox = document.getElementById("chat-box");
  const typingIndicator = `
    <div id="typing-indicator" class="tyn-qa-item">
        <div class="tyn-qa-avatar">
            <div class="tyn-qa-avatar-wrap">
                <div class="tyn-media tyn-size-md">
                    <img src="https://cdn-icons-png.flaticon.com/512/11865/11865338.png" alt="">
                </div>
            </div>
        </div>
        <div class="message message--typing">
            <p class="message__item">
                <span class="message__dot"></span>
                <span class="message__dot"></span>
                <span class="message__dot"></span>
            </p>
        </div>
    </div>`;
  const typingElement = document.createElement("div");
  typingElement.innerHTML = typingIndicator;
  chatBox.appendChild(typingElement);
  scrollToBottom(chatBox);
}

function removeTypingIndicator() {
  const typingElement = document.getElementById("typing-indicator");
  if (typingElement) typingElement.remove();
}

function renderIcon(type) {
  const icons = {
    "thumbs-up": `
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-hand-thumbs-up-fill" viewBox="0 0 16 16">
        <path d="M6.956 1.745C7.021.81 7.908.087 8.864.325l.261.066c.463.116.874.456 1.012.965.22.816.533 2.511.062 4.51a10 10 0 0 1 .443-.051c.713-.065 1.669-.072 2.516.21.518.173.994.681 1.2 1.273.184.532.16 1.162-.234 1.733q.086.18.138.363c.077.27.113.567.113.856s-.036.586-.113.856c-.039.135-.09.273-.16.404.169.387.107.819-.003 1.148a3.2 3.2 0 0 1-.488.901c.054.152.076.312.076.465 0 .305-.089.625-.253.912C13.1 15.522 12.437 16 11.5 16H8c-.605 0-1.07-.081-1.466-.218a4.8 4.8 0 0 1-.97-.484l-.048-.03c-.504-.307-.999-.609-2.068-.722C2.682 14.464 2 13.846 2 13V9c0-.85.685-1.432 1.357-1.615.849-.232 1.574-.787 2.132-1.41.56-.627.914-1.28 1.039-1.639.199-.575.356-1.539.428-2.59z"></path>
      </svg>`,
    "thumbs-down": `
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-hand-thumbs-down" viewBox="0 0 16 16">
        <path d="M8.864 15.674c-.956.24-1.843-.484-1.908-1.42-.072-1.05-.23-2.015-.428-2.59-.125-.36-.479-1.012-1.04-1.638-.557-.624-1.282-1.179-2.131-1.41C2.685 8.432 2 7.85 2 7V3c0-.845.682-1.464 1.448-1.546 1.07-.113 1.564-.415 2.068-.723l.048-.029c.272-.166.578-.349.97-.484C6.931.08 7.395 0 8 0h3.5c.937 0 1.599.478 1.934 1.064.164.287.254.607.254.913 0 .152-.023.312-.077.464.201.262.38.577.488.9.11.33.172.762.004 1.15.069.13.12.268.159.403.077.27.113.567.113.856s-.036.586-.113.856c-.035.12-.08.244-.138.363.394.571.418 1.2.234 1.733-.206.592-.682 1.1-1.2 1.272-.847.283-1.803.276-2.516.211a10 10 0 0 1-.443-.05 9.36 9.36 0 0 1-.062 4.51c-.138.508-.55.848-1.012.964z"></path>
      </svg>`,
  };
  return icons[type] || "";
}

function scrollToBottom(element) {
  element.scrollTop = element.scrollHeight;
}
