<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/appVars.inc.php';

function isValidUsername()
{
    //TODO: fix it
    return true;
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
    $matchedVisitors = $database->select('Visitors', '*', ['userid'=>$input['userid']]);
    if(count($matchedVisitors) != 1)
    {
        $result['success'] = false;
        $result['errorMsg'] = 'درخواست بد';

        return $result;
    }
    if(!isValidUsername($input['username']))
    {
        $result['success'] = false;
        $result['errorMsg'] = 'از نام کاربری ایمیل CE استفاده کنید';

        return $result;
    }
    $matchedUsernames = $database->select('Users', '*', ['username'=>$input['username']]);
    if(count($matchedUsernames) != 0)
    {
        $result['success'] = false;
        $result['errorMsg'] = 'کاربر قبلا ثیت نام شده';

        return $result;
    }
    $result['success'] = true;
    if(!isset($input['inviter']))
    {
        $result['hasValidInviter'] = false;
        return $result;
    }
    $matchedInviters = $database->select('Users', '*', ['username'=>$input['inviter']]);
    $result['hasValidInviter'] = (count($matchedUsernames) == 1);
    
    return $result;

}

function main()
{
    $input = json_decode(file_get_contents("php://input"));
    $validationResult = validateInput($input);
    if($validationResult['success'])
    {
        $userID = $input['userid'];
        $username = $input['username'];
        $database = initDatabase();
        $database->insert('Users', ['username'=>$username, 'tele_id'=>$userID, 'w_limit'=>W_LIMIT]);
        if($result['hasValidInviter'])
        {
            $inviter = $input['inviter'];
            $inviterWLimit = $database->select('Users', 'w_limit', ['username'=>$inviter])[0] ;
            $inviterWLimit += dW;
            $database->update('Users', ['w_limit'=>$inviterWLimit], ['username'=>$inviter]);
            $database->insert('Invitations', ['inviter'=>$inviter, 'invitee'=>$username]);
        }
    }
    echo(json_encode($validationResult));
}

main();
