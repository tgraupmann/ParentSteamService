<?php

header("Content-Type: text/plain");

$txt = "#Steam Client
c:\program files (x86)\steam\steam.exe
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
      // reset file
      $myfile = fopen($file, 'w') or die('Reset: Unable to open file!');
      fwrite($myfile, 'no');
      fclose($myfile);

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
