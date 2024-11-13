<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

require_once __DIR__  . '/../../../../core/php/core.inc.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use phpseclib3\Net\SSH2;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SFTP;

if (!defined('NET_SSH2_LOGGING')) {
    define('NET_SSH2_LOGGING', 2); // SSH2::LOG_COMPLEX
}

if (!defined('NET_SFTP_LOGGING')) {
    define('NET_SFTP_LOGGING', 2); // SFTP::LOG_COMPLEX
}

class SSHConnectException extends \RuntimeException {

}

class SSHException extends \RuntimeException {
    private $log;
    private $lastError;

    public function __construct($message, $lastError = '', $log = '') {
        parent::__construct($message);
        $this->log = $log;
        $this->lastError = $lastError;
    }

    public function getLastError() {
        return $this->lastError;
    }

    public function getLog() {
        return $this->log;
    }
}

class sshmanager extends eqLogic {

    const COMMANDS_FILEPATH = __DIR__ . '/../../data/commands/commands.json';

    const CONFIG_USERNAME = 'username';
    const CONFIG_PASSWORD = 'password';
    const CONFIG_SSH_KEY = 'ssh-key';
    const CONFIG_SSH_PASSPHRASE = 'ssh-passphrase';
    const CONFIG_AUTH_METHOD = 'auth-method';
    const CONFIG_HOST = 'host';
    const CONFIG_PORT = 'port';
    const CONFIG_TIMEOUT = 'timeout';

    const AUTH_METHOD_PASSWORD = 'password';
    const AUTH_METHOD_SSH_KEY = 'ssh-key';
    const AUTH_METHOD_AGENT = 'agent';
    const DEFAULT_AUTH_METHOD = 'password';

    const DEFAULT_TIMEOUT = 30;
    const DEFAULT_PORT = 22;

    const CONST_TO_JS = [
        'CONFIG_USERNAME' => self::CONFIG_USERNAME,
        'CONFIG_PASSWORD' => self::CONFIG_PASSWORD,
        'CONFIG_SSH_KEY' => self::CONFIG_SSH_KEY,
        'CONFIG_SSH_PASSPHRASE' => self::CONFIG_SSH_PASSPHRASE,
        'CONFIG_AUTH_METHOD' => self::CONFIG_AUTH_METHOD,
        'CONFIG_HOST' => self::CONFIG_HOST,
        'CONFIG_PORT' => self::CONFIG_PORT,
        'CONFIG_TIMEOUT' => self::CONFIG_TIMEOUT,
        'AUTH_METHOD_PASSWORD' => self::AUTH_METHOD_PASSWORD,
        'AUTH_METHOD_SSH_KEY' => self::AUTH_METHOD_SSH_KEY,
        'AUTH_METHOD_AGENT' => self::AUTH_METHOD_AGENT,
    ];

    public function decrypt() {
        $this->setConfiguration(self::CONFIG_USERNAME, utils::decrypt($this->getConfiguration(self::CONFIG_USERNAME)));
        $this->setConfiguration(self::CONFIG_PASSWORD, utils::decrypt($this->getConfiguration(self::CONFIG_PASSWORD)));
        $this->setConfiguration(self::CONFIG_SSH_KEY, utils::decrypt($this->getConfiguration(self::CONFIG_SSH_KEY)));
        $this->setConfiguration(self::CONFIG_SSH_PASSPHRASE, utils::decrypt($this->getConfiguration(self::CONFIG_SSH_PASSPHRASE)));
    }

    public function encrypt() {
        $this->setConfiguration(self::CONFIG_USERNAME, utils::encrypt($this->getConfiguration(self::CONFIG_USERNAME)));
        $this->setConfiguration(self::CONFIG_PASSWORD, utils::encrypt($this->getConfiguration(self::CONFIG_PASSWORD)));
        $this->setConfiguration(self::CONFIG_SSH_KEY, utils::encrypt($this->getConfiguration(self::CONFIG_SSH_KEY)));
        $this->setConfiguration(self::CONFIG_SSH_PASSPHRASE, utils::encrypt($this->getConfiguration(self::CONFIG_SSH_PASSPHRASE)));
    }

    public static function getPluginBranch() {
        $pluginBranch = 'N/A';
		try {
			$_updateSSHManager = update::byLogicalId('sshmanager');
			$pluginBranch = $_updateSSHManager->getConfiguration('version', 'N/A') . ' (' . $_updateSSHManager->getSource() . ')';
		}
		catch (\Exception $e) {
			log::add(__CLASS__, 'warning', '[BRANCH] Get ERROR :: ' . $e->getMessage());
		}
		log::add(__CLASS__, 'info', '[BRANCH] PluginBranch :: ' . $pluginBranch);
        return $pluginBranch;
    }

    public static function getPluginVersion() {
        $pluginVersion = '0.0.0';
        try {
            if (!file_exists(dirname(__FILE__) . '/../../plugin_info/info.json')) {
                log::add(__CLASS__, 'warning', '[VERSION] fichier info.json manquant');
            }
            $data = json_decode(file_get_contents(dirname(__FILE__) . '/../../plugin_info/info.json'), true);
            if (!is_array($data)) {
                log::add(__CLASS__, 'warning', '[VERSION] Impossible de décoder le fichier info.json');
            }
            try {
                $pluginVersion = $data['pluginVersion'];
            } catch (\Exception $e) {
                log::add(__CLASS__, 'warning', '[VERSION] Impossible de récupérer la version du plugin');
            }
        } catch (\Exception $e) {
            log::add(__CLASS__, 'warning', '[VERSION] Get ERROR :: ' . $e->getMessage());
        }
        log::add(__CLASS__, 'info', '[VERSION] PluginVersion :: ' . $pluginVersion);
        return $pluginVersion;
    }

