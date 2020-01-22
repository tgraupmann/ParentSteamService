# Parent Steam Service

## Overview

The `Parent Steam Service` is a system service for Windows to manage Steam playtime for parents by controlling the system hosts file. By periodically blocking domains, parents can limit access to Steam, as needed.

The service checks a URL for hosts changes on an interval. When a change is detected, the HOSTS file is modified.

Browsers typically need to be restarted after changing the HOSTS file, in order to take effect.

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
  $file = 'contents_' . basename($computer) . '.txt';
  if (!file_exists($file)) {
    $myfile = fopen($file, 'w') or die("Unable to open file!");
    fwrite($myfile, $txt);
    fclose($myfile);
  } else {
    include $file;
  }
} else {
  echo ($txt);
}
?>
```

The following sample `PHP` can cause the service to reboot the computer when `yes` is returned. After returning `yes` the file reverts to `no`.

`reboot.php`
```
<?php

header("Content-Type: text/plain");

$txt = "no";

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

    // print contents
    echo ($contents);

    // reset file
    $myfile = fopen($file, 'w') or die('Reset: Unable to open file!');
    fwrite($myfile, $txt);
    fclose($myfile);
  }
} else {
  echo ($txt);
}
?>
```

The following sample `PHP` provides a remote reboot button for each detected computer.

`manage.php`
```
<h1>Steam Service Management</h1>

<?php

if (isset($_GET['computer'])) {
  $computer = $_GET['computer'];
  $file = 'reboot_' . basename($computer) . '.txt';
  $myfile = fopen($file, 'w') or die('Create: Unable to open file!');
  fwrite($myfile, 'yes');
  fclose($myfile);
}

?>
<html>
<head>
<script>
function rebootComputer(computer) {
  if (confirm('Are you sure you want to reboot ' + computer + '?')) {
    window.location.href = 'manage.php?computer=' + computer;
  }
}
</script>
</head>
<?php
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
    echo ('<br/>');

    echo ('<button style="width: 250px; height=60px; padding:5px; margin:10px;" onclick="rebootComputer(\''. $entry .'\')">REBOOT ' . strtoupper($entry) . '</button></div> <wbr/>');
}
?>
</html>
```
