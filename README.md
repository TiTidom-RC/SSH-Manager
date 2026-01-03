# SSH Manager

## User Documentation

Please check <https://titidom-rc.github.io/Documentation/fr_FR/SSHManager> for documentation on how to use this plugin on your Jeedom

## Developer documentation

This plugin can be easily use from your own plugin if you need to execute ssh commands to a remote host.

### Installation

Your plugin must depend on **SSH Manager** plugin. Here's how to set it up:

#### Step 1: Automatic installation via packages.json

Create a `packages.json` file in your plugin's root directory with the following content:

```JSON
{
  "plugin": {
    "sshmanager" : {}
  }
}
```

This will automatically install **SSH Manager** when your plugin dependencies are installed.

#### Step 2: Check SSH Manager availability

In your plugin's main class file, implement the dependency check:

```PHP
// In your plugin's main class file (e.g., yourplugin.class.php)

public static function dependancy_info() {
    $return = array();
    $return['state'] = 'ok';
    
    // Check if SSH Manager is installed and active
    try {
        if (!class_exists('sshmanager')) {
            $return['state'] = 'nok';
        }
    } catch (Exception $e) {
        $return['state'] = 'nok';
    }
    
    return $return;
}
```

And in your `plugin_info/info.json`, set:

```JSON
{
  "hasDependency": true
}
```

This ensures Jeedom will check for dependencies before allowing your plugin to run.

### Configuration

First part is probably to let user of your plugin (we will call it the *client plugin*, client of **SSH Manager**) select a remote host, you will need it somewhere in your equipment or plugin configuration.

#### HTML

In your `desktop/php/myclientplugin.php` you need to foresee input fields that will allow user to select the remote host and even to add new one directly from your plugin if needed. Please find below a code snippet that you can reuse as is. You only need to adapt the `data-l2key="host_id"` if you whish.

```HTML
<div class="form-group">
    <label class="col-sm-4 control-label help" data-help="{{Choisissez un hôte dans la liste ou créez un nouveau}}">{{Hôte}}</label>
    <div class="col-sm-3">
        <div class="input-group">
            <select class="eqLogicAttr form-control roundedLeft sshmanagerHelper" data-helper="list" data-l1key="configuration" data-l2key="host_id">

            </select>
            <span class="input-group-btn">
                <a class="btn btn-default cursor roundedRight sshmanagerHelper" data-helper="add" title="{{Ajouter un nouvel hôte}}">
                    <i class="fas fa-plus-circle"></i>
                </a>
            </span>
        </div>
    </div>
</div>
```

The important part is to keep a select with `sshmanagerHelper` class and attribute `data-helper="list"`.

We recommande as well to keep a button (`a` tag) with `sshmanagerHelper` class and attribute `data-helper="add"`.

**Optional**: If you want to allow users to edit an existing host directly from your plugin, you can add an "Edit" button:

```HTML
<div class="form-group">
    <label class="col-sm-4 control-label help" data-help="{{Choisissez un hôte dans la liste ou créez un nouveau}}">{{Hôte}}</label>
    <div class="col-sm-3">
        <div class="input-group">
            <select class="eqLogicAttr form-control roundedLeft sshmanagerHelper" data-helper="list" data-l1key="configuration" data-l2key="host_id">

            </select>
            <span class="input-group-btn">
                <a class="btn btn-default cursor sshmanagerHelper" data-helper="edit" title="{{Éditer l'hôte sélectionné}}">
                    <i class="fas fa-pen"></i>
                </a>
                <a class="btn btn-default cursor roundedRight sshmanagerHelper" data-helper="add" title="{{Ajouter un nouvel hôte}}">
                    <i class="fas fa-plus-circle"></i>
                </a>
            </span>
        </div>
    </div>
</div>
```

The edit button must have `sshmanagerHelper` class and attribute `data-helper="edit"`.

#### Include JS

In the same file, you need to include our helper js file. Do this at the same place than other js include:

```PHP
<?php include_file('desktop', 'sshmanager.helper', 'js', 'sshmanager'); // do not change anything on this line ?>
<?php include_file('desktop', 'myclientplugin', 'js', 'myclientplugin'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>
```

#### JS

In your `desktop\js\myclientplugin.js`, you need to call `buildSelectHost()` function. The argument is the current value so the function can select it correctly once the list is build.

```JS
function printEqLogic(_eqLogic) {
  buildSelectHost(_eqLogic.configuration.host_id);
}
```

#### Result

The dropdown list will be filled with available hosts created in **SSH Manager**

![select host](docs/selecthost.png)

After clicking on the small "Add" button, a modal will open to let user create a new **SSH Manager** host without leaving your plugin configuration screen

![add new host](docs/addnew.png)

After clicking *save*, the new host will be directly available in the dropdown list

If you included the "Edit" button (optional), clicking on it will open a modal to edit the currently selected host. The host information will be updated in **SSH Manager** and any changes will be immediately available to all plugins using this host.

### Usage