    public static function getConfigForCommunity() {
        $CommunityInfo = "```\n";
        $CommunityInfo = $CommunityInfo . 'Debian : ' . system::getOsVersion() . "\n";
        $CommunityInfo = $CommunityInfo . 'PluginVersion : ' . config::byKey('pluginVersion', 'sshmanager') . "\n";
        $CommunityInfo = $CommunityInfo . 'PluginBranch : ' . config::byKey('pluginBranch', 'sshmanager') . "\n";
        $CommunityInfo = $CommunityInfo . "```";
        return $CommunityInfo;
    }

    // Methods used by client plugins

    public static function getRemoteHosts() {
        $hosts = [];
        foreach (eqLogic::byType(__CLASS__, true) as $sshmanager) {
            $hosts[$sshmanager->getId()] = $sshmanager->getName();
        }
        return $hosts;
    }
    
    public static function checkSFTPConnection($hostId) {
        /** @var sshmanager */
        $sshmanager = eqLogic::byId($hostId);
        if (!is_object($sshmanager)) {
            throw new Exception('Invalid Host Id');
        }
        // log::add(__CLASS__, 'debug', "[{$sshmanager->getName()}] Check SFTP Connection");
        return $sshmanager->internalCheckSFTPConnection();
    }

    /**
     * check ssh connection on the remote host provided by hostId
     *
     * @param int $hostId
     * @return bool $status
     */
    public static function checkSSHConnection($hostId) {
        /** @var sshmanager */
        $sshmanager = eqLogic::byId($hostId);
        if (!is_object($sshmanager)) {
            throw new Exception('Invalid Host Id');
        }
        // log::add(__CLASS__, 'debug', "[{$sshmanager->getName()}] Check SSH Connection");
        return $sshmanager->internalCheckSSHConnection();
    }

    // TODO Add a timeout option to executeCmds

    /**
     * execute ssh cmd on the remote host provided by hostId
     *
     * @param int $hostId
     * @param array|string $commands
     * @return array|string $results
     */
    public static function executeCmds($hostId, $commands, $cmdName = '') {
        /** @var sshmanager */
        $sshmanager = eqLogic::byId($hostId);
        if (!is_object($sshmanager)) {
            throw new Exception('Invalid Host Id');
        }

        if (is_array($commands)) {
            log::add(__CLASS__, 'debug', "[{$sshmanager->getName()}] Cmds :: " . json_encode($commands));
            $results = [];
            foreach ($commands as $cmd) {
                if (trim($cmd) === '') {
                    log::add(__CLASS__, 'warning', '[' . $sshmanager->getName() . '] ' . (!empty($cmdName) ? $cmdName : 'Cmd') . ' :: Empty command (array)');
                    $results[] = '';
                }
                $results[] = $sshmanager->internalExecuteCmd($cmd);
            }
            return $results;
        } elseif (is_string($commands)) {
            if (trim($commands) === '') {
                log::add(__CLASS__, 'warning', '[' . $sshmanager->getName() . '] ' . (!empty($cmdName) ? $cmdName : 'Cmd') . ' :: Empty command (string)');
                return '';
            }
            return $sshmanager->internalExecuteCmd($commands, $cmdName);
        } else {
            throw new Exception('Invalid Command Type');
        }
    }

    /**
     * send a file to the remote host
     *
     * @param int $hostId
     * @param string $localFile - path to the local file
     * @param string $remoteFile - path to the remote file
     * @return bool - true if the file was sent successfully
     */
    public static function sendFile($hostId, string $localFile, string $remoteFile, $resume = false) {
        /** @var sshmanager */
        $sshmanager = eqLogic::byId($hostId);
        if (!is_object($sshmanager)) {
            throw new Exception('Invalid Host Id');
        }
        return $sshmanager->internalSendFile($localFile, $remoteFile, $resume);
    }

    /**
     * get a file from the remote host
     *
     * @param int $hostId
     * @param string $remoteFile - path to the remote file
     * @param string $localFile - path to the local file
     * @param bool $resume - resume the download
     * @return bool - true if the file was received successfully
     */
    public static function getFile($hostId, string $remoteFile, $localFile = false) {
        /** @var sshmanager */
        $sshmanager = eqLogic::byId($hostId);
        if (!is_object($sshmanager)) {
            throw new Exception('Invalid Host Id');
        }
        return $sshmanager->internalGetFile($remoteFile, $localFile);
    }

    // end methods used by client plugins

    public static function getTemplateCommands() {
        $commandsJson = self::COMMANDS_FILEPATH;
        if (file_exists($commandsJson)) {
            $commandsJson = file_get_contents($commandsJson, true);
        } else {
            log::add(__CLASS__, 'error', '[TemplateCmds] Commands :: JSON File not found');
            throw new Exception('Commands :: JSON File not found');
        }
        $commands = json_decode($commandsJson, true);
        ksort($commands);
        return $commands;
    }

