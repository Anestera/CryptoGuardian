function openTab(evt, tabName) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tab-content");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("tab-link");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.className += " active";
}

function uploadFile() {
    var fileInput = document.getElementById('profile-picture');
    var file = fileInput.files[0];
    
    if (!file) {
        alert('Please select a file to upload.');
        return;
    }
    
    var formData = new FormData();
    formData.append('profile-picture', file);
    
    // Здесь можно использовать fetch или XMLHttpRequest для отправки formData на сервер
    // Пример с использованием fetch:
    fetch('upload.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        // Действия после успешной загрузки (если требуется)
        console.log('Upload successful:', data);
    })
    .catch(error => {
        console.error('Upload error:', error);
    });
}