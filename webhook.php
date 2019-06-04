<?php
$fd = fopen("inputs.txt", "a");
fprintf($fd, "%s\n", file_get_contents('php://input'));
?>