    private function getConnectionData() {
        /** @var string */
        $host = $this->getConfiguration(self::CONFIG_HOST);
        /** @var int */
        $port = $this->getConfiguration(self::CONFIG_PORT, self::DEFAULT_PORT);
        /** @var int */
        $timeout = $this->getConfiguration(self::CONFIG_TIMEOUT, self::DEFAULT_TIMEOUT);

        if ($host == "") {
            log::add(__CLASS__, 'error', '[' . $this->getName() .  '] Host name or IP not defined');
            throw new RuntimeException(__('Adresse IP ou nom d\'hôte non configuré', __FILE__));
        }

        return [$host, $port, $timeout];
    }

    private function getAuthenticationData() {
        /** @var string */
        $username = $this->getConfiguration(self::CONFIG_USERNAME);
        if ($username == "") {
            log::add(__CLASS__, 'error', '[' . $this->getName() .  '] Username not defined');
            throw new RuntimeException(__('Nom d\'utilisateur non configuré', __FILE__));
        }

        /** @var string */
        $authmethod = $this->getConfiguration(self::CONFIG_AUTH_METHOD, self::DEFAULT_AUTH_METHOD);

        switch ($authmethod) {
            case self::CONFIG_PASSWORD:
                $keyOrpassword = $this->getConfiguration(self::CONFIG_PASSWORD);
                if ($keyOrpassword == "") {
                    log::add(__CLASS__, 'error', '[' . $this->getName() .  '] Password not defined');
                    throw new RuntimeException(__('Mot de passe non configuré', __FILE__));
                }
                break;
            case self::AUTH_METHOD_SSH_KEY:
                $sshkey = $this->getConfiguration(self::CONFIG_SSH_KEY);
                $sshpassphrase = $this->getConfiguration(self::CONFIG_SSH_PASSPHRASE);
                if ($sshkey == "") {
                    log::add(__CLASS__, 'error', '[' . $this->getName() .  '] SSH Key not defined');
                    throw new RuntimeException(__('Clé SSH non configurée', __FILE__));
                }
                try {
                    $keyOrpassword = PublicKeyLoader::load($sshkey, $sshpassphrase);
                } catch (\phpseclib3\Exception\NoKeyLoadedException $ex) {
                    log::add(__CLASS__, 'error', '[' . $this->getName() .  '] ' . $ex->getMessage());
                    throw $ex;
                }
                break;
            case self::AUTH_METHOD_AGENT:
                //TODO: check if agent auth could be usefull? we only need to uncomment the following line and remove the exception
                // $keyOrpassword = new \phpseclib3\System\SSH\Agent();
                throw new RuntimeException(sprintf(__("Méthode d'authentification non supportée: %s", __FILE__), $authmethod));
                break;
            default:
                throw new RuntimeException(sprintf(__("Méthode d'authentification non supportée: %s", __FILE__), $authmethod));
        }
        return [$username, $keyOrpassword];
    }

    private function internalSendFile(string $localFile, string $remoteFile, $resume = false) {	
        if (!file_exists($localFile)) {
            log::add(__CLASS__, 'error', "[{$this->getName()}] SFTP Local file not found :: {$localFile}");
            return false;
        }
        
        try {
            $sftp = $this->getSFTPClient();
        } catch (RuntimeException $ex) {
            log::add(__CLASS__, 'error', "[{$this->getName()}] SFTP (GetSFTPClient) RunTimeEx :: {$ex->getMessage()}");
            throw new RuntimeException("SFTP (GetSFTPClient) RunTimeEx :: {$ex->getMessage()}");
        } catch (\Throwable $th) {
            log::add(__CLASS__, 'error', "[{$this->getName()}] SFTP (GetSFTPClient) General Exception :: " . $th->getMessage());
            throw $th;
        }

        $result = false;
        log::add(__CLASS__, 'debug', '[' . $this->getName() . '] Send file :: ' . $localFile);

        try {
            // @throws \UnexpectedValueException — on receipt of unexpected packets
            // @throws FileNotFoundException - if you're uploading via a file and the file doesn't exist    
            
            if ($resume) {
                $result = $sftp->put($remoteFile, $localFile, SFTP::SOURCE_LOCAL_FILE | SFTP::RESUME);
            } else {
                $result = $sftp->put($remoteFile, $localFile, SFTP::SOURCE_LOCAL_FILE);
            }

        } catch (\UnexpectedValueException $ex) {
            log::add(__CLASS__, 'debug', "[{$this->getName()}] SFTP (SendFile) Remote File Expected :: {$localFile}");
            log::add(__CLASS__, 'error', "[{$this->getName()}] SFTP (SendFile) UnexpectedValueException :: " . $ex->getMessage());

            log::add(__CLASS__, 'debug', '['. $this->getName() .'] SFTP (SendFile) RuntimeEx LastError :: ' . $sftp->getLastSFTPError());
            log::add(__CLASS__, 'debug', '['. $this->getName() .'] SFTP (SendFile) RuntimeEx Logs ::' . "\r\n" . $sftp->getSFTPLog());
            throw new SSHException($ex->getMessage(), $sftp->getLastSFTPError(), $sftp->getSFTPLog());

        } catch (\phpseclib3\Exception\FileNotFoundException $ex) {
            log::add(__CLASS__, 'debug', "[{$this->getName()}] SFTP (SendFile) Local File Expected :: {$localFile}");
            log::add(__CLASS__, 'error', "[{$this->getName()}] SFTP (SendFile) FileNotFoundException :: " . $ex->getMessage());

            log::add(__CLASS__, 'debug', '['. $this->getName() .'] SFTP (SendFile) FileNotFoundException LastError :: ' . $sftp->getLastSFTPError());
            log::add(__CLASS__, 'debug', '['. $this->getName() .'] SFTP (SendFile) FileNotFoundException Logs ::' . "\r\n" . $sftp->getSFTPLog());
            throw new SSHException($ex->getMessage(), $sftp->getLastSFTPError(), $sftp->getSFTPLog());

        } catch (RuntimeException $ex) {
            log::add(__CLASS__, 'debug', "[{$this->getName()}] SFTP (SendFile) Local File Expected :: {$localFile}");
            log::add(__CLASS__, 'error', "[{$this->getName()}] SFTP (SendFile) RunTimeEx :: {$ex->getMessage()}");
            
            log::add(__CLASS__, 'debug', '['. $this->getName() .'] SFTP (SendFile) RuntimeEx LastError :: ' . $sftp->getLastSFTPError());
            log::add(__CLASS__, 'debug', '['. $this->getName() .'] SFTP (SendFile) RuntimeEx Logs ::' . "\r\n" . $sftp->getSFTPLog());
            throw new SSHException($ex->getMessage(), $sftp->getLastSFTPError(), $sftp->getSFTPLog());

        } catch (\Throwable $th) {
            log::add(__CLASS__, 'debug', "[{$this->getName()}] SFTP (SendFile) Local File Expected :: {$localFile}");
            log::add(__CLASS__, 'error', "[{$this->getName()}] SFTP (SendFile) General Exception :: " . $th->getMessage());
            throw $th;
        }
        return $result;
    }

