<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/appVars.inc.php';

function validateRequest($input)
{
    //Token to identofy real sender
    //Characters need to be legal
    return ["success" => true];
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
        $database->insert('Modifications',
            ['username'=>$username, 'word_place'=>$word_place, 'change_time'=>$change_time]);
        $database->update('Story', ['word'=>$alternative, 'writer'=>$username], ['word_place'=>$word_place]);
    }

    echo(json_encode($requestValidity));
}

main();
