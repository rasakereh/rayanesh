<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/appVars.inc.php';

use Formapro\TelegramBot\AnswerCallbackQuery;
use Formapro\TelegramBot\Bot;
use Formapro\TelegramBot\Update;

function getRegisterationInfo($update)
{
    $registerationInfo = [];
    $callbackQuery = $update->getCallbackQuery();
    if(is_null($callbackQuery))
    {
        $registerationInfo['valid'] = false;
        return $registerationInfo;
    }
    $incomingGameName = $callbackQuery->getGameShortName();
    if(is_null($incomingGameName) || $incomingGameName != $gameName)
    {
        $registerationInfo['valid'] = false;
        return $registerationInfo;
    }
    $sender = $callbackQuery->getFrom();
    if(is_null($sender))
    {
        $registerationInfo['valid'] = false;
        return $registerationInfo;
    }
    $database = initDatabase();
    $registerationInfo['valid'] = true;
    $registerationInfo['userid'] = $sender->getID();
    $matchedVisitors = $database->select('Visitors', '*', ['userid'=>$registerationInfo['userid']]);
    if(count($matchedVisitors) == 0)
    {
        $registerationInfo['registered'] = false;
        $registerationInfo['fullname'] = ($sender->getFirstName() ?? "") . " " . ($sender->getLastName() ?? "");
        if(strlen($registerationInfo['fullname']) == 1)
            $registerationInfo['fullname'] = "NO_NAME";
        
        registerVisitor($registerationInfo);
        $registerationInfo['inviter'];

        return $registerationInfo;
    }
    $matchedUsernames = $database->select('Users', 'username', ['tele_id'=>$registerationInfo['userid']]);
    if(count($matchedUsernames) != 1)
    {
        $registerationInfo['registered'] = false;
        $registerationInfo['inviter'];
        
        return $registerationInfo;
    }
    $registerationInfo['username'] = $matchedUsernames[0];
    $registerationInfo['token'] = tokenFromUsername($registerationInfo['username']);
    
    return $registerationInfo;
}

function sendGame($registerationInfo, $callbackQuery)
{
    $queryString = "?username=".$registerationInfo['username']."&token=".$registerationInfo['token'];
    $answer = new AnswerCallbackQuery($callbackQuery->getId());
    $answer->setUrl($gameURL.$queryString);
    $res = $bot->answerCallbackQuery($answer);
    //herokuLog($res);
}

function sendSignupForm($registerationInfo, $callbackQuery)
{
    $queryString = "?userid=".$registerationInfo['userid'];
    if(isset($registerationInfo['inviter']))
        $queryString .= "&inviter=".$registerationInfo['inviter'];
    $answer = new AnswerCallbackQuery($callbackQuery->getId());
    $answer->setUrl($signupURL.$queryString);
    $res = $bot->answerCallbackQuery($answer);
    //herokuLog($res);
}

function main()
{
    global $botToken, $gameURL;
    $input = file_get_contents('php://input');
    logInput($input);
    
    $bot = new Bot($botToken);

    $data = json_decode($input, true);

    $update = Update::create($data);

    $registerationInfo = getRegisterationInfo($update);
    if(!$registerationInfo['valid'])    return;
    if($registerationInfo['registered'])
    {
        sendGame($registerationInfo, $update);
    }
    else
    {
        sendSignupForm($registerationInfo, $update);
    }
}

main();