    private function internalGetFile(string $remoteFile, $localFile = false) {
        try {
            $sftp = $this->getSFTPClient();
        } catch (RuntimeException $ex) {
            log::add(__CLASS__, 'error', "[{$this->getName()}] SFTP (GetSFTPClient) RunTimeEx :: {$ex->getMessage()}");
            throw new RuntimeException("SFTP (GetSFTPClient) RunTimeEx :: {$ex->getMessage()}");
        } catch (\Throwable $th) {
            log::add(__CLASS__, 'error', "[{$this->getName()}] SFTP (GetSFTPClient) General Exception :: " . $th->getMessage());
            throw $th;
        }
        
        $result = false;
        log::add(__CLASS__, 'debug', '[' . $this->getName() . '] Get file :: ' . $remoteFile);

        try {
            if ($localFile === false) {
                $result = $sftp->get($remoteFile);
            } else {
                $result = $sftp->get($remoteFile, $localFile);
            }

            if (!$sftp->isConnected()) {
                log::add(__CLASS__, 'error', '[' . $this->getName() . '] SFTP (GetFile) :: Disconnected');
                $sftp->disconnect();
                return $result;
            }

            if ($sftp->isTimeout()) {
                log::add(__CLASS__, 'error', '[' . $this->getName() . '] SFTP (GetFile) :: Timeout');
                $sftp->reset();
                return $result;
            }

            if ($result !== false) {
                log::add(__CLASS__, 'debug', '[' . $this->getName() . '] SFTP (GetFile) Remote File Received :: ' . $remoteFile);
            }
        } catch (\UnexpectedValueException $ex) {
            log::add(__CLASS__, 'debug', '[' . $this->getName() . '] SFTP (GetFile) Remote File Expected :: ' . $remoteFile);
            log::add(__CLASS__, 'error', "[{$this->getName()}] SFTP (GetFile) UnexpectedValueException :: " . $ex->getMessage());
            
            log::add(__CLASS__, 'debug', '['. $this->getName() .'] SFTP (GetFile) UnexpectedValueException LastError :: ' . $sftp->getLastSFTPError());
            log::add(__CLASS__, 'debug', '['. $this->getName() .'] SFTP (GetFile) UnexpectedValueException Logs ::' . "\r\n" . $sftp->getSFTPLog());
            throw new SSHException($ex->getMessage(), $sftp->getLastSFTPError(), $sftp->getSFTPLog());

        } catch (RuntimeException $ex) {
            log::add(__CLASS__, 'debug', '[' . $this->getName() . '] SFTP (GetFile) Remote File Expected :: ' . $remoteFile);
            log::add(__CLASS__, 'error', "[{$this->getName()}] SFTP (GetFile) RunTimeEx :: {$ex->getMessage()}");
            
            log::add(__CLASS__, 'debug', '['. $this->getName() .'] SFTP (GetFile) RuntimeEx LastError :: ' . $sftp->getLastSFTPError());
            log::add(__CLASS__, 'debug', '['. $this->getName() .'] SFTP (GetFile) RuntimeEx Logs ::' . "\r\n" . $sftp->getSFTPLog());
            throw new SSHException($ex->getMessage(), $sftp->getLastSFTPError(), $sftp->getSFTPLog());

        } catch (\Throwable $th) {
            log::add(__CLASS__, 'debug', '[' . $this->getName() . '] SFTP (GetFile) Remote File Expected :: ' . $remoteFile);
            log::add(__CLASS__, 'error', "[{$this->getName()}] SFTP (GetFile) General Exception :: " . $th->getMessage());
            throw $th;
        }
        return $result;
    }

    /** @var SSH2[] */
    private static $_ssh2_client = [];

