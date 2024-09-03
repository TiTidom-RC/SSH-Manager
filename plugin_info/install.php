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
    $pluginVersion = sshmanager::getPluginVersion();
    config::save('pluginVersion', $pluginVersion, 'sshmanager');
}

function sshmanager_update() {
    $pluginVersion = sshmanager::getPluginVersion();
    config::save('pluginVersion', $pluginVersion, 'sshmanager');

    /* Ménage dans les répertoires du plugin si besoin */
    try {
        $dirToDelete = array(
            /* __DIR__ . '/../ressources' */
        );
        
        $filesToDelete = array(
            /* __DIR__ . '/../plugin_info/packages.json' */
        );

        foreach ($dirToDelete as $dir) {
            log::add('sshmanager', 'debug', '[DIR-CHECK] Vérification de la présence du répertoire ' . $dir);
            if (file_exists($dir)) {
                shell_exec('sudo rm -rf ' . $dir);
                log::add('sshmanager', 'debug', '[DIR-CHECK_OK] Le répertoire ' . $dir . ' a bien été effacé.');
            } else {
                log::add('sshmanager', 'debug', '[DIR-CHECK_NA] Répertoire ' . $dir . ' non trouvé. Aucune action requise.');
            }
        }
        foreach ($filesToDelete as $file) {
            log::add('sshmanager', 'debug', '[FILE-CHECK] Vérification de la présence du fichier : ' . $file);
            if (file_exists($file)) {
                shell_exec('sudo rm -f ' . $file);
                log::add('sshmanager', 'debug', '[FILE-CHECK_OK] Le fichier  ' . $file . ' a bien été effacé.');
            } else {
                log::add('sshmanager', 'debug', '[FILE-CHECK_NA] Fichier ' . $file . ' non trouvé. Aucune action requise.');
            }
        }
    } catch (Exception $e) {
        log::add('sshmanager', 'debug', '[DIR-FILE-CHECK_KO] Exception :: ' . $e->getMessage());
    }
}

function sshmanager_remove() {
    foreach (eqLogic::byType('sshmanager', false) as $sshmanager) {
        $cron = cron::byClassAndFunction('sshmanager', 'pullCustom', array('SSHManager_Id' => intval($sshmanager->getId())));
        if (is_object($cron)) {
            $cron->remove();
        }
    }
}
