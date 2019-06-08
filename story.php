<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/appVars.inc.php';

$database = initDatabase();
$storyWords = $database->select('story', ['word_place', 'word']);
usort($storyWords, function($a, $b){return $a['word_place'] - $b['word_place'];});
$story = "";

foreach($storyWords as $word)
{
    $story .= $word . ' ';
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
            var inviteLink = <?php echo($gameTgURL); ?> + "&inviter=" + username;
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
                document.getElementById('inviteSpan').innerText = inviteLink;
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
                alternative = document.getElementById('txtWordChanger').value
                word.innerText = alternative;
                changeRequest = {username: username,
                                token: token,
                                wp: selectedWord, 
                                alternative: nuetralized(alternative)};
                ajax("change.php", JSON.stringify(changeRequest), verifyChange);
            }

            function nuetralized(input)
            {
                return input;
                //TODO: this bro
            }

            funciton verifyChange(response)
            {
                response = JSON.parse(response);
                if(response['success'])
                {
                    displayMessage("عوض شد :)");
                }
                else
                {
                    displayMessage(response['errorMsg']);
                }
            }

            function copyLink()
            {
                document.getElementById('inviteSpan').select();
                document.execCommand("copy");
                displayMessage("کپی شد");
            }

            function displayMessage(msg)
            {
                //TODO: replace alerts with better buddies
                alert(msg);
            }
        </script>
    </head>
    <body onload = "init()">
        <div id = "whole">
            <div id = "storyFrame"></div>
            <div id = "controls">
                <input type = "text" id = "txtWordChanger">
                <input type = "button" value = "تغییرش بده" id = "btnChanger" onclick = "changeWord()">
            </div>

            <div>
                با این لینک ملتو دعوت کن به بازی تا محدودیت کلماتت کمتر شه:
                <span id = "inviteSpan"></span> <input type = "button" value = "کپی لینک" onclick = "copyLink()">
            </div>
            <div id = "credit">Photo by Kelli Tungay on Unsplash</div>
        </div>
    </body>
</html>