    /** @var SFTP[] */
    private static $_sftp_client = [];

    private function getSFTPClient() {
        $eqLogicID = $this->getId();
        $eqLogicName = $this->getName();
        $pid = getmypid();

        if (!(isset(sshmanager::$_sftp_client[$eqLogicID]))) {
            [$host, $port, $timeout] = $this->getConnectionData();
            [$username, $keyOrpassword] = $this->getAuthenticationData();
            log::add(__CLASS__, 'debug', "[{$eqLogicName}] >>>> Creating SFTP client (pid: {$pid}) for eqLogic {$eqLogicID} to {$host}");
            
            try {
                $sftp = new SFTP($host, $port, $timeout);
            } catch (Exception $e) {
                log::add(__CLASS__, 'error', "[{$eqLogicName}] >>>> SFTP Exception :: " . $e->getMessage());
                throw $e;
            }

            try {
                if (!$sftp->login($username, $keyOrpassword)) {
                    log::add(__CLASS__, 'error', "[{$eqLogicName}] >>>> SFTP Login failed for {$username}@{$host}:{$port}");
                    throw new RuntimeException("SFTP Login failed for {$username}@{$host}:{$port}; please check username and password or ssh key.");
                }
            } catch (RuntimeException $ex) {
                log::add(__CLASS__, 'error', '[' . $eqLogicName . '] SFTP Login Exception :: ' . $ex->getMessage());
                throw $ex;
            } catch (\Throwable $th) {
                log::add(__CLASS__, 'error', "[{$eqLogicName}] SFTP General Exception :: " . $th->getMessage());
                throw $th;
            }
            log::add(__CLASS__, 'debug', "[{$eqLogicName}] >>>> SFTP Connected and authenticated");
            sshmanager::$_sftp_client[$eqLogicID] = $sftp;

        } else {
            // log::add(__CLASS__, 'debug', "[" . $eqLogicName . "] >>>> Existing SFTP client (pid: {$pid}) for eqLogic {$eqLogicID}");
        }
        return sshmanager::$_sftp_client[$eqLogicID];
    }

    private function getSSH2Client() {
        $eqLogicID = $this->getId();
        $eqLogicName = $this->getName();
        $pid = getmypid();

        if (!(isset(sshmanager::$_ssh2_client[$eqLogicID]))) {
            [$host, $port, $timeout] = $this->getConnectionData();
            [$username, $keyOrpassword] = $this->getAuthenticationData();
            log::add(__CLASS__, 'debug', "[{$eqLogicName}] >>>> Creating SSH2 client (pid: {$pid}) for eqLogic {$eqLogicID} to {$host}");
            
            try {
                $ssh2 = new SSH2($host, $port, $timeout);
            } catch (Exception $e) {
                log::add(__CLASS__, 'error', "[{$eqLogicName}] >>>> SSH2 Exception :: " . $e->getMessage());
                throw $e;
            }

            try {
                if (!$ssh2->login($username, $keyOrpassword)) {
                    log::add(__CLASS__, 'error', "[{$eqLogicName}] >>>> SSH2 Login failed for {$username}@{$host}:{$port}");
                    throw new RuntimeException("SSH2 Login failed for {$username}@{$host}:{$port}; please check username and password or ssh key.");
                }
            } catch (RuntimeException $ex) {
                log::add(__CLASS__, 'error', '[' . $eqLogicName . '] SSH2 Login Exception :: ' . $ex->getMessage());
                throw $ex;
            } catch (\Throwable $th) {
                log::add(__CLASS__, 'error', "[{$eqLogicName}] SSH2 General Exception :: " . $th->getMessage());
                throw $th;
            }
            log::add(__CLASS__, 'debug', "[{$eqLogicName}] >>>> SSH2 :: Connected and authenticated");
            sshmanager::$_ssh2_client[$eqLogicID] = $ssh2;

        } else {
            // log::add(__CLASS__, 'debug', "[" . $eqLogicName . "] >>>> Existing SSH2 client (pid: {$pid}) for eqLogic {$eqLogicID}");
        }
        return sshmanager::$_ssh2_client[$eqLogicID];
    }

    private function internalCheckSFTPConnection() {
        try {
            $sftp = $this->getSFTPClient();
            return $sftp->isConnected() && $sftp->isAuthenticated();
        } catch (RuntimeException $ex) {
            // log::add(__CLASS__, 'error', "[{$this->getName()}] SFTP CheckConnection Exception :: {$ex->getMessage()}");
            return false;
        } catch (\Throwable $th) {
            log::add(__CLASS__, 'error', "[{$this->getName()}] SFTP General CheckConnection Exception :: " . $th->getMessage());
            return false;
        }
    }

    private function internalCheckSSHConnection() {
        try {
            $ssh2 = $this->getSSH2Client();
            return $ssh2->isConnected() && $ssh2->isAuthenticated();
        } catch (RuntimeException $ex) {
            // log::add(__CLASS__, 'error', "[{$this->getName()}] SSH2 CheckConnection Exception :: {$ex->getMessage()}");
            return false;
        } catch (\Throwable $th) {
            log::add(__CLASS__, 'error', "[{$this->getName()}] SSH2 General CheckConnection Exception :: " . $th->getMessage());
            return false;
        }
    }

