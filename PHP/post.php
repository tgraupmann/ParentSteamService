<?php

$computer = '';
$data = '';
if (isset($_POST['computer']) &&
  isset($_POST['data'])) {
  $computer = $_POST['computer'];
  $data = $_POST['data'];
  $file = 'data_' . basename($computer) . '.txt';
  $myfile = fopen($file, 'w') or die('Data: Unable to open file!');
  fwrite($myfile, $data);
  fclose($myfile);
}
?>
