<?php
require_once __DIR__ . '/vendor/autoload.php';

/***********************
 ** Game Info
 **********************/
define("BOT_TOKEN", "862132371:AAGxs5ukw_3JcWsZO4qhwVDNCWY3BoceXc4");
define("GAME_URL", "https://rayanesh-game.herokuapp.com/story.php");
define("REGISTER_URL", "https://rayanesh-game.herokuapp.com/register.php");
define("BOT_URL", "t.me/RayaMag12Bot");
define("GAME_NAME", "rdastaan");
define("GAME_TG_URL", BOT_URL . "?game=" . GAME_NAME);

/***********************
 ** Hyper parameters
 **********************/
define("TIME_QUANTUM", 60*30); // half an hour
define("W_LIMIT", 4);
define("dW", 1);
define("RELEASE_DATE", 1560612600);
define("DELAY", 5*24*60*60);   //5 days
define("INITIALIZE_PORTION", 1);
define("WORD_COUNT", 200);
define("MAX_LEN", 15);

use Medoo\Medoo;

function initDatabase()
{
    $dbopts = parse_url(getenv('DATABASE_URL'));
    $database = new Medoo([
        'database_type' => 'pgsql',
        'database_name' => ltrim($dbopts["path"],'/'),
        'server' => $dbopts["host"],
        'username' => $dbopts["user"],
        'password' => $dbopts["pass"],
        "option"        => [
            PDO::ATTR_CASE    => PDO::CASE_NATURAL,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]
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

function tokenFromUsername($username)
{
    $str1 = $username;
    $str2 = $username;
    for($i = 0; $i < 19; $i++)
    {
        $str1 = substr(md5($str1), 3, 16);
        $str2 = substr(md5($str2), 11, 16);
    }

    return $str1.$str2;
}

function getMethodUrl(string $method): string
{
    return sprintf('https://api.telegram.org/bot%s/%s', BOT_TOKEN, $method);
}