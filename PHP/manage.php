<html>
<head>
<title>Steam Service Management</title>
<script><?php if (isset($_GET['action'])) { ?>
setTimeout(function() {
  window.location.href = '?';
}, 3000);
<?php } ?>
function rebootComputer(computer) {
  if (confirm('Are you sure you want to reboot ' + computer + '?')) {
    if (confirm('Are you REALLY sure you want to reboot ' + computer + '?')) {
      window.location.href = 'manage.php?action=reboot&computer=' + computer;
    } else {
      window.location.href = '?';
    }
  } else {
    window.location.href = '?';
  }
}
function endProcesses(computer) {
  if (confirm('Are you sure you want to end processes on ' + computer + '?')) {
    window.location.href = 'manage.php?action=end&computer=' + computer;
  } else {
    window.location.href = '?';
  }
}
function lockComputer(action, computer) {
  if (confirm('Are you sure you want to ' + action + ' ' + computer + '?')) {
    window.location.href = 'manage.php?action='+action+'&computer=' + computer;
  } else {
    window.location.href = '?';
  }
}
</script>
</head>

<h1><a href="?">Steam Service Management</a></h1>

<?php

$action = '';
if (isset($_GET['action'])) {
  $action = $_GET['action'];
}

$computer = '';
if (isset($_GET['computer'])) {
  $computer = $_GET['computer'];
}

if (strcasecmp($action, 'reboot') == 0 &&
  isset($_GET['computer'])) {
  $computer = $_GET['computer'];
  $file = 'reboot_' . basename($computer) . '.txt';
  $myfile = fopen($file, 'w') or die('Create Reboot: Unable to open file!');
  fwrite($myfile, 'yes');
  fclose($myfile);
}

if (strcasecmp($action, 'end') == 0 &&
  isset($_GET['computer'])) {
  $computer = $_GET['computer'];
  $file = 'end_' . basename($computer) . '.txt';
  $myfile = fopen($file, 'w') or die('Create End: Unable to open file!');
  fwrite($myfile, 'yes');
  fclose($myfile);
}

if (strcasecmp($action, 'lock') == 0 &&
  isset($_GET['computer'])) {
  $computer = $_GET['computer'];
  $file = 'lock_' . basename($computer) . '.txt';
  $myfile = fopen($file, 'w') or die('Create Lock: Unable to open file!');
  fwrite($myfile, 'yes');
  fclose($myfile);
}

if (strcasecmp($action, 'unlock') == 0 &&
  isset($_GET['computer'])) {
  $computer = $_GET['computer'];
  $file = 'lock_' . basename($computer) . '.txt';
  $myfile = fopen($file, 'w') or die('Create Unlock: Unable to open file!');
  fwrite($myfile, 'no');
  fclose($myfile);
}

foreach (glob("contents_*.txt") as $filename) {
    $prefix = 'contents_';
    $suffix = '.txt';
    $entry = substr($filename, strlen($prefix), strlen($filename) - strlen($prefix) - strlen($suffix));

    $file = 'description_' . basename($entry) . '.txt';
    if (!file_exists($file)) {
      $myfile = fopen($file, 'w') or die('Create: Unable to open file!');
      fwrite($myfile, 'Unknown');
      fclose($myfile);
    }

    echo ('<div style="display: inline-table; text-align: center; border: solid black; margin: 25px; padding: 20px;">');
    include "$file";

    if (strcasecmp($action, 'reboot') == 0 &&
      strcasecmp($computer, $entry) == 0) {
      echo ('<small style="color: #F00">rebooting...</small> <wbr/>');
    }

    if (strcasecmp($action, 'end') == 0 &&
      strcasecmp($computer, $entry) == 0) {
      echo ('<small style="color: #F00">ending processes...</small> <wbr/>');
    }

    if (strcasecmp($action, 'lock') == 0 &&
      strcasecmp($computer, $entry) == 0) {
      echo ('<small style="color: #F00">locking...</small> <wbr/>');
    }

    if (strcasecmp($action, 'unlock') == 0 &&
      strcasecmp($computer, $entry) == 0) {
      echo ('<small style="color: #0F0">unlocking...</small> <wbr/>');
    }

    // read file
    $locked = 'no';
    $file = 'lock_' . basename($entry) . '.txt';
    if (file_exists($file)) {
      $myfile = fopen($file, 'r') or die('Read: Unable to open file!');
      $locked = fread($myfile,filesize($file));
      fclose($myfile);
    }

    echo ('<br/>');

    $rebooting = 'no';
    $file = 'reboot_' . basename($computer) . '.txt';
    if (file_exists($file)) {
      // read file
      $myfile = fopen($file, 'r') or die('Read: Unable to open file!');
      $rebooting = fread($myfile,filesize($file));
      fclose($myfile);
    }
    if (strcasecmp($rebooting, 'yes') == 0) {
      echo ('<button style="width: 250px; height: 60px; padding:5px; margin:10px;" onclick="rebootComputer(\''. $entry .'\')">REBOOTING ' . strtoupper($entry) . '</button>');
    } else {
      echo ('<button style="width: 250px; height: 60px; padding:5px; margin:10px;" onclick="rebootComputer(\''. $entry .'\')">REBOOT ' . strtoupper($entry) . '</button>');
    }

    echo ('<button style="width: 250px; height: 60px; padding:5px; margin:10px;" onclick="endProcesses(\''. $entry .'\')">END PROCESSES on ' . strtoupper($entry) . '</button>');

    if (strcasecmp($locked, 'yes') == 0) {
      echo ('<button style="width: 250px; height: 60px; padding:5px; margin:10px;" onclick="lockComputer(\'unlock\', \''. $entry .'\')">UNLOCK HOSTS on ' . strtoupper($entry) . '</button>');
    } else {
      echo ('<button style="width: 250px; height: 60px; padding:5px; margin:10px;" onclick="lockComputer(\'lock\', \''. $entry .'\')">LOCK HOSTS on ' . strtoupper($entry) . '</button>');
    }

    echo ('</div> <wbr/>');
}
?>
</html>
