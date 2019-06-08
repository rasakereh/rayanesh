<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/appVars.inc.php';
require_once __DIR__ . '/register.php';

use GuzzleHttp\ClientInterface;
use Formapro\TelegramBot\AnswerCallbackQuery;
use Formapro\TelegramBot\Bot;
use Formapro\TelegramBot\Update;
use Formapro\TelegramBot\SendMessage;

class COMMAND
{
    const START = 0;
    const MESSAGE = 1;
    const CALLBACK = 2;
};

function sendGameRules($chatid)
{
    $message = "قوانین بازی:
    🆔 میتونید با ثبت آیدی CEتون تو بات، بازی رو شروع کنید!(این همون آی‌دیه که باهاش ایمیل ce رو ساختید)
    🕑 تو هر t دقیقه فقط میتونید w تا کلمه رو عوض کنید
    🎁 میتونید به ازای معرفی هر نفر به بازی dw تا کلمه بیشتر تو این مدت زمان تغییر بدین
    🔑 مسابقه d روز بعد شروع میشه و کسی که تو این مدت معرف تعداد بیشتری باشه، k% متن اولیه رو خودش مشخص میکنه
    
    پس دست بجنبونید!";
    sendTextMessage($chatid, $message);
}

function getRegisterationInfo($userid, $database=NULL)
{
    herokuLog(__FUNCTION__);
    //herokuLog(func_get_args());
    $registerationInfo = [];
    $database = $database ?? initDatabase();
    $matchedUsernames = $database->select('Users', 'username', ['tele_id'=>$userid]);
    if(count($matchedUsernames) != 1)
    {
        $registerationInfo['registered'] = false;
        
        return $registerationInfo;
    }
    $registerationInfo['registered'] = true;
    $registerationInfo['username'] = $matchedUsernames[0];
    $registerationInfo['token'] = tokenFromUsername($registerationInfo['username']);
    
    return $registerationInfo;
}

function sendGame($registerationInfo, $callbackQuery)
{
    herokuLog(__FUNCTION__);
    //herokuLog(func_get_args());
    $queryString = "?username=".$registerationInfo['username']."&token=".$registerationInfo['token'];
    $answer = new AnswerCallbackQuery($callbackQuery->getId());
    $answer->setUrl($gameURL.$queryString);
    $res = $bot->answerCallbackQuery($answer);
    //herokuLog($res);
}

function sendSignupForm($chatid)
{
    herokuLog(__FUNCTION__);
    //herokuLog(func_get_args());
    $messageText = "برای شروع نام کاربریتو وارد کن.\n برای این‌کار از نام کاربری ایمیل ce.sharif.edu@ات استفاده کن.
    نام کاربری اون چیزیه که قبل @ تو میل سی‌ای میاد.";
    sendTextMessage($chatid, $messageText);
}

function sendTextMessage($chatid, $messageText)
{
    herokuLog(__FUNCTION__);
    //herokuLog(func_get_args());
    $message = new SendMessage($chatid, $messageText);
    $bot = new Bot(BOT_TOKEN);
    $bot->sendMessage($message);
}

function sendGameMessage($chatid)
{
    herokuLog(__FUNCTION__);
    //herokuLog(func_get_args());
    $httpClient = new \GuzzleHttp\Client();
    $bot = new Bot(BOT_TOKEN);
    $keyboard = [
        [
            ["text"=>"Play the god damn game!", "callback_game"=>["game_short_name"=>GAME_NAME]]
        ],
        [
            ["text"=>"Invite the god damn friends!", "callback_data"=>"Invite"]
        ]
    ];
    $gameMessage = [
        "chat_id"=>$chatid,
        "game_short_name"=>GAME_NAME,
        "reply_markup"=>$keyboard
    ];
    $response = $httpClient->post($bot->getMethodUrl('sendGame'), [
        'json' => get_values($gameMessage),
    ]);
}