    private function internalExecuteCmd(string $command, $cmdName = '') {
        try {
            $ssh2 = $this->getSSH2Client();
        } catch (RuntimeException $ex) {
            log::add(__CLASS__, 'error', "[{$this->getName()}] ExecCmd RunTimeEx :: {$ex->getMessage()}");
            throw new RuntimeException("ExecCmd RunTimeEx :: {$ex->getMessage()}");
        } catch (\Throwable $th) {
            log::add(__CLASS__, 'error', "[{$this->getName()}] ExecCmd General Exception :: " . $th->getMessage());
            throw $th;
        }
        
        $result = '';
        $exNeedReset = false;

        try {
            $result = $ssh2->exec($command);

            if (!$ssh2->isConnected()) {
                log::add(__CLASS__, 'debug', '[' . $this->getName() . '] ' . (!empty($cmdName) ? $cmdName : 'Cmd') . ' :: ' . str_replace("\r\n", "\\r\\n", $command));
                log::add(__CLASS__, 'error', '[' . $this->getName() . '] ' . (!empty($cmdName) ? $cmdName : 'Cmd') . ' :: Disconnected');
                $result = '';
                $ssh2->disconnect();
                return $result;
            }

            if ($ssh2->isTimeout()) {
                log::add(__CLASS__, 'debug', '[' . $this->getName() . '] ' . (!empty($cmdName) ? $cmdName : 'Cmd') . ' :: ' . str_replace("\r\n", "\\r\\n", $command));
                log::add(__CLASS__, 'error', '[' . $this->getName() . '] ' . (!empty($cmdName) ? $cmdName : 'Cmd') . ' :: Timeout');
                $result = '';
                $exNeedReset = true;
                return $result;
            }
            
            // TODO ATTENTION, une variable qui renvoit 0 est considérée comme vide.

            if (!empty($result)) {
                $result = trim($result);
                //TODO: '\n' should be escaped from $result before logging
                log::add(__CLASS__, 'debug', '[' . $this->getName() . '] ' . (!empty($cmdName) ? $cmdName : 'Cmd') . ' :: ' . str_replace("\r\n", "\\r\\n", $command));
                log::add(__CLASS__, 'debug', '[' . $this->getName() . '] ' . (!empty($cmdName) ? $cmdName : 'Cmd') . ' Result :: ' . $result);
            } else {
                log::add(__CLASS__, 'debug', '[' . $this->getName() . '] ' . (!empty($cmdName) ? $cmdName : 'Cmd') . ' :: ' . str_replace("\r\n", "\\r\\n", $command));
                log::add(__CLASS__, 'debug', '[' . $this->getName() . '] ' . (!empty($cmdName) ? $cmdName : 'Cmd') . ' :: Empty Result');
            }
        } catch (RuntimeException $ex) {
            log::add(__CLASS__, 'debug', '[' . $this->getName() . '] ' . (!empty($cmdName) ? $cmdName : 'Cmd') . ' :: ' . str_replace("\r\n", "\\r\\n", $command));
            log::add(__CLASS__, 'error', '[' . $this->getName() . '] ' . (!empty($cmdName) ? $cmdName : 'Cmd') . ' RunTimeEx :: ' . $ex->getMessage());
            
            log::add(__CLASS__, 'debug', '['. $this->getName() .'] ' . (!empty($cmdName) ? $cmdName : 'Cmd') . ' RuntimeEx LastError :: ' . $ssh2->getLastError());
			log::add(__CLASS__, 'debug', '['. $this->getName() .'] ' . (!empty($cmdName) ? $cmdName : 'Cmd') . ' RuntimeEx Logs ::' . "\r\n" . $ssh2->getLog());
            
            $exNeedReset = true;
            throw new SSHException($ex->getMessage(), $ssh2->getLastError(), $ssh2->getLog());

        } catch (\Throwable $th) {
            log::add(__CLASS__, 'debug', '[' . $this->getName() . '] ' . (!empty($cmdName) ? $cmdName : 'Cmd') . ' :: ' . str_replace("\r\n", "\\r\\n", $command));
            log::add(__CLASS__, 'error', '[' . $this->getName() . '] ' . (!empty($cmdName) ? $cmdName : 'Cmd') . ' Exception :: ' . $th->getMessage());
            
            $exNeedReset = true;
            throw $th;
        } finally {
            if ($exNeedReset) {
                try {
                    $ssh2->reset();
                    
                    log::add(__CLASS__, 'debug', '[' . $this->getName() . '] ' . (!empty($cmdName) ? $cmdName : 'Cmd') . ' :: Reset Connection');
                } catch (Exception $ex) {
                    log::add(__CLASS__, 'error', '[' . $this->getName() . '] ' . (!empty($cmdName) ? $cmdName : 'Cmd') . ' Reset Exception :: ' . $ex->getMessage());
                }
            }
        }
        return $result;
    }

    public function preInsert() {
        if ($this->getConfiguration(self::CONFIG_AUTH_METHOD) == '') {
            $this->setConfiguration(self::CONFIG_AUTH_METHOD, self::DEFAULT_AUTH_METHOD);
        }
    }

