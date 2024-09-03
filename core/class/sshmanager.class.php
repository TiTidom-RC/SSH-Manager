<?php

require_once __DIR__  . '/../../../../core/php/core.inc.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use phpseclib3\Net\SSH2;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SFTP;

if (!defined('NET_SSH2_LOGGING')) {
    define('NET_SSH2_LOGGING', 2);
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
            $hosts[] = [
                'id' => $sshmanager->getId(),
                'name' => $sshmanager->getName(),
            ];
        }
        return $hosts;
    }

    public static function executeCmds(int $hostId, array $commands) {
        /** @var sshmanager */
        $sshmanager = eqLogic::byId($hostId);
        if (!is_object($sshmanager)) {
            throw new Exception('Invalid host id');
        }
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
        $port = $this->getConfiguration(self::CONFIG_PORT, 22);
        /** @var int */
        $timeout = $this->getConfiguration(self::CONFIG_TIMEOUT, 10);

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
            case 'sshkey':
                $sshkey = $this->getConfiguration(self::CONFIG_SSH_KEY);
                $sshpassphrase = $this->getConfiguration(self::CONFIG_SSH_PASSPHRASE);
                if ($sshkey == "") {
                    log::add(__CLASS__, 'error', 'SSH key not defined');
                    throw new RuntimeException('SSH key not defined');
                }
                $keyOrpassword = PublicKeyLoader::load($sshkey, $sshpassphrase);
                break;
            case 'agent':
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

        if (!$ssh->login($username, $keyOrpassword)) {
            $error = "Authentification SSH KO";
            log::add(__CLASS__, 'error', $error, 'authKO');
            throw new Exception($error);
        }

        $results = [];
        foreach ($commands as $cmd) {
            $cmd = str_replace("{user}", $username, $cmd);
            $result = $ssh->exec($cmd);
            log::add(__CLASS__, 'debug', "SSH exec:{$cmd} => {$result}");
            $results[] = explode("\n", $result);
        }

        return $results;
    }

    public function execSSH($_commands = array()) {
        // TODO : Méthode extraite du plugin Monitoring, à adapter pour SSHManager.
        $equipement = $this->getName();
        $confLocalOrRemote = $this->getConfiguration('maitreesclave'); // local ou déporté, et si déporté (qui nous intéresse ici), par mot de passe ou par clé

        if (($confLocalOrRemote == 'deporte' || $confLocalOrRemote == 'deporte-key') && $this->getIsEnable()) {
            $ip = $this->getConfiguration('addressip');
            $port = $this->getConfiguration('portssh', 22);
            $timeout = $this->getConfiguration('timeoutssh', 30);
            $user = $this->getConfiguration('user');
            $pass = $this->getConfiguration('password');
            $sshkey = $this->getConfiguration('ssh-key');
            $sshpassphrase = $this->getConfiguration('ssh-passphrase');
            $cnx_ssh = '';


            // Début de la connexion SSH
            try {
                $sshconnection = new SSH2($ip, $port, $timeout);
                log::add(__CLASS__, 'debug', '[' . $equipement . '][connectSSH] Connexion SSH :: IP/Port: ' . $ip . ':' . $port . ' / Timeout: ' . $timeout);
            } catch (Exception $e) {
                log::add(__CLASS__, 'error', '[' . $equipement . '][connectSSH] Connexion SSH :: ' . $e->getMessage());
                $cnx_ssh = 'KO';
            }

            if ($cnx_ssh != 'KO') {
                if ($confLocalOrRemote == 'deporte-key') {
                    try {
                        $keyOrPwd = PublicKeyLoader::load($sshkey, $sshpassphrase);
                        log::add(__CLASS__, 'debug', '[' . $equipement . '][connectSSH] PublicKeyLoader :: OK');
                    } catch (Exception $e) {
                        log::add(__CLASS__, 'error', '[' . $equipement . '][connectSSH] PublicKeyLoader :: ' . $e->getMessage());
                        $keyOrPwd = '';
                    }
                } else {
                    $keyOrPwd = $pass;
                    log::add(__CLASS__, 'debug', '[' . $equipement . '][connectSSH] Authentification SSH par Mot de passe');
                }

                try {
                    if (!$sshconnection->login($user, $keyOrPwd)) {
                        log::add(__CLASS__, 'error', '[' . $equipement . '][connectSSH] Login ERROR :: ' . $user);
                        $cnx_ssh = 'KO';
                    }
                } catch (Exception $e) {
                    log::add(__CLASS__, 'error', '[' . $equipement . '][connectSSH] Authentification SSH :: ' . $e->getMessage());
                    $cnx_ssh = 'KO';
                }

                try {
                    if ($sshconnection->isConnected()) {
                        log::add(__CLASS__, 'debug', '[' . $equipement . '][connectSSH] Connexion SSH (isConnected) :: OK');
                        if ($sshconnection->isAuthenticated()) {
                            log::add(__CLASS__, 'debug', '[' . $equipement . '][connectSSH] Connexion SSH (isAuthenticated) :: OK');
                        } else {
                            log::add(__CLASS__, 'error', '[' . $equipement . '][connectSSH] Connexion SSH (isAuthenticated) :: KO');
                            log::add(__CLASS__, 'debug', '[' . $equipement . '][connectSSH] Connexion SSH LastError :: ' . $sshconnection->getLastError());
                            $cnx_ssh = 'KO';
                        }
                    } else {
                        log::add(__CLASS__, 'error', '[' . $equipement . '][connectSSH] Connexion SSH (isConnected) :: KO');
                        log::add(__CLASS__, 'debug', '[' . $equipement . '][connectSSH] Connexion SSH LastError :: ' . $sshconnection->getLastError());
                        $cnx_ssh = 'KO';
                    }
                } catch (Exception $e) {
                    log::add(__CLASS__, 'error', '[' . $equipement . '][connectSSH] Connexion SSH :: ' . $e->getMessage());
                    log::add(__CLASS__, 'debug', '[' . $equipement . '][connectSSH] Connexion SSH Log :: ' . $sshconnection->getLog());
                    $cnx_ssh = 'KO';
                }

                // Fin de la connexion SSH
                if ($cnx_ssh != 'KO') {
                    $cnx_ssh = 'OK';
                    log::add(__CLASS__, 'debug', '[' . $equipement . '][connectSSH] Connexion SSH (cnx_ssh) :: OK');

                    $result = array();
                    foreach ($_commands as $command) {
                        $result[$command] = $sshconnection->exec($command);
                    }
                    // Suite du code à exécuter si la connexion SSH est OK
                }
            }
        }
    }

    public function preInsert() {
        $this->setConfiguration(self::CONFIG_AUTH_METHOD, self::DEFAULT_AUTH_METHOD);
    }
}

class sshmanagerCmd extends cmd {
}
