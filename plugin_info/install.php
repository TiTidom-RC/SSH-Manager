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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function sshmanager_install() {
    // Get Plugin Version from plugin_info/info.json
    $pluginVersion = sshmanager::getPluginVersion();
    config::save('pluginVersion', $pluginVersion, 'sshmanager');

    // Get Plugin Branch
    $pluginBranch = sshmanager::getPluginBranch();
    config::save('pluginBranch', $pluginBranch, 'sshmanager');

    message::removeAll('sshmanager');
    message::add('sshmanager', 'Installation du plugin SSH Manager :: v' . $pluginVersion, 'install');

    // Init des valeurs par défaut
    if (config::byKey('refreshOnSave', 'sshmanager') == '') {
        config::save('refreshOnSave', '1', 'sshmanager');
    }
    if (config::byKey('disableUpdateMsg', 'sshmanager') == '') {
        config::save('disableUpdateMsg', '0', 'sshmanager');
    }
}

function sshmanager_update() {
    // Get Plugin Version from plugin_info/info.json
    $pluginVersion = sshmanager::getPluginVersion();
    config::save('pluginVersion', $pluginVersion, 'sshmanager');

    // Get Plugin Branch
    $pluginBranch = sshmanager::getPluginBranch();
    config::save('pluginBranch', $pluginBranch, 'sshmanager');

    if (config::byKey('disableUpdateMsg', 'sshmanager', '0') == '0') {
        message::removeAll('sshmanager');
        message::add('sshmanager', 'Mise à jour du plugin SSH Manager :: v' . $pluginVersion, 'update');
    }

    // Init des valeurs par défaut
    if (config::byKey('refreshOnSave', 'sshmanager') == '') {
        config::save('refreshOnSave', '1', 'sshmanager');
    }
    if (config::byKey('disableUpdateMsg', 'sshmanager') == '') {
        config::save('disableUpdateMsg', '0', 'sshmanager');
    }

    /* Ménage dans les répertoires du plugin si besoin */
    $pluginDir = dirname(__DIR__);
    try {
        $pathsToRemove = array(
            // Accepte fichiers ET répertoires (rm -rf) — ajouter ici les chemins à supprimer à chaque mise à jour
        );
        $cleanupRemoved = 0;
        $cleanupErrors = 0;
        foreach ($pathsToRemove as $path) {
            if (file_exists($path)) {
                $output = array();
                $returnVar = 0;
                exec('rm -rf ' . escapeshellarg($path) . ' 2>&1', $output, $returnVar);
                if ($returnVar !== 0) {
                    $cleanupErrors++;
                    log::add('sshmanager', 'warning', '[CLEANUP_KO] Echec suppression "' . $path . '" (Code: ' . $returnVar . ') : ' . implode(' ', $output));
                } else {
                    $cleanupRemoved++;
                    log::add('sshmanager', 'info', '[CLEANUP_OK] Chemin supprimé : ' . $path);
                }
            }
        }
        $cleanupSummary = count($pathsToRemove) . ' chemin(s) vérifié(s), ' . $cleanupRemoved . ' supprimé(s)';
        if ($cleanupErrors > 0) {
            $cleanupSummary .= ', ' . $cleanupErrors . ' erreur(s)';
        }
        log::add('sshmanager', 'debug', '[CLEANUP] ' . $cleanupSummary);
    } catch (Exception $e) {
        log::add('sshmanager', 'warning', '[CLEANUP_KO] Erreur lors du nettoyage : ' . $e->getMessage());
    }
}

function sshmanager_remove() {
    // Close all active SSH and SFTP connections
    try {
        $count = sshmanager::closeAllConnections();
        log::add('sshmanager', 'info', "[REMOVE] Closed {$count} active connection(s)");
    } catch (Exception $e) {
        log::add('sshmanager', 'error', '[REMOVE] Error closing connections :: ' . $e->getMessage());
    }
    
    // Remove all cron jobs
    foreach (eqLogic::byType('sshmanager', false) as $sshmanager) {
        $cron = cron::byClassAndFunction('sshmanager', 'cronEqLogic', array('SSHManager_Id' => intval($sshmanager->getId())));
        if (is_object($cron)) {
            $cron->remove();
        }
    }
}
