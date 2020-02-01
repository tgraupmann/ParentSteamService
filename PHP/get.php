<?php

header("Content-Type: text/plain");

$txt = 'no';

$computer = '';
if (isset($_GET['computer'])) {
  $computer = $_GET['computer'];
  $file = 'get_' . basename($computer) . '.txt';
  if (!file_exists($file)) {
    $myfile = fopen($file, 'w') or die('Create: Unable to open file!');
    fwrite($myfile, $txt);
    fclose($myfile);
    echo ($txt);
  } else {
    // read file
    $myfile = fopen($file, 'r') or die('Read: Unable to open file!');
    $contents = fread($myfile,filesize($file));
    fclose($myfile);

    if (strcasecmp($contents, 'yes') == 0) {
      // reset file
      $myfile = fopen($file, 'w') or die('Reset: Unable to open file!');
      fwrite($myfile, $txt);
      fclose($myfile);
    }

    // print contents
    echo ($contents);
  }
} else {
  echo ($txt);
}
?>
