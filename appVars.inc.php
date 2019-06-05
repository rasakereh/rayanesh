<?php
require_once __DIR__ . '/vendor/autoload.php';

$botToken = "821288398:AAFj7vf3L3hw0kLBGwsnkU__fzy8TmFxuMA";
$gameURL = "https://rayanesh-game.herokuapp.com/story.php";

/***********************
 ** Hyper parameters
 **********************/
define("TIME_QUANTUM", 1);
define("W_LIMIT", 1);
define("dW", 1);
define("DELAY", 1);
define("INITIALIZE_PORTION", 1);

use Medoo\Medoo;

function initDatabase()
{
    $dbopts = parse_url(getenv('DATABASE_URL'));
    $database = new Medoo([
        'database_type' => 'pgsql',
        'database_name' => ltrim($dbopts["path"],'/'),
        'server' => $dbopts["host"],
        'username' => $dbopts["user"],
        'password' => $dbopts["pass"]
    ]);

    return $database;
}

function logInput($input)
{
    $fd = fopen("inputs.txt", "a");
    fprintf($fd, "%s\n", $input);
}

function herokuLog($msg)
{
    error_log(var_export($msg, true));
}
