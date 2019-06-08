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
        try
        {
        $database->insert("validusernames", ["username"=>trim($currUser)]);
        }
        catch(Exception $err)
        {
            echo("Inserting: ".trim($currUser));
            echo($err->getMessage());
        }
    }
    fclose($fn);

    echo("done!");
}

main();
