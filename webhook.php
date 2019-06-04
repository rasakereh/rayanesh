<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . 'appVars.inc.php';

function storeInClass($className, $srcArray)
{
    try
    {
        $result = new $className();
        foreach($srcArray as $key=>$value)
        {
            $result->$key = $value;
        }

        return $result;
    }
}

function logInput()
{
    $fd = fopen("inputs.txt", "a");
    fprintf($fd, "%s\n", file_get_contents('php://input'));
}

function main()
{
    logInput();
    use Formapro\TelegramBot\AnswerCallbackQuery;
    use Formapro\TelegramBot\Bot;
    use Formapro\TelegramBot\Update;
    
    $bot = new Bot($botToken);

    $data = json_decode(file_get_contents('php://input'), true);

    $update = Update::create($data);

    if ($callbackQuery = $update->getCallbackQuery())
    {
        $answer = new AnswerCallbackQuery($callbackQuery->getId());
        $answer->setUrl($gameURL);
        $bot->answerCallbackQuery($answer);
    }
}

main();

?>