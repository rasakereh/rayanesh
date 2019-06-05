<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/appVars.inc.php';

use Formapro\TelegramBot\AnswerCallbackQuery;
use Formapro\TelegramBot\Bot;
use Formapro\TelegramBot\Update;

function logInput($input)
{
    $fd = fopen("inputs.txt", "a");
    fprintf($fd, "%s\n", $input);
}

function herokuLog($msg)
{
    error_log(var_export($msg, true));
}

function main()
{
    global $botToken, $gameURL;
    $input = file_get_contents('php://input');
    logInput($input);
    
    $bot = new Bot($botToken);

    $data = json_decode($input, true);

    $update = Update::create($data);

    if ($callbackQuery = $update->getCallbackQuery())
    {
        $answer = new AnswerCallbackQuery($callbackQuery->getId());
        $answer->setUrl($gameURL);
        $res = $bot->answerCallbackQuery($answer);
        herokuLog($res);
    }
}

main();

?>