    public function postSave() {
        $refresh = $this->getCmd(null, 'refresh');
        if (!is_object($refresh)) {
            $refresh = new sshmanagerCmd();
            $refresh->setLogicalId('refresh');
            $refresh->setIsVisible(1);
            $refresh->setName(__('Rafraichir', __FILE__));
            $refresh->setConfiguration('cmdType', 'refreshAll');
            $refresh->setType('action');
            $refresh->setSubType('other');
            $refresh->setEqLogic_id($this->getId());
            $refresh->save();
        }

        if (trim($this->getConfiguration('autorefresh')) != '') {
            log::add(__CLASS__, 'debug', '[' . $this->getName() . '] cronEqLogic (AutoRefresh) :: ' . $this->getConfiguration('autorefresh'));

            $cron = cron::byClassAndFunction(__CLASS__, 'cronEqLogic', array('SSHManager_Id' => intval($this->getId())));
            if (!is_object($cron)) {
                $cron = new cron();
                $cron->setClass(__CLASS__);
                $cron->setFunction('cronEqLogic');
                $cron->setOption(array('SSHManager_Id' => intval($this->getId())));
                $cron->setDeamon(0);
            }
            if ($this->getIsEnable()) {
                $cron->setEnable(1);
            } else {
                $cron->setEnable(0);
            }

            $_cronPattern = $this->getConfiguration('autorefresh');
            $cron->setSchedule($_cronPattern);

            if ($_cronPattern == '* * * * *') {
                $cron->setTimeout(1);
                log::add(__CLASS__, 'debug', '[' . $this->getName() . '] cronEqLogic Timeout :: 1min');
            } else {
                $_ExpMatch = array();
                $_ExpResult = preg_match('/^([0-9,]+|\*)\/([0-9]+)/', $_cronPattern, $_ExpMatch);
                if ($_ExpResult === 1) {
                    $cron->setTimeout(intval($_ExpMatch[2]));
                    log::add(__CLASS__, 'debug', '[' . $this->getName() . '] cronEqLogic Timeout :: '. $_ExpMatch[2] .'min');
                } else {
                    $cron->setTimeout(15);
                    log::add(__CLASS__, 'debug', '[' . $this->getName() . '] cronEqLogic Timeout :: Default 15min');
                }
            }
            $cron->save();
        } else {
            $cron = cron::byClassAndFunction(__CLASS__, 'cronEqLogic', array('SSHManager_Id' => intval($this->getId())));
            if (is_object($cron)) {
                $cron->remove();
                log::add(__CLASS__, 'debug', '[' . $this->getName() . '] Remove cronEqLogic');
            }
        }

        if ($this->getIsEnable() == 1 && config::byKey('refreshOnSave', 'sshmanager', '1') == '1') {
            log::add(__CLASS__, 'debug', '[' . $this->getName() . '] ' . __('Refresh (postSave) de l\'équipement', __FILE__));
            $this->refreshAllInfo();
        } else {
            /* log::add(__CLASS__, 'debug', '[' . $this->getName() . '] ' . __('Pas de Refresh (postSave) de l\'équipement', __FILE__)); */
        }
    }

    public function refreshAllInfo() {
        $isConnected = $this->internalCheckSSHConnection();
        if (!$isConnected) {
            log::add(__CLASS__, 'error', '[' . $this->getName() . '][RefreshAllInfo] ' . __('Connexion SSH :: KO', __FILE__));
            return;
        }
        /** @var sshmanagerCmd */
        foreach ($this->getCmd('info') as $cmd) {
            if ($cmd->getConfiguration('autorefresh', 1) != 1) {
                continue;
            }
            try {
                $cmd->refreshInfo();
            } catch (Exception $exc) {
                log::add(__CLASS__, 'error', sprintf(__("%s refreshAllInfo Exception :: %s", __FILE__), $cmd->getHumanName(), $exc->getMessage()));
            }
        }
    }

    public static function cronCmd($_options) {
        $cmd = cmd::byId($_options['cmd_id']);
        if (is_object($cmd)) {
            try {
                $cmd->execute();
            } catch (Exception $exc) {
                log::add(__CLASS__, 'error', sprintf(__("%s cronCmd :: %s", __FILE__), $cmd->getHumanName(), $exc->getMessage()));
            }
        }
    }

    public static function cronEqLogic($_options) {
        $eqLogic = eqLogic::byId($_options['SSHManager_Id']);
        if (is_object($eqLogic)) {
            try {
                $eqLogic->refreshAllInfo();
            } catch (Exception $exc) {
                log::add(__CLASS__, 'error', sprintf(__("[%s] cronEqLogic Exception :: %s", __FILE__), $eqLogic->getName(), $exc->getMessage()));
            }
        }
    }

    public function preRemove() {
        $cron = cron::byClassAndFunction(__CLASS__, 'cronEqLogic', array('SSHManager_Id' => intval($this->getId())));
        if (is_object($cron)) {
            $cron->remove();
        }
    }
}

class sshmanagerCmd extends cmd {
    public function dontRemoveCmd() {
        return ($this->getLogicalId() == 'refresh');
    }

    public function refreshInfo() {
        if ($this->getType() != 'info' || trim($this->getConfiguration('ssh-command')) == '') {
            log::add(get_class($this->getEqLogic()), 'warning', '[' . $this->getEqLogic()->getName() . '][' . $this->getName() . '] Refresh :: Type not info or Command empty');
            return;
        }
        $this->getEqLogic()->checkAndUpdateCmd($this, $this->execute());
    }

    public function preSave() {
        //TODO : delete this function once migration is done
        if ($this->getConfiguration('ssh-commands') != '') {
            $this->setConfiguration('ssh-command', $this->getConfiguration('ssh-commands'));
            $this->setConfiguration('ssh-commands', null);
        }
    }

