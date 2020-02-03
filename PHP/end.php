<?php

header("Content-Type: text/plain");

$txt = "#Steam Client
steam

#Discord
c:\users\devin\appdata\local\discord\app-0.0.305\discord.exe

#Rust
rustclient

#Escape from Tarkov
escapefromtarkov

#Task Manager
taskmgr
";

$computer = '';
if (isset($_GET['computer'])) {
  $computer = $_GET['computer'];
  $file = 'end_' . basename($computer) . '.txt';
  if (!file_exists($file)) {
    $myfile = fopen($file, 'w') or die('Create: Unable to open file!');
    fwrite($myfile, 'no');
    fclose($myfile);
    // blank result
  } else {
    // read file
    $myfile = fopen($file, 'r') or die('Read: Unable to open file!');
    $ready = fread($myfile,filesize($file));
    fclose($myfile);

    if (strcasecmp($ready, 'yes') == 0) {
      $file = 'processes_' . basename($computer) . '.txt';
      if (!file_exists($file)) {
        $myfile = fopen($file, 'w') or die('Create: Unable to open file!');
        fwrite($myfile, $txt);
        fclose($myfile);
      } else {
        $myfile = fopen($file, 'r') or die('Read: Unable to open file!');
        $contents = fread($myfile,filesize($file));
        fclose($myfile);
        echo ($contents);
      }
    }
  }
}
?>
