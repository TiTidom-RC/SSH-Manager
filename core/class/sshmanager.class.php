<?php

require_once __DIR__  . '/../../../../core/php/core.inc.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use phpseclib3\Net\SSH2;
use phpseclib3\Crypt\PublicKeyLoader;

if (!defined('NET_SSH2_LOGGING')) {
	define('NET_SSH2_LOGGING', 2);
}

class sshmanager extends eqLogic {
    public function decrypt() {
        // TODO: Update decrypt() method.
		$this->setConfiguration('user', utils::decrypt($this->getConfiguration('user')));
		$this->setConfiguration('password', utils::decrypt($this->getConfiguration('password')));
		$this->setConfiguration('ssh-key', utils::decrypt($this->getConfiguration('ssh-key')));
		$this->setConfiguration('ssh-passphrase', utils::decrypt($this->getConfiguration('ssh-passphrase')));
	}
	
	public function encrypt() {
        // TODO: Update encrypt() method.
		$this->setConfiguration('user', utils::encrypt($this->getConfiguration('user')));
		$this->setConfiguration('password', utils::encrypt($this->getConfiguration('password')));
		$this->setConfiguration('ssh-key', utils::encrypt($this->getConfiguration('ssh-key')));
		$this->setConfiguration('ssh-passphrase', utils::encrypt($this->getConfiguration('ssh-passphrase')));
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
		}
		catch (\Exception $e) {
			log::add(__CLASS__, 'warning', '[VERSION] Get ERROR :: ' . $e->getMessage());
		}
		log::add(__CLASS__, 'info', '[VERSION] PluginVersion :: ' . $pluginVersion);
        return $pluginVersion;
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
                log::add(__CLASS__, 'debug', '['. $equipement .'][connectSSH] Connexion SSH :: IP/Port: ' . $ip . ':' . $port . ' / Timeout: ' . $timeout);
            } catch (Exception $e) {
                log::add(__CLASS__, 'error', '['. $equipement .'][connectSSH] Connexion SSH :: '. $e->getMessage());
                $cnx_ssh = 'KO';
            }

            if ($cnx_ssh != 'KO') {
                if ($confLocalOrRemote == 'deporte-key') {
                    try {
                        $keyOrPwd = PublicKeyLoader::load($sshkey, $sshpassphrase);
                        log::add(__CLASS__, 'debug', '['. $equipement .'][connectSSH] PublicKeyLoader :: OK');
                    } catch (Exception $e) {
                        log::add(__CLASS__, 'error', '['. $equipement .'][connectSSH] PublicKeyLoader :: '. $e->getMessage());
                        $keyOrPwd = '';
                    }
                }
                else {
                    $keyOrPwd = $pass;
                    log::add(__CLASS__, 'debug', '['. $equipement .'][connectSSH] Authentification SSH par Mot de passe');
                }

                try {
                    if (!$sshconnection->login($user, $keyOrPwd)) {
                        log::add(__CLASS__, 'error', '['. $equipement .'][connectSSH] Login ERROR :: ' . $user);
                        $cnx_ssh = 'KO';
                    }
                } catch (Exception $e) {
                    log::add(__CLASS__, 'error', '['. $equipement .'][connectSSH] Authentification SSH :: '. $e->getMessage());
                    $cnx_ssh = 'KO';
                }

                try {
                    if ($sshconnection->isConnected()) {
                        log::add(__CLASS__, 'debug', '['. $equipement .'][connectSSH] Connexion SSH (isConnected) :: OK');
                        if ($sshconnection->isAuthenticated()) {
                            log::add(__CLASS__, 'debug', '['. $equipement .'][connectSSH] Connexion SSH (isAuthenticated) :: OK');
                        }
                        else {
                            log::add(__CLASS__, 'error', '['. $equipement .'][connectSSH] Connexion SSH (isAuthenticated) :: KO');
                            log::add(__CLASS__, 'debug', '['. $equipement .'][connectSSH] Connexion SSH LastError :: ' . $sshconnection->getLastError());
                            $cnx_ssh = 'KO';
                        }
                    } else {
                        log::add(__CLASS__, 'error', '['. $equipement .'][connectSSH] Connexion SSH (isConnected) :: KO');
                        log::add(__CLASS__, 'debug', '['. $equipement .'][connectSSH] Connexion SSH LastError :: ' . $sshconnection->getLastError());
                        $cnx_ssh = 'KO';
                    }
                } catch (Exception $e) {
                    log::add(__CLASS__, 'error', '['. $equipement .'][connectSSH] Connexion SSH :: '. $e->getMessage());
                    log::add(__CLASS__, 'debug', '['. $equipement .'][connectSSH] Connexion SSH Log :: ' . $sshconnection->getLog());
                    $cnx_ssh = 'KO';
                }

                // Fin de la connexion SSH
                if ($cnx_ssh != 'KO') {
                    $cnx_ssh = 'OK';
                    log::add(__CLASS__, 'debug', '['. $equipement .'][connectSSH] Connexion SSH (cnx_ssh) :: OK');
                    
                    $result = array();
                    foreach ($_commands as $command) {
                        $result[$command] = $sshconnection->exec($command);
                    }
                    // Suite du code à exécuter si la connexion SSH est OK
                }
            }
        }

    }
}

class sshmanagerCmd extends cmd {
}
