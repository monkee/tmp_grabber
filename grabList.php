<?php

// $cnt = file_get_contents("https://www.qianqianxs.com/67/67309/");

// // 从gbk转utf8
// $cnt = iconv("gbk", "utf8", $cnt);

$cnt = file_get_contents("listbody.html");

$m = preg_match_all('/<li><a href="(\\/67\\/67309\\/[0-9]+\.html)">([^<]+)<\\/a><\\/li>/isU', $cnt, $mch);

echo implode("\n", $mch[1]);
echo "\n";