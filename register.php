<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/appVars.inc.php';

function isValidUsername($username, $database)
{
    $validUsernames = $database->select("validusernames", "username", ["username"=>$username]);

    return count($validUsernames) == 1;
}

function validateInput($input)
{
    $result = [];
    if(!isset($input['username']) || !isset($input['userid']))
    {
        $result['success'] = false;
        $result['errorMsg'] = 'درخواست بد';

        return $result;
    }
    $database = initDatabase();
    $matchedVisitors = $database->select('visitors', '*', ['userid'=>$input['userid']]);
    if(count($matchedVisitors) != 1)
    {
        $result['success'] = false;
        $result['errorMsg'] = 'درخواست بد';

        return $result;
    }
    if(!isValidUsername($input['username'], $database))
    {
        $result['success'] = false;
        $result['errorMsg'] = 'از نام کاربری ایمیل CE استفاده کنید';

        return $result;
    }
    $matchedUsernames = $database->select('users', '*', ['username'=>$input['username']]);
    if(count($matchedUsernames) != 0)
    {
        $result['success'] = false;
        $result['errorMsg'] = 'کاربر قبلا ثیت نام شده';

        return $result;
    }
    $result['success'] = true;
    $result['hasValidInviter'] = isset($input['inviter']);
    
    return $result;

}

function register($input)
{
    $validationResult = validateInput($input);
    if($validationResult['success'])
    {
        $userID = $input['userid'];
        $username = $input['username'];
        $database = initDatabase();
        $database->insert('users', ['username'=>$username, 'tele_id'=>$userID, 'w_limit'=>W_LIMIT]);
        herokuLog("Checking if has valid inviter");
        if($result['hasValidInviter'])
        {
            herokuLog("Yes he has :))");
            $inviter = $input['inviter'];
            $inviterWLimit = $database->select('users', 'w_limit', ['username'=>$inviter])[0] ;
            $inviterWLimit += dW;
            $database->update('users', ['w_limit'=>$inviterWLimit], ['username'=>$inviter]);
        }
    }
    return $validationResult;
}