#### Execute SSH commands

Following function from *sshmanager* class is available. First argument is the host Id. It should match the one retrieve during configuration and second argument is an array of SSH commands. Each one will be executed one by one in the same session but different context, if you need to preserve the context, please provided severals commands in the same item separated by `;` (as you would do in a terminal)

```PHP
/**
  * execute ssh cmd on the remote host provided by hostId
  *
  * @param int $hostId
  * @param array|string $commands
  * @return array|string $results
  */
public static function executeCmds($hostId, array $commands) {}
```

`$commands` can be an array of command or a string with a single command.

Example:

```PHP
$commands = [
  'mv -f /tmp/config.yaml /opt/my_app/config.yaml',
  'cd /opt/my_app/; sudo make install'
];
$outputs = sshmanager::executeCmds($this->getConfiguration('host_id'), $commands);
```

return value, `$outputs` in the example, will be an array providing one item by commands, so:

- $outputs[0] is the result of $commands[0]
- $outputs[1] is the result of $commands[1]
- ...

if you provide a string with a single command then the output will be a string as well.

If your command produce several lines, you will receive line feed `\n` in the output, don't forget to take them into account in your code.

If you prefer an array of strings instead of multilines from the output, you can use the command `$outputsArray = explode("\n", $outputs)` in your code.

#### Close SSH connections

Since version 1.1.0, you can explicitly close SSH connections after executing your commands. This is recommended to avoid keeping connections open unnecessarily.

```PHP
/**
  * Close SSH connection for a specific host
  *
  * @param int $hostId
  * @return void
  */
public static function closeConnection($hostId) {}
```

Example:

```PHP
$commands = ['uptime', 'df -h'];
$outputs = sshmanager::executeCmds($this->getConfiguration('host_id'), $commands);

// Process outputs...

// Close connection when done
sshmanager::closeConnection($this->getConfiguration('host_id'));
```

You can also close all open connections at once:

```PHP
/**
  * Close all SSH connections
  *
  * @return void
  */
public static function closeAllConnections() {}
```

#### Send file to remote host

Send a local file to the remote host via SFTP.

```PHP
/**
  * Send a file to the remote host
  *
  * @param int $hostId
  * @param string $localFile - Path to the local file
  * @param string $remoteFile - Path where to save the file on remote host
  * @param bool $resume - Resume upload if interrupted (optional, default: false)
  * @return bool - true if the file was sent successfully
  */
public static function sendFile($hostId, string $localFile, string $remoteFile, $resume = false) {}
```

Example:

```PHP
$localFile = '/tmp/config.yaml';
$remoteFile = '/opt/my_app/config.yaml';

$success = sshmanager::sendFile(
    $this->getConfiguration('host_id'), 
    $localFile, 
    $remoteFile
);

if ($success) {
    log::add('myplugin', 'info', 'File uploaded successfully');
} else {
    log::add('myplugin', 'error', 'Failed to upload file');
}
```

The `$resume` parameter allows you to resume an interrupted upload. This is useful for large files that may fail during transfer.

#### Retrieve file from remote host

Download a file from the remote host via SFTP.

```PHP
/**
  * Get a file from the remote host
  *
  * @param int $hostId
  * @param string $remoteFile - Path to the file on remote host
  * @param string $localFile - Path where to save the file locally (optional)
  * @return bool|string - true if file saved to $localFile, or file content as string if $localFile is false
  */
public static function getFile($hostId, string $remoteFile, $localFile = false) {}
```

Example 1: Download and save to local file

```PHP
$remoteFile = '/var/log/my_app.log';
$localFile = '/tmp/my_app.log';

$success = sshmanager::getFile(
    $this->getConfiguration('host_id'), 
    $remoteFile, 
    $localFile
);

if ($success) {
    log::add('myplugin', 'info', 'File downloaded successfully');
    $content = file_get_contents($localFile);
}
```

Example 2: Get file content directly

```PHP
$remoteFile = '/etc/hostname';

$content = sshmanager::getFile(
    $this->getConfiguration('host_id'), 
    $remoteFile
);

if ($content !== false) {
    log::add('myplugin', 'info', "Hostname: {$content}");
}
```

### Best Practices

1. **Always close connections**: Use `closeConnection()` or `closeAllConnections()` after executing your commands to free resources
2. **Handle errors**: Check if the returned output contains error messages and handle them appropriately
3. **Test thoroughly**: Test your SSH commands manually before integrating them into your plugin
4. **Batch commands**: When multiple commands need to share context, combine them with `;` in a single command string
5. **User feedback**: Provide clear feedback to users when SSH operations succeed or fail

### Version Requirements

- **SSH Manager 1.0.0+**: Basic SSH command execution
- **SSH Manager 1.1.0+**: Connection closing functions (`closeConnection`, `closeAllConnections`)
- **SSH Manager 1.2.0+**: Edit host functionality from client plugins

Make sure to specify the minimum required version of SSH Manager in your plugin dependencies.
