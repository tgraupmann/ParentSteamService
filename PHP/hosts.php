<?php

header("Content-Type: text/plain");

$txt = "#blocked domains:
127.0.0.1   steampowered.com
127.0.0.1   steamcommunity.com
127.0.0.1   steamgames.com
127.0.0.1   steamusercontent.com
127.0.0.1   steamcontent.com
127.0.0.1   steamstatic.com
127.0.0.1   akamaihd.net
127.0.0.1   store.steampowered.com
127.0.0.1   steamstore-a.akamaihd.net
127.0.0.1   steamcdn-a.akamaihd.net
";

$computer = '';
if (isset($_GET['computer'])) {
  $computer = $_GET['computer'];

  // read file
  $locked = 'no';
  $file = 'lock_' . basename($computer) . '.txt';
  if (file_exists($file)) {
    $myfile = fopen($file, 'r') or die('Read: Unable to open file!');
    $locked = fread($myfile,filesize($file));
    fclose($myfile);
  }

  $file = 'contents_' . basename($computer) . '.txt';
  if (!file_exists($file)) {
    $myfile = fopen($file, 'w') or die("Unable to open file!");
    fwrite($myfile, $txt);
    fclose($myfile);
  } else {
    $myfile = fopen($file, 'r') or die('Read: Unable to open file!');
    $content = fread($myfile,filesize($file));
    fclose($myfile);

    if (strcasecmp($locked, 'yes') != 0) {
      $content = preg_replace("/\r\n|\r|\n/", "\r\n#", $content);
    }
    echo ($content);
  }
} else {
  $txt = preg_replace("/\r\n|\r|\n/", "\r\n#", $txt);
  echo ($txt);
}
?>
