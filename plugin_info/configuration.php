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

include_file('core', 'authentification', 'php');
if (!isConnect()) {
    include_file('desktop', '404', 'php');
    die();
}

$_branchSSHManager = config::byKey('pluginBranch', 'sshmanager', 'N/A');

if (strpos($_branchSSHManager, 'stable') !== false) {
    $_labelBranchSSHM = '<span class="label label-success text-capitalize">' . $_branchSSHManager . '</span>';
} elseif (strpos($_branchSSHManager, 'beta') !== false) {
    $_branchSSHM = '<span class="label label-warning text-capitalize">' . $_branchSSHManager . '</span>';
} elseif (strpos($_branchSSHManager, 'dev') !== false) {
    $_labelBranchSSHM = '<span class="label label-danger text-capitalize">' . $_branchSSHManager . '</span>';
} else {
    $_labelBranchSSHM = '<span class="label label-info">N/A</span>';
}

?>

<form class="form-horizontal">
    <fieldset>
        <div>
            <legend><i class="fas fa-info"></i> {{Plugin}}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label">{{Version}}
                    <sup><i class="fas fa-question-circle tooltips" title="{{Version du plugin à indiquer sur Community}}"></i></sup>
                </label>
                <div class="col-md-1">
                    <input class="configKey form-control" data-l1key="pluginVersion" readonly />
                </div>
                <div class="col-md-1">
                    <?php echo $_labelBranchSSHM ?>
                </div>
            </div>
            <legend><i class="fas fa-list-alt"></i> {{Options}}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label">{{Rafraichir les valeurs à la sauvegarde}}
                    <sup><i class="fas fa-question-circle tooltips" title="{{Cochez cette option pour rafraîchir automatiquement les valeurs des commandes lors de la sauvegarde d'un équipement}}"></i></sup>
                </label>
                <div class="col-lg-1">
                    <input type="checkbox" class="configKey" data-l1key="refreshOnSave" />
                </div>
            </div>
        </div>
    </fieldset>
</form>