# Parent Steam Service

## Overview

The `Parent Steam Service` is a system service for Windows to manage Steam playtime for parents by controlling the system hosts file. By periodically blocking domains, parents can limit access to Steam, as needed.

The service checks a URL for hosts changes on an interval. When a change is detected, the HOSTS file is modified.

Browsers typically need to be restarted after changing the HOSTS file, in order to take effect.

`manage.php`

![image_3](images/image_3.png)

`hosts.php`

![image_4](images/image_4.png)

`reboot.php`

![image_5](images/image_5.png)

`end.php`

![image_7](images/image_7.png)

## Setup

To install, open a Visual Studio Command Prompt.

```
installutil bin\Debug\SteamServiceMonitor.exe
```

To uninstall, open a Visual Studio Command Prompt.

```
installutil /u bin\Debug\SteamServiceMonitor.exe
```

After the service has been installed, start the service by either rebooting or hitting play in the `Services` control panel.

![image_1](images/image_1.png)

When the service is running, the task manager will show a process `SteamServiceMonitor.exe`.

![image_2](images/image_2.png)

The service can automatically stop named processes.

![image_6](images/image_6.png)

## Configuration

The `HostsUri` configuration setting monitors the contents of a URL which controls the contents of the system `HOSTS` file.

The `RebootUri` configuration setting monitors the contents a URL which can reboot the machine.

Sample `App.config`:

```
<?xml version="1.0" encoding="utf-8" ?>
<configuration>
    <startup>
        <supportedRuntime version="v4.0" sku=".NETFramework,Version=v4.8" />
    </startup>
  <appSettings>
    <add key="HostsUri" value="https://[your_domain_here]/path/to/hosts.php" />
    <add key="EndUri" value="https://[your_domain_here]/path/to/end.php" />
    <add key="RebootUri" value="https://[your_domain_here]/path/to/reboot.php" />
  </appSettings>
</configuration>
```

### Sample PHP JavaScript

The following sample `PHP` allows the user to control hosts configurations per machine using the `?computer=` query parameter to select the contents that the service uses.

`hosts.php`
```
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
```

The following sample `PHP` can cause the service to reboot the computer when `yes` is returned. After returning `yes` the file reverts to `no`.

`reboot.php`
```
<?php

header("Content-Type: text/plain");

$txt = 'no';

$computer = '';
if (isset($_GET['computer'])) {
  $computer = $_GET['computer'];
  $file = 'reboot_' . basename($computer) . '.txt';
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
```

The following sample `PHP` can cause the service to stop the list of processes on the computer when `yes` is returned. After returning `yes` the file reverts to `no`.

`end.php`
```
<?php

header("Content-Type: text/plain");

$computer = '';
if (isset($_GET['computer'])) {
  $computer = $_GET['computer'];
  $file = 'end_' . basename($computer) . '.txt';
  if (!file_exists($file)) {
    $myfile = fopen($file, 'w') or die('Create: Unable to open file!');
    fwrite($myfile, $txt);
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
      if (file_exists($file)) {
        $myfile = fopen($file, 'r') or die('Read: Unable to open file!');
        $contents = fread($myfile,filesize($file));
        fclose($myfile);
        echo ($contents);
      }
    }
  }
}
?>
```

The following sample `PHP` provides remote actions for `reboot`, `lock`, `unlock` for detected computers. When locked, all steam domains in the `hosts` file will point to localhost and will not resolve correctly. When unlocked, all domain redirects will be commented out in the `hosts` file.

`manage.php`
```
<html>
<head>
<title>Steam Service Management</title>
<script>
setTimeout(function() {
  window.location.href = '?';
}, 3000);
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

    echo ('<button style="width: 250px; height: 60px; padding:5px; margin:10px;" onclick="rebootComputer(\''. $entry .'\')">REBOOT ' . strtoupper($entry) . '</button>');

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
```
