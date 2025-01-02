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

try {
    require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
    include_file('core', 'authentification', 'php');

    if (!isConnect('admin')) {
        throw new Exception(__('401 - Accès non autorisé', __FILE__));
    }

    /* Fonction permettant l'envoi de l'entête 'Content-Type: application/json'
     En V3 : indiquer l'argument 'true' pour contrôler le token d'accès Jeedom
     En V4 : autoriser l'exécution d'une méthode 'action' en GET en indiquant le(s) nom(s) de(s) action(s) dans un tableau en argument
    */
    ajax::init(array());

    if (init('action') == 'getRemoteHosts') {
        $remoteHosts = array();
        if (class_exists('sshmanager')) {
            $remoteHosts = sshmanager::getRemoteHosts();
        } 
        ajax::success($remoteHosts);
    }

    if (init('action') == 'getTemplateCommands') {
        $commands = sshmanager::getTemplateCommands();
        ajax::success($commands);
    }
  
    if (init('action') == 'getUsedBy') {
        $return = '';
        $usedBy = sshmanager::customUsedBy('eqLogic', init('eqLogic_id'));
        foreach ($usedBy as $usedByEqLogic) {
            $plugin = plugin::byId($usedByEqLogic->getEqType_name());
		    $return .= '<a href="' . $plugin->getLinkToConfiguration() . '" class="btn btn-xs btn-info"><img class="img-responsive" style="width : 18px;display:inline-block;" src="' . $plugin->getPathImgIcon() . '" /> ' . $plugin->getName(). ' </a>';
            if ($usedByEqLogic->getIsEnable() != 1) {
                $return .= '<a href="' . $usedByEqLogic->getLinkToConfiguration() . '" class="btn btn-xs btn-info">' . $usedByEqLogic->getHumanName(true) . '</a><br/>';
            } else {
                $return .= '<a href="' . $usedByEqLogic->getLinkToConfiguration() . '" class="btn btn-xs btn-primary">' . $usedByEqLogic->getHumanName(true) . '</a><br/>';
            }
        }
        ajax::success($return);
    }

    throw new Exception(__('Aucune méthode correspondante à : ', __FILE__) . init('action'));
    /*     * *********Catch exeption*************** */
} catch (Exception $e) {
    ajax::error(displayException($e), $e->getCode());
}
