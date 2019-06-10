<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/appVars.inc.php';

$database = initDatabase();
$storyWords = $database->select('story', ['word_place', 'word']);
usort($storyWords, function($a, $b){return $a['word_place'] - $b['word_place'];});
$story = "";

foreach($storyWords as $word)
{
    $story .= $word['word'] . ' ';
}

$story = '"' . trim($story) . '"';

?>
<html dir = "rtl">
    <head>
        <meta charset="utf-8"/>
        <title>رایانش</title>
        <link rel="stylesheet" type="text/css" href="style.css">
        
        <script>
            var urlParams = new URLSearchParams(window.location.search);
            var story = <?php echo($story); ?>;
            var username = urlParams.get('username');
            var token = urlParams.get('token');
            var selectedWord = "wb0";

            function ajax(destination, request, responseHandle) {
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        responseHandle(this.responseText);
                    }
                };
                xhttp.open("POST", destination, true);
                xhttp.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhttp.setRequestHeader('Content-Type', 'application/json');
                xhttp.send(request);
			}

            function init()
            {
                loadTheStory();
            }

            function loadTheStory()
            {
                storyWords = story.split(" ");
                storyFrame = document.getElementById('storyFrame');
                for(var i = 0; i < storyWords.length; i++)
                {
                    wordBox = document.createElement('span');
                    wordBox.setAttribute('class', 'wordBox');
                    wordBox.setAttribute('id', 'wb'+i);
                    wordBox.setAttribute('onclick', 'wordSelected("wb'+i+'")');
                    wordBox.innerText = storyWords[i];
                    storyFrame.appendChild(wordBox);
                }
            }

            function wordSelected(wordID)
            {
                previousWord = document.getElementById(selectedWord);
                word = document.getElementById(wordID);
                previousWord.setAttribute('class', 'wordBox');
                word.setAttribute('class', 'wordBox selected');
                selectedWord = wordID;
                document.getElementById('txtWordChanger').value = word.innerText;
            }

            function changeWord()
            {
                word = document.getElementById(selectedWord);
                alternative = document.getElementById('txtWordChanger').value;
                changeRequest = {username: username,
                                token: token,
                                wp: selectedWord.substring(2), 
                                alternative: nuetralized(alternative)};
                displayMessage("یه لحظه وایسا به بچه‌های سرور بگم...", false);
                ajax("change.php", JSON.stringify(changeRequest), verifyChange);
            }

            function nuetralized(input)
            {
                return input.trim();
                //TODO: this bro
            }

            function verifyChange(response)
            {
                response = JSON.parse(response);
                if(response['success'])
                {
                    word = document.getElementById(selectedWord);
                    alternative = document.getElementById('txtWordChanger').value;
                    word.innerText = alternative;
                    displayMessage("عوض شد :)", true);
                }
                else
                {
                    displayMessage(response['errorMsg'], true);
                }
            }

            function displayMessage(msg, autoVanish)
            {
                //TODO: replace alerts with better buddies
                document.getElementById('messageBox').innerText = msg;
                displayModal(true);
                if(autoVanish)
                {
                    setTimeout(function(){
                        displayModal(false);
                    }, 2000);
                }
            }

            function displayModal(visible)
            {
                var modal = document.getElementById("msgModal");
                modal.style.display = visible ? "block" : "none";
            }
        </script>
    </head>
    <body onload = "init()">
        <div id="background">
            <img src="bg.jpg" class="stretch" alt="" />
        </div>
        <div id = "whole">
            <div id = "storyFrame"></div>
            <div id = "controls">
                <input type = "text" id = "txtWordChanger">
                <input type = "button" value = "تغییرش بده" id = "btnChanger" onclick = "changeWord()">
            </div>
            <div id = "credit">Photo by Annie Spratt on Unsplash</div>
        </div>
        <!-- Modal adopted of https://www.w3schools.com/howto/howto_css_modals.asp -->
        <div id="msgModal" class="modal">

            <!-- Modal content -->
            <div class="modal-content">
                <div id = "messageBox"></div>
            </div>
        </div>
    </body>
</html>