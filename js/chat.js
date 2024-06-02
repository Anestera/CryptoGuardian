document.addEventListener("DOMContentLoaded", function() {
    loadMessages();

    document.getElementById("messageForm").addEventListener("submit", function(event) {
        event.preventDefault();
        console.log("Form submitted"); // Добавлено для проверки отправки формы
        sendMessage();
    });
});

function loadMessages() {
    fetch('load_messages.php')
        .then(response => response.json())
        .then(data => {
            var messagesContainer = document.getElementById('messages');
            messagesContainer.innerHTML = '';

            data.forEach(message => {
                var messageElement = document.createElement('div');
                messageElement.className = 'message';

                var senderElement = document.createElement('strong');
                senderElement.textContent = message.sender + ": ";
                messageElement.appendChild(senderElement);

                var textElement = document.createElement('span');
                textElement.textContent = message.message;
                messageElement.appendChild(textElement);

                if (message.file_id) {
                    var downloadLink = document.createElement('a');
                    downloadLink.href = 'download_file.php?file_id=' + message.file_id;
                    downloadLink.textContent = ' Download file';
                    messageElement.appendChild(downloadLink);
                }

                messagesContainer.appendChild(messageElement);
            });
        });
}

function sendMessage() {
    var formData = new FormData(document.getElementById("messageForm"));
    formData.forEach((value, key) => console.log(key + ": " + value)); // Добавлено для проверки данных формы

    fetch('send_message.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(result => {
        console.log(result);
        loadMessages();
        document.getElementById("messageForm").reset();
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