    public function postSave() {
        if ($this->getConfiguration('cmdType') == 'refresh') {
            if (trim($this->getConfiguration('cmdCronRefresh')) != '') {
                log::add(get_class($this->getEqLogic()), 'debug', '[' . $this->getEqLogic()->getName() . '][' . $this->getName() . '] cmdCronRefresh :: ' . $this->getConfiguration('cmdCronRefresh'));
                
                $cron = cron::byClassAndFunction(get_class($this->getEqLogic()), 'cronCmd', array('cmd_id' => intval($this->getId())));
                if (!is_object($cron)) {
                    $cron = new cron();
                    $cron->setClass(get_class($this->getEqLogic()));
                    $cron->setFunction('cronCmd');
                    $cron->setOption(array('cmd_id' => intval($this->getId())));
                    $cron->setDeamon(0);
                }
                if ($this->getEqLogic()->getIsEnable()) {
                    $cron->setEnable(1);
                } else {
                    $cron->setEnable(0);
                }
    
                $_cronPattern = $this->getConfiguration('cmdCronRefresh');
                $cron->setSchedule($_cronPattern);
    
                if ($_cronPattern == '* * * * *') {
                    $cron->setTimeout(1);
                    log::add(get_class($this->getEqLogic()), 'debug', '[' . $this->getEqLogic()->getName() . '][' . $this->getName() . '] cmdCronRefresh Timeout :: 1min');
                } else {
                    $_ExpMatch = array();
                    $_ExpResult = preg_match('/^([0-9,]+|\*)\/([0-9]+)/', $_cronPattern, $_ExpMatch);
                    if ($_ExpResult === 1) {
                        $cron->setTimeout(intval($_ExpMatch[2]));
                        log::add(get_class($this->getEqLogic()), 'debug', '[' . $this->getEqLogic()->getName() . '][' . $this->getName() . '] cmdCronRefresh Timeout :: '. $_ExpMatch[2] .'min');
                    } else {
                        $cron->setTimeout(15);
                        log::add(get_class($this->getEqLogic()), 'debug', '[' . $this->getEqLogic()->getName() . '][' . $this->getName() . '] cmdCronRefresh Timeout :: Default 15min');
                    }
                }
                $cron->save();
            } else {
                $cron = cron::byClassAndFunction(get_class($this->getEqLogic()), 'cronCmd', array('cmd_id' => intval($this->getId())));
                if (is_object($cron)) {
                    $cron->remove();
                    log::add(get_class($this->getEqLogic()), 'debug', '[' . $this->getEqLogic()->getName() . '][' . $this->getName() . '] Remove cronCmd');
                }
            }
        } else {
            $cron = cron::byClassAndFunction(get_class($this->getEqLogic()), 'cronCmd', array('cmd_id' => intval($this->getId())));
            if (is_object($cron)) {
                $cron->remove();
                log::add(get_class($this->getEqLogic()), 'debug', '[' . $this->getEqLogic()->getName() . '][' . $this->getName() . '] Not Refresh Type :: Remove cronCmd');
            }
        }
        
        
    }

    public function preRemove() {
        $cron = cron::byClassAndFunction(get_class($this->getEqLogic()), 'cronCmd', array('cmd_id' => intval($this->getId())));
        if (is_object($cron)) {
            $cron->remove();
            log::add(get_class($this->getEqLogic()), 'debug', '[' . $this->getEqLogic()->getName() . '][' . $this->getName() . '] preRemove cronCmd');
        }
    }

    public function execute($_options = null) {
        if ($this->getLogicalId() == 'refresh') {
            /** @var sshmanager */
            $eqLogic = $this->getEqLogic();
            $eqLogic->refreshAllInfo();
            return;
        } elseif ($this->getConfiguration('cmdType') == 'refresh') {
            if ($this->getConfiguration('cmdToRefresh') != '') {
                $cmd = cmd::byId($this->getConfiguration('cmdToRefresh'));
                if (is_object($cmd)) {
                    log::add(get_class($this->getEqLogic()), 'info', '[' . $this->getEqLogic()->getName() . '][' . $cmd->getName() . '] ' . __('Refresh de la commande', __FILE__));
                    $cmd->refreshInfo();
                    return;
                }   
            }   
        }

        $command = $this->getConfiguration('ssh-command');
        $cmd_name = $this->getName();

        if ($_options != null) {
            if ($this->getType() == 'action') {
                switch ($this->getSubType()) {
                    case 'slider':
                        $command = str_replace('#slider#', $_options['slider'], $command);
                        break;
                    case 'color':
                        $command = str_replace('#color#', $_options['color'], $command);
                        break;
                    case 'select':
                        $command = str_replace('#select#', $_options['select'], $command);
                        break;
                    case 'message':
                        $replace = array('#title#', '#message#');
                        $replaceBy = array($_options['title'], $_options['message']);
                        if ($_options['message'] == '' && $_options['title'] == '') {
                            throw new Exception(__('Le message et le sujet ne peuvent pas être vide', __FILE__));
                        }
                        $command = str_replace($replace, $replaceBy, $command);
                        break;
                }
            }
        }
        $result = sshmanager::executeCmds($this->getEqLogic_id(), $command, $cmd_name);
        if ($this->getType() == 'info') {
            return $result; //TODO: what to do with '\n' in result?
        }
    }
}
