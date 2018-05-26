<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Url Shortener</title>
    <link href="http://urlshortener.loc/main.css" rel="stylesheet">
</head>
<body>
<form method="post" id="container" action="getLink.php" class="container">
    <label class="input-field" for="original-field">Введите URI:</label>
    <input class="input-field" id="original-field" name="original-url" type="url"/>
    <div id="generate" class="input-submit" onclick="sendUri()">Сгенерировать</div>
    <div id="create-yourself" class="input-submit" onclick="toggleYourself()">Или ввести самому</div>
</form>
<p>Минифицированная ссылка:</p>
<a href="" id="result"></a>
<script>
    
    function toggleYourself () {
        console.log('hell');
        document.getElementById('generate').style.display = 'none';
        let createBtn = document.getElementById('create-yourself');

        let el = document.createElement('input'),
            label = document.createElement('label');

        label.innerText = 'Введите нужный алиас для URI: ';
        label.className='input-field';
        el.type = 'text';
        el.id = 'own-input';
        el.className = 'input-field';

        let container = document.getElementById('container');
        container.appendChild(label);
        container.appendChild(el);
        container.appendChild(createBtn);
        createBtn.innerText = 'Установить значение';
        createBtn.onclick = sendUri;
    }
    
    function sendUri() {
        let xhr = new XMLHttpRequest();

        let ownValueEl = document.getElementById('own-input');
        let ownValue =  ownValueEl ?
            ownValueEl.value : null;

        let err = document.getElementById('err') ? document.getElementById('err') : document.createElement('p');
        err.id = 'err';
        document.getElementById('container').appendChild(err);

        let body = (ownValue ? ('custom-url=' + ownValue + '&') : '')
            + 'original-url=' + document.getElementById('original-field').value;

        xhr.open('POST', 'getLink', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onreadystatechange = function() {
            if (xhr.readyState !== 4) {
                return;
            }

            if (xhr.status !== 200) {
                console.log('Error sending');
                return;
            }

            let link = document.getElementById('result');
            let result = JSON.parse(xhr.responseText);
            if (result.error) {
                err.innerText = result.error;
                err.style.display = 'block';
                link.innerText = '';
                link.href = '';
                return;
            }
            document.getElementById('err').style.display = 'none';
            link.innerText = result.short_link;
            link.href = result.short_link;
        };

        xhr.send(body);
    }
</script>
</body>
</html>

<?php exit; ?>