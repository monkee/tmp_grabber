<?php
/**
 * 
 */
define("HOST", "https://www.qianqianxs.com");

$cList = file_get_contents("list.txt");

$start = $argv[1];
$isStart = false;

if(empty($start)){
  $isStart = true;
}

foreach(explode("\n", $cList) as $url){
  if($isStart == false){
    if($url == $start){
      $isStart = true;
    }else{
      continue;
    }
  }

  printf("%s start\n", $url);
  $res = grab(HOST . $url);
  if(empty($res)){
    file_put_contents("fail.txt", $url . "\n", FILE_APPEND);
    sleep(60);  //貌似是60秒解禁
    continue;
  }
  // var_dump($res);exit;
  $cnt = sprintf("%s\n%s\n", $res['title'], $res['content']);
  file_put_contents('.' . $url, $cnt);
  
  printf("%s is ok\n", $url);
  sleep(10); //休息一秒，看看会不会解封
}

fclose($f);

function grab($url){
  $cnt = httpCall($url);

  $cnt = iconv('gbk', 'utf8', $cnt);
  preg_match('/<div class="panel-body content-body content-ext">(.*)<\\/div>/isU', $cnt, $mt);
  $content = str_replace('<br />', "\n", $mt[1]);

  $cnts = [];
  foreach(explode("\n", $content) as $line){
      $line = trim($line);
      if(!empty($line)){
        $cnts[] = "    " . $line;
      }
  }
  $content = implode("\n", $cnts);

  if(empty($content)){
    return null;
  }

  // 获取标题
  //<title>绝世盘龙最新章节,绝世盘龙 486 惟愿此生不再见,千千小说</title>
  preg_match('/<title>(.*)<\\/title>/isU', $cnt, $mt);
  $title = mb_substr($mt[1], 14, -5, "UTF-8");
  if(empty($title)){
    return null;
  }
  return [
    'title' => $title, 
    'content' => $content,
  ];
  var_dump($mt);exit;

  $dom = new DOMDocument(); 

  // Load the url's contents into the DOM 
  @$dom->loadHTML($cnt); 
  $cntNodes = $dom->getElementById("chaptercontent");
  
  $lines = [];
  foreach($cntNodes->childNodes as $node){
    if($node->nodeType == XML_TEXT_NODE){
      $line = trim($node->textContent);
      if(!empty($line)){
        $lines[] = $line;
      }
      continue;
      //文字
    }else if($node->nodeType == XML_ELEMENT_NODE){
      if($node->tagName == "br"){
        continue;
      }elseif($node->tagName == "center"){
        break;
      }
    }
  }
  // 处理下一页的数据
  $next = $dom->getElementById("pt_next");
  $href = $next->getAttribute("href");

  // 处理title
  $title = $dom->getElementById("top")->textContent;
  return [
    'title' => $title, 
    'next' => $href,
    'content' => implode("\n", $lines),
  ];
}


function httpCall($url){
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.86 Safari/537.36');
  curl_setopt($ch, CURLOPT_COOKIEJAR, 'a.cookie');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $cnt = curl_exec($ch);

  $info = curl_getinfo($ch);
  if($info['http_code'] != 200){
    printf("http=%d\n", $info['http_code']);
    return '';
  }

  return $cnt;
}