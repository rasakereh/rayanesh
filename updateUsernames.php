<?php
require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/appVars.inc.php";

function main()
{
    $database = initDatabase();
    $database->delete("validusernames", ["OR"=>["username"=>"0", "username[!]"=>"0"]]);
    
    $fn = fopen("usernames.txt","r");
    while(!feof($fn))
    {
        $currUser = fgets($fn);
        $database->insert("validusername", ["username"=>trim($currUser)]);
    }
    fclose($fn);

    echo("done!");
}

main();
