<?php
$cookie_name = "steamservicemgmt";
$cookie_value = "1337";
$proceed = false;
if (!isset($_COOKIE[$cookie_name])) {
  $user = '';
  if (isset($_POST['user'])) {
    $user = $_POST['user'];
  }
  $pass = '';
  if (isset($_POST['pass'])) {
    $pass = $_POST['pass'];
  }

  if($user == "parent" && $pass == "password") {
    $proceed = true;
    setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day
  }
} else {
  $proceed = true;
}

if ($proceed == false) {
  //echo "Cookie named '" . $cookie_name . "' is not set!";
  ?>
  <div class="divText">Authentication is required to proceed.</div><br/>
  <br/>
  <form method="POST" action="">
  <div class="divText">Username:</div>&nbsp;<input type="text" name="user"></input><br/>
  <br/>
  <div class="divText">Password:</div>&nbsp;<input type="password" name="pass"></input><br/>
  <br/>
  <input type="submit" name="submit" value="Submit"></input>
  </form>
  <?php

  exit();
} ?><html>
<head>
<title>Steam Service Management</title>
<script><?php if (isset($_GET['action'])) { ?>
setTimeout(function() {
  window.location.href = '?';
}, 3000);
<?php } ?>
function getCookie(cname) {
  var name = cname + "=";
  var decodedCookie = decodeURIComponent(document.cookie);
  var ca = decodedCookie.split(';');
  for(var i = 0; i <ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) == ' ') {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}
function setCookie(cname, cvalue, exMins) {
    var d = new Date();
    d.setTime(d.getTime() + (exMins*60*1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}
function logout() {
  setCookie('<?php echo ($cookie_name) ?>', '', 0);
  //console.log('cookie', getCookie('<?php echo ($cookie_name) ?>'));
  window.location.href = '?';
}
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
function getProcesses(computer) {
  if (confirm('Are you sure you want to get processes on ' + computer + '?')) {
    window.location.href = 'manage.php?action=get&computer=' + computer;
  } else {
    window.location.href = '?';
  }
}
function blockProcesses(computer) {
  if (confirm('Are you sure you want to block processes on ' + computer + '?')) {
    window.location.href = 'manage.php?action=block&computer=' + computer;
  } else {
    window.location.href = '?';
  }
}
function unblockProcesses(computer) {
  if (confirm('Are you sure you want to unblock processes on ' + computer + '?')) {
    window.location.href = 'manage.php?action=unblock&computer=' + computer;
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

<div>
  <a href="?" style="font-size: 2em; padding: 100px">Steam Service Management</a>
  <button style="width: 150px; height: 60px;" onclick="logout()">LOG OUT</button>
</div>

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

if (strcasecmp($action, 'get') == 0 &&
  isset($_GET['computer'])) {
  $computer = $_GET['computer'];
  $file = 'get_' . basename($computer) . '.txt';
  $myfile = fopen($file, 'w') or die('Create Get: Unable to open file!');
  fwrite($myfile, 'yes');
  fclose($myfile);
}

if (strcasecmp($action, 'block') == 0 &&
  isset($_GET['computer'])) {
  $computer = $_GET['computer'];
  $file = 'end_' . basename($computer) . '.txt';
  $myfile = fopen($file, 'w') or die('Create End: Unable to open file!');
  fwrite($myfile, 'yes');
  fclose($myfile);
}

if (strcasecmp($action, 'unblock') == 0 &&
  isset($_GET['computer'])) {
  $computer = $_GET['computer'];
  $file = 'end_' . basename($computer) . '.txt';
  $myfile = fopen($file, 'w') or die('Create End: Unable to open file!');
  fwrite($myfile, 'no');
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

    if (strcasecmp($action, 'get') == 0 &&
      strcasecmp($computer, $entry) == 0) {
      echo ('<small style="color: #F00">getting processes...</small> <wbr/>');
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
    $file = 'reboot_' . basename($entry) . '.txt';
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

    $file = 'get_' . basename($entry) . '.txt';
    $getting = 'no';
    if (file_exists($file)) {
      // read file
      $myfile = fopen($file, 'r') or die('End: Unable to open file!');
      $getting = fread($myfile,filesize($file));
      fclose($myfile);
    }
    if (strcasecmp($getting, 'yes') == 0) {
      echo ('<button style="width: 250px; height: 60px; padding:5px; margin:10px;" onclick="getProcesses(\''. $entry .'\')">GETTING PROCESSES on ' . strtoupper($entry) . '</button>');
    } else {
      echo ('<button style="width: 250px; height: 60px; padding:5px; margin:10px;" onclick="getProcesses(\''. $entry .'\')">GET PROCESSES on ' . strtoupper($entry) . '</button>');
    }

    $file = 'end_' . basename($entry) . '.txt';
    $blocked = 'no';
    if (file_exists($file)) {
      // read file
      $myfile = fopen($file, 'r') or die('End: Unable to open file!');
      $blocked = fread($myfile,filesize($file));
      fclose($myfile);
    }
    if (strcasecmp($blocked, 'yes') == 0) {
      echo ('<button style="width: 250px; height: 60px; padding:5px; margin:10px;" onclick="unblockProcesses(\''. $entry .'\')">UNBLOCK GAMES on ' . strtoupper($entry) . '</button>');
    } else {
      echo ('<button style="width: 250px; height: 60px; padding:5px; margin:10px;" onclick="blockProcesses(\''. $entry .'\')">BLOCK GAMES on ' . strtoupper($entry) . '</button>');
    }

    if (strcasecmp($locked, 'yes') == 0) {
      echo ('<button style="width: 250px; height: 60px; padding:5px; margin:10px;" onclick="lockComputer(\'unlock\', \''. $entry .'\')">UNLOCK HOSTS on ' . strtoupper($entry) . '</button>');
    } else {
      echo ('<button style="width: 250px; height: 60px; padding:5px; margin:10px;" onclick="lockComputer(\'lock\', \''. $entry .'\')">LOCK HOSTS on ' . strtoupper($entry) . '</button>');
    }

    echo ('</div> <wbr/>');
}
?>
</html>
