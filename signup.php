<html dir = "rtl">
    <head>
        <meta charset="utf-8"/>
        <title>رایانش</title>
        <link rel="stylesheet" type="text/css" href="style.css">
        
        <script>
            var urlParams = new URLSearchParams(window.location.search);
            var userid = urlParams.get('userid');

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

            function register()
            {
                var username = document.getElementbyId('btnRegister').value;
                registerRequest = {userid: userid, username: username};
                ajax("register.php", JSON.stringify(registerRequest), verifyRegisteration);
            }

            funciton verifyRegisteration(response)
            {
                //TODO: replace alerts with better buddies
                response = JSON.parse(response);
                if(response['success'])
                {
                    alert("بزن بریم :)");
                    document.location = "redirect.php?userid=" + userid;
                }
                else
                {
                    alert(response['errorMsg']);
                }
            }
        </script>
    </head>
    <body>
        <div id = "whole">
            <div id = "controls">
                <div>ثبت نام</div>
                <input type = "text" id = "username">
                <input type = "button" value = "بزن بریم!" id = "btnRegister" onclick = "register()">
                <div>برای ثبت نام از نام کاربری و گذرواژه ایمیل ce.sharif.edu@ات استفاده کن.
نام کاربری اون چیزیه که قبل @ تو میل سی‌ای میاد. </div>
            </div>

            <div id = "credit">Photo by Kelli Tungay on Unsplash</div>
        </div>
    </body>
</html>