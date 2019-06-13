<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/appVars.inc.php';
?>
<html dir = "rtl">
    <head>
        <meta charset="utf-8"/>
        <title>رایانش</title>
        <style>
            #wrapper
            {
                max-width: 70%;
            }
        </style>
        <link rel="stylesheet" type="text/css" href="style.css">
        <link rel="stylesheet" href="FlipClock/compiled/flipclock.css">
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
		<script src="FlipClock/compiled/flipclock.js"></script>
        
        <script>
            var delay = <?php echo(DELAY); ?>;
            var release = <?php echo(RELEASE_DATE); ?>;
            var now = <?php echo(time()); ?>;

            var clock;
		
            $(document).ready(function() {
                var clock;

                clock = $('.clock').FlipClock({
                    clockFace: 'DailyCounter',
                    autoStart: true,
                    callbacks: {
                        stop: function() {}
                    }
                });
                        
                clock.setTime(delay+release - now);
                clock.setCountdown(true);
                clock.start();

            });
        </script>
    </head>
    <body onload = "init()">
        <div id="background">
            <img src="bg.jpg" class="stretch" alt="" />
        </div>
        <div id = "wrapper"><div class="clock" style="margin:2em;"></div></div>
    </body>
</html>