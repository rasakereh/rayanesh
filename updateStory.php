<?php
require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/appVars.inc.php";

function main()
{
    $story = "شهید علی‌اکبر غیوری ثالث در اولین بهار زندگی خود به دنیا آمد، اما گویا دنیا به وی نیامد
    او پنج‌سال بعد وارد دانشکده‌ی هنرهای زیبا شد و از همان اول از همسن و سال‌های خود حداقل سه سال بزرگ‌تر بود
    علی اکبر قصه‌ی ما، با صدور فرمان جهاد توسط امام(عج) به دمشق رفت و از آنجا یکی یکی پله‌های ترقی را طی نموده و پزشکی قهار شد.
    او کسی نبود جز پروفسور سمیعی.
    خون آریایی تو رگات نیست اگه به اشتراک نذاری";

    $database = initDatabase();
    $database->delete("story", ["OR"=>["word_place"=>0, "word_place[!]"=>0]]);
    $words = explode(" ",$story);
    for($i = 0; $i < count($words); $i++)
    {
        $database->insert("story", ["word_place"=>$i, "word"=>$words[$i]]);
    }
    echo("done!");
}

main();
