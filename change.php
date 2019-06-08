<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/appVars.inc.php';

function time_diff($t1, $t2)
{
    return abs($t1-$t2);
}

function tokenMatchesUser($token, $username)
{
    return (tokenFromUsername($username) == $token);
}

function isWpValid($wp)
{
    if(!is_numeric($wp))    return false;
    if(intval($wp) != floatval($wp))    return false;
    if(0 > intval($wp) || intval($wp) >= WORD_COUNT)    return false;
    
    return true;
}

function isAlternativeValid($word)
{
    return (strlen($word) <= MAX_LEN) && (strpos($word, " ") === false);
}

function isUserOveractive($username)
{
    $database = initDatabase();
    $modifications = $database->select('modifications', 'change_time', ['username'=>$username]);
    $userLimit = $database->select('users', 'w_limit', ['username'=>$username])[0];
    rsort($modifications);

    return !(count($modifications) < $userLimit || time_diff($modifications[$userLimit-1], time()) > TIME_QUANTUM);
}

function validateRequest($input)
{
    //Characters need to be legal
    $result = [];
    if(!isset($input['username']) || !isset($input['wp']) || !isset($input['alternative']) || !isset($input['token']))
    {
        $result['success'] = false;
        $result['errorMsg'] = 'درخواست بد';

        return $result;
    }

    if(!tokenMatchesUser($input['token'], $input['username']))
    {
        $result['success'] = false;
        $result['errorMsg'] = 'درخواست بد';

        return $result;
    }

    if(!isWpValid($input['wp']))
    {
        $result['success'] = false;

        herokuLog("invalid wordpalce!".$input['wp']);
        return $result;
    }

    if(!isAlternativeValid($input['alternative']))
    {
        $result['success'] = false;
        $result['errorMsg'] = 'این چیه آخه بزرگوار!';

        return $result;
    }

    if(isUserOveractive($input['username']))
    {
        $result['success'] = false;
        $result['errorMsg'] = 'کار و زندگی نداری رفیق؟ برو بشین پا درسات! محدودیت تعداد تغییر داریما!';

        return $result;
    }
    
    $result['success'] = true;

    return $result;
    
}

function main()
{
    $input = json_decode(file_get_contents('php://input'), true);
    $requestValidity = validateRequest($input);
    if($requestValidity["success"] == true)
    {
        $database = initDatabase();
        $username = $input['username'];
        $word_place = $input['wp'];
        $change_time = time();
        $alternative = $input['alternative'];
        $database->insert('modifications',
            ['username'=>$username, 'word_place'=>$word_place, 'change_time'=>$change_time]);
        $database->update('story', ['word'=>$alternative, 'writer'=>$username], ['word_place'=>$word_place]);
    }

    echo(json_encode($requestValidity));
}

main();