function getRequestType($update)
{
    herokuLog(__FUNCTION__);
    //herokuLog(func_get_args());
    $result = ['valid'=>false];
    $database = initDatabase();
    if(count($database->select('Updates', '*', ['update_id'=>$update->getUpdateId()])))
        return $result;
    
    $database->insert('Updates', ['update_id'=>$update->getUpdateId()]);
    
    $callbackQuery = $update->getCallbackQuery();
    $message = $update->getMessage();
    if(!is_null($callbackQuery))
    {
        $result['type'] = COMMAND::CALLBACK;
        $result['valid'] = true;

        return $result;
    }
    if(!is_null($message))
    {
        $msgText = $message->getText();
        if(is_null($msgText))
        {
            return $result;
        }
        if(strncmp($msgText, "/start", strlen("/start")) == 0)
        {
            $result['type'] = COMMAND::START;
            $result['valid'] = true;
            
            return $result;
        }
        $result['type'] = COMMAND::MESSAGE;
        $result['valid'] = true;

        return $result;
    }

    return $result;
    
}

function startRecieved($update)
{
    herokuLog(__FUNCTION__);
    //herokuLog(func_get_args());
    $message = $update->getMessage();
    $msgText = $message->getText();
    $sender = $message->getFrom();
    $database = initDatabase();
    $matchedVisitors = $database->select('Visitors', '*', ['userid'=>$sender->getID()]);
    if(count($matchedVisitors) == 0)
    {
        // There is a new visitor:
        $fullname = ($sender->getFirstName() ?? "") . " " . ($sender->getLastName() ?? "");
        if(strlen($fullname) == 1)
            $fullname = "NO_NAME";
        
        $database->insert("Visitors", ["userid"=>$sender->getID(),
                                        "fullname"=>$fullname,
                                        "chatid"=>$message->getChat()->getID()]);
        if(strlen($msgText) > strlen("/start "))
        {
            // There is an inviter:
            sscanf($msgText, "/start %s", $inviter);
            $matchedInviters = $database->select('Users', 'username', ['username'=>$inviter]);
            if(count($matchedInviters) == 1)
            {
                $database->insert("Invitations", ["inviter"=>$inviter, "invitee"=>$sender->getID()]);
            }
        }
    }
    sendGameRules($message->getChat()->getID());
    $registerationInfo = getRegisterationInfo($sender->getID(), $database);
    
    if($registerationInfo['registered'])
    {
        sendGameMessage($message->getChat()->getID());
    }
    else
    {
        sendSignupForm($message->getChat()->getID());
    }
}

function messageRecieved($update)
{
    herokuLog(__FUNCTION__);
    //herokuLog(func_get_args());
    $database = initDatabase();
    $message = $update->getMessage();
    $msgText = $message->getText();
    $sender = $message->getFrom();
    $registerationInfo = getRegisterationInfo($sender->getID(), $database);
    if(!$registerationInfo['registered'])
    {
        $input = ['username'=>$msgText, 'userid'=>$sender->getID()];
        $inviters = $database->select('Invitations', "inviter", ['invitee'=>$sender->getID()]);
        if(count($inviters) == 1)
            $input['inviter'] = $inviters[0];
        $registerResult = register($input);
        if($registerResult['success'])
        {
            sendTextMessage($message->getChat()->getID(), "خوش اومدی رفیق!");
            sendGameMessage($message->getChat()->getID());
        }
        else
        {
            sendTextMessage($message->getChat()->getID(), $registerResult['errorMsg']);
        }
    }
}

function main()
{
    herokuLog(__FUNCTION__);
    //herokuLog(func_get_args());
    $input = file_get_contents('php://input');
    
    $data = json_decode($input, true);

    $update = Update::create($data);

    //sendTextMessage($update->getMessage()->getChat()->getID(), "گند زدم فدا سرم");
    $requestType = getRequestType($update);
    if(!$requestType['valid'])
        return;
    
    switch($requestType['type'])
    {
        case COMMAND::START:
            startRecieved($update);
            break;
        case COMMAND::MESSAGE:
            messageRecieved($update);
            break;
        case COMMAND::CALLBACK:
            callbackRecieved($update);
            break;
    }
}

main();
