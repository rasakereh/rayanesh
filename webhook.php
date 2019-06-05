<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/appVars.inc.php';

use Formapro\TelegramBot\AnswerCallbackQuery;
use Formapro\TelegramBot\Bot;
use Formapro\TelegramBot\Update;
// TODO: It is strongly recommended to store user_id as the register form is requested to mitigate fake user_ids
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
        //TODO: Check for game_short_name
        $answer = new AnswerCallbackQuery($callbackQuery->getId());
        $answer->setUrl($gameURL);
        $res = $bot->answerCallbackQuery($answer);
        herokuLog($res);
    }
}

main();
