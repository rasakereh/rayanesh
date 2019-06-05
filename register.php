<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/appVars.inc.php';

function validateInput($input)
{
    return ["success"=>true];
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
    }
    echo(json_encode($validationResult));
}

main();
