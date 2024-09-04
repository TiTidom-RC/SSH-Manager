<?php

require_once __DIR__  . '/../../../../core/php/core.inc.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use phpseclib3\Net\SSH2;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SFTP;

if (!defined('NET_SSH2_LOGGING')) {
    define('NET_SSH2_LOGGING', 2);
}

class SSHConnectException extends \RuntimeException {
}

class sshmanager extends eqLogic {

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

    const DEFAULT_TIMEOUT = 10;
    const DEFAULT_PORT = 22;

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

    // Methods used by client plugins

    public static function getRemoteHosts() {
        $hosts = [];
        foreach (eqLogic::byType(__CLASS__, true) as $sshmanager) {
            $hosts[$sshmanager->getId()] = $sshmanager->getName();
        }
        return $hosts;
    }

    /**
     * execute ssh cmd on the remote host provided by hostId
     *
     * @param int $hostId
     * @param array $commands
     * @return array $results
     */
    public static function executeCmds($hostId, array $commands) {
        /** @var sshmanager */
        $sshmanager = eqLogic::byId($hostId);
        if (!is_object($sshmanager)) {
            throw new Exception('Invalid host id');
        }
        log::add(__CLASS__, 'debug', "executeCmds on {$sshmanager->getName()}");
        return $sshmanager->internalExecuteCmds($commands);
    }

    public static function sendFile(int $hostId, string $localFile, string $remoteFile) {
        /** @var sshmanager */
        $sshmanager = eqLogic::byId($hostId);
        if (!is_object($sshmanager)) {
            throw new Exception('Invalid host id');
        }
        return $sshmanager->internalSendFile($localFile, $remoteFile);
    }

    public static function getFile(int $hostId, string $remoteFile, string $localFile) {
        /** @var sshmanager */
        $sshmanager = eqLogic::byId($hostId);
        if (!is_object($sshmanager)) {
            throw new Exception('Invalid host id');
        }
        return $sshmanager->internalGetFile($remoteFile, $localFile);
    }

    // end methods used by client plugins

    private function getConnectionData() {
        /** @var string */
        $host = $this->getConfiguration(self::CONFIG_HOST);
        /** @var int */
        $port = $this->getConfiguration(self::CONFIG_PORT, self::DEFAULT_PORT);
        /** @var int */
        $timeout = $this->getConfiguration(self::CONFIG_TIMEOUT, self::DEFAULT_TIMEOUT);

        if ($host == "") {
            log::add(__CLASS__, 'error', 'Host name or IP not defined');
            throw new RuntimeException('Host name or IP not defined');
        }

        return [$host, $port, $timeout];
    }

    private function getAuthenticationData() {

        /** @var string */
        $username = $this->getConfiguration(self::CONFIG_USERNAME);
        if ($username == "") {
            log::add(__CLASS__, 'error', 'username not defined');
            throw new RuntimeException('username not defined');
        }

        /** @var string */
        $authmethod = $this->getConfiguration(self::CONFIG_AUTH_METHOD, self::DEFAULT_AUTH_METHOD);

        switch ($authmethod) {
            case self::CONFIG_PASSWORD:
                $keyOrpassword = $this->getConfiguration(self::CONFIG_PASSWORD);
                if ($keyOrpassword == "") {
                    log::add(__CLASS__, 'error', 'Password not defined');
                    throw new RuntimeException('Password not defined');
                }
                break;
            case self::AUTH_METHOD_SSH_KEY:
                $sshkey = $this->getConfiguration(self::CONFIG_SSH_KEY);
                $sshpassphrase = $this->getConfiguration(self::CONFIG_SSH_PASSPHRASE);
                if ($sshkey == "") {
                    log::add(__CLASS__, 'error', 'SSH key not defined');
                    throw new RuntimeException('SSH key not defined');
                }
                try {
                    $keyOrpassword = PublicKeyLoader::load($sshkey, $sshpassphrase);
                } catch (\phpseclib3\Exception\NoKeyLoadedException $ex) {
                    log::add(__CLASS__, 'error', $ex->getMessage());
                    throw $ex;
                }

                break;
            case self::AUTH_METHOD_AGENT:
                //TODO: check if agent auth could be usefull?
                throw new RuntimeException("Unsupported auth method: {$authmethod}");
                break;
            default:
                throw new RuntimeException("Unsupported auth method: {$authmethod}");
        }
        return [$username, $keyOrpassword];
    }

    private function internalSendFile(string $localFile, string $remoteFile) {
        [$host, $port, $timeout] = $this->getConnectionData();
        [$username, $keyOrpassword] = $this->getAuthenticationData();

        $sftp = new SFTP($host, $port, $timeout);
        if ($sftp->login($username, $keyOrpassword)) {
            log::add(__CLASS__, 'debug', "send file to {$host}");
            return $sftp->put($remoteFile, $localFile, SFTP::SOURCE_LOCAL_FILE);
        }
        log::add(__CLASS__, 'debug', "login failed, could not put file {$remoteFile}");
        return false;
    }

    private function internalGetFile(string $remoteFile, string $localFile) {
        [$host, $port, $timeout] = $this->getConnectionData();
        [$username, $keyOrpassword] = $this->getAuthenticationData();

        $sftp = new SFTP($host, $port, $timeout);
        if ($sftp->login($username, $keyOrpassword)) {
            log::add(__CLASS__, 'debug', "get file from {$host}");
            return $sftp->get($remoteFile, $localFile);
        }
        log::add(__CLASS__, 'debug', "login failed, could not get file {$remoteFile}");
        return false;
    }

    private function internalExecuteCmds(array $commands) {
        [$host, $port, $timeout] = $this->getConnectionData();
        [$username, $keyOrpassword] = $this->getAuthenticationData();

        $ssh = new SSH2($host, $port, $timeout);
        try {
            if (!$ssh->login($username, $keyOrpassword)) {
                throw new SSHConnectException("[{$this->getName()}] Login failed for {$username}@{$host}:{$port}; please check username and password or ssh key.");
            }

            if (!$ssh->isConnected()) {
                throw new SSHConnectException("[{$this->getName()}] Connexion failed:" . $ssh->getLastError());
            }

            if (!$ssh->isAuthenticated()) {
                throw new SSHConnectException("[{$this->getName()}] Authentication failed:" . $ssh->getLastError());
            }
        } catch (SSHConnectException $ex) {
            log::add(__CLASS__, 'error', $ex->getMessage());
            throw $ex;
        } catch (\Throwable $th) {
            log::add(__CLASS__, 'error', "[{$this->getName()}] General exception during connection: " . $th->getMessage() . " - log: " . $ssh->getLog());
            log::add(__CLASS__, 'error', "[{$this->getName()}] log: " . $ssh->getLog());
            throw $th;
        }

        log::add(__CLASS__, 'debug', "[{$this->getName()}] Connected and authenticated");

        $results = [];
        foreach ($commands as $cmd) {
            $cmd = str_replace("{user}", $username, $cmd);
            $result = $ssh->exec($cmd);
            log::add(__CLASS__, 'debug', "SSH exec:{$cmd} => {$result}");
            $results[] = explode("\n", $result);
        }

        return $results;
    }

    public function preInsert() {
        $this->setConfiguration(self::CONFIG_AUTH_METHOD, self::DEFAULT_AUTH_METHOD);
    }
}

class sshmanagerCmd extends cmd {
}
