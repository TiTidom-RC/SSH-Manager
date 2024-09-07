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
    private $_log;  // log of the SSH2 object

    public function __construct($message, $log = '') {
        parent::__construct($message);
        $this->_log = $log;
    }

    public function getLog() {
        return $this->_log;
    }
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

    public static function cron() {
        /** @var sshmanager */
        foreach (self::byType(__CLASS__, true) as $sshmanager) {
            $autorefresh = $sshmanager->getConfiguration('autorefresh');
            if ($autorefresh == '')  continue;
            try {
                $cron = new Cron\CronExpression($autorefresh, new Cron\FieldFactory);
                if ($cron->isDue()) {
                    $sshmanager->refreshAllInfo();
                }
            } catch (Exception $e) {
                log::add(__CLASS__, 'error', __('Expression cron non valide pour ', __FILE__) . $sshmanager->getName() . ' : ' . $autorefresh);
            }
        }
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
     * check ssh connection on the remote host provided by hostId
     *
     * @param int $hostId
     * @return bool $status
     */
    public static function checkConnection($hostId) {
        /** @var sshmanager */
        $sshmanager = eqLogic::byId($hostId);
        if (!is_object($sshmanager)) {
            throw new Exception('Invalid host Id');
        }
        log::add(__CLASS__, 'debug', "Check SSH Connection on {$sshmanager->getName()}");
        return $sshmanager->internalCheckConnection();
    }

    /**
     * execute ssh cmd on the remote host provided by hostId
     *
     * @param int $hostId
     * @param array|string $commands
     * @return array|string $results
     */
    public static function executeCmds($hostId, $commands) {
        /** @var sshmanager */
        $sshmanager = eqLogic::byId($hostId);
        if (!is_object($sshmanager)) {
            throw new Exception('Invalid host Id');
        }
        log::add(__CLASS__, 'debug', "executeCmds on {$sshmanager->getName()}");

        if (is_array($commands)) {
            $results = [];
            foreach ($commands as $cmd) {
                $results[] = $sshmanager->internalExecuteCmd($cmd);
            }
            return $results;
        } elseif (is_string($commands)) {
            return $sshmanager->internalExecuteCmd($commands);
        } else {
            throw new Exception('Invalid command type');
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
    public static function sendFile($hostId, string $localFile, string $remoteFile) {
        /** @var sshmanager */
        $sshmanager = eqLogic::byId($hostId);
        if (!is_object($sshmanager)) {
            throw new Exception('Invalid host id');
        }
        return $sshmanager->internalSendFile($localFile, $remoteFile);
    }

    /**
     * get a file from the remote host
     *
     * @param int $hostId
     * @param string $remoteFile - path to the remote file
     * @param string $localFile - path to the local file
     * @return bool - true if the file was received successfully
     */
    public static function getFile($hostId, string $remoteFile, string $localFile) {
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
            throw new RuntimeException(__('Adresse IP ou nom d\'hôte non configuré', __FILE__));
        }

        return [$host, $port, $timeout];
    }

    private function getAuthenticationData() {

        /** @var string */
        $username = $this->getConfiguration(self::CONFIG_USERNAME);
        if ($username == "") {
            log::add(__CLASS__, 'error', 'username not defined');
            throw new RuntimeException(__('Nom d\'utilisateur non configuré', __FILE__));
        }

        /** @var string */
        $authmethod = $this->getConfiguration(self::CONFIG_AUTH_METHOD, self::DEFAULT_AUTH_METHOD);

        switch ($authmethod) {
            case self::CONFIG_PASSWORD:
                $keyOrpassword = $this->getConfiguration(self::CONFIG_PASSWORD);
                if ($keyOrpassword == "") {
                    log::add(__CLASS__, 'error', 'Password not defined');
                    throw new RuntimeException(__('Mot de passe non configuré', __FILE__));
                }
                break;
            case self::AUTH_METHOD_SSH_KEY:
                $sshkey = $this->getConfiguration(self::CONFIG_SSH_KEY);
                $sshpassphrase = $this->getConfiguration(self::CONFIG_SSH_PASSPHRASE);
                if ($sshkey == "") {
                    log::add(__CLASS__, 'error', 'SSH key not defined');
                    throw new RuntimeException(__('Clé SSH non configurée', __FILE__));
                }
                try {
                    $keyOrpassword = PublicKeyLoader::load($sshkey, $sshpassphrase);
                } catch (\phpseclib3\Exception\NoKeyLoadedException $ex) {
                    log::add(__CLASS__, 'error', $ex->getMessage());
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

    /** @var SSH2[] */
    private static $_ssh2_client = [];

    private function getSSH2Client() {
        $eqLogicID = $this->getId();
        $pid = getmypid();

        if (!(isset(sshmanager::$_ssh2_client[$eqLogicID]))) {
            [$host, $port, $timeout] = $this->getConnectionData();
            [$username, $keyOrpassword] = $this->getAuthenticationData();
            log::add(__CLASS__, 'debug', "[{$pid}] Creating SSH2 client for eqLogic {$eqLogicID} to {$host}");
            $ssh2 = new SSH2($host, $port, $timeout);

            try {
                if (!$ssh2->login($username, $keyOrpassword)) {
                    throw new SSHConnectException("[{$this->getName()}] Login failed for {$username}@{$host}:{$port}; please check username and password or ssh key.", $ssh2->getLog());
                }

                if (!$ssh2->isConnected()) {
                    throw new SSHConnectException("[{$this->getName()}] Connection failed:" . $ssh2->getLastError(), $ssh2->getLog());
                }

                if (!$ssh2->isAuthenticated()) {
                    throw new SSHConnectException("[{$this->getName()}] Authentication failed:" . $ssh2->getLastError(), $ssh2->getLog());
                }
            } catch (SSHConnectException $ex) {
                log::add(__CLASS__, 'error', $ex->getMessage());
                throw $ex;
            } catch (\Throwable $th) {
                log::add(__CLASS__, 'error', "[{$this->getName()}] General exception during connection: " . $th->getMessage() . " - log: " . $ssh2->getLog());
                log::add(__CLASS__, 'error', "[{$this->getName()}] log: " . $ssh2->getLog());
                throw $th;
            }

            log::add(__CLASS__, 'debug', "[{$this->getName()}] Connected and authenticated");

            sshmanager::$_ssh2_client[$eqLogicID] = $ssh2;
        } else {
            log::add(__CLASS__, 'debug', "[{$pid}] Existing SSH2 client for eqLogic {$eqLogicID}");
        }
        return sshmanager::$_ssh2_client[$eqLogicID];
    }

    private function internalCheckConnection() {
        try {
            $ssh2 = $this->getSSH2Client();
            return $ssh2->isConnected() && $ssh2->isAuthenticated();
        } catch (SSHConnectException $ex) {
            log::add(__CLASS__, 'error', "[{$this->getName()}] Exception :: {$ex->getMessage()}");
            log::add(__CLASS__, 'debug', "[{$this->getName()}] Exception Log :: {$ex->getLog()}");
            return false;
        } catch (\Throwable $th) {
            log::add(__CLASS__, 'error', $th->getMessage());
            return false;
        }
    }

    private function internalExecuteCmd(string $command) {
        $ssh2 = $this->getSSH2Client();
        $result = $ssh2->exec($command);
        //TODO: '\n' should be escaped from $result before logging
        log::add(__CLASS__, 'debug', "SSH exec:{$command} => {$result}");
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
            $refresh->setConfiguration('cmdType', 'refresh');
            $refresh->setType('action');
            $refresh->setSubType('other');
            $refresh->setEqLogic_id($this->getId());
            $refresh->save();
        }
        if ($this->getIsEnable() == 1 && config::byKey('refreshOnSave', 'sshmanager', '1') == '1') {
            $this->refreshAllInfo();
        }
    }

    public function refreshAllInfo() {
        /** @var sshmanagerCmd */
        foreach ($this->getCmd('info') as $cmd) {
            try {
                $cmd->refreshInfo();
            } catch (Exception $exc) {
                log::add(__CLASS__, 'error', sprintf(__("Erreur pour %s: %s", __FILE__), $cmd->getHumanName(), $exc->getMessage()));
            }
        }
    }
}

class sshmanagerCmd extends cmd {
    public function dontRemoveCmd() {
        return ($this->getLogicalId() == 'refresh');
    }

    public function refreshInfo() {
        if ($this->getType() != 'info' || trim($this->getConfiguration('ssh-command')) == '') {
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

    public function execute($_options = null) {
        if ($this->getLogicalId() == 'refresh') {
            /** @var sshmanager */
            $eqLogic = $this->getEqLogic();
            $eqLogic->refreshAllInfo();
            return;
        } elseif ($this->getConfiguration('cmdType') == 'refresh') {
            if ($this->getValue() != '') {
                $cmd = sshmanagerCmd::byId($this->getValue());
                if (is_object($cmd)) {
                    $eqLogic = $this->getEqLogic();
                    $cmd->refreshInfo();
                    log::add(get_class($eqLogic), 'info', '[' . $eqLogic->getName() . ']' . __('Refresh de la commande : ', __FILE__) . $cmd->getName());
                    return;
                }   
            }   
        }

        $command = $this->getConfiguration('ssh-command');

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
        $result = sshmanager::executeCmds($this->getEqLogic_id(), $command);
        if ($this->getType() == 'info') {
            return $result; //TODO: what to do with '\n' in result?
        }
    }
}
