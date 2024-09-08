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

if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}

sendVarToJS(sshmanager::CONST_TO_JS);

$commandsJson = sshmanager::COMMANDS_FILEPATH;

if (file_exists($commandsJson)) {
    $commandsJson = file_get_contents($commandsJson, true);
} else {
    log::add('sshmanager', 'error', '[TemplateCmds] Error :: json file not found');
    throw new Exception('Error :: json file not found');
}
$commands = json_decode($commandsJson, true);

sendVarToJS('commands', $commands);

?>

<div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
    <div role="tabpanel" class="tab-pane active">
        <legend><i class="fas fa-list-alt"></i> {{Commandes SSH}} :</legend>
        <br />
        <div class="col-sm-12">
            <form class="form-horizontal">
                <fieldset>
                    <div class="form-group">
                        <label class="col-md-4 control-label">{{Commande}}</label>
                        <div class="col-md-6">
                            <select class="form-control selectCmdTemplate" data-l1key="ssh-select">
                                <?php foreach ($commands as $command): ?>
                                    <option value="<?php echo $command['command']; ?>"><?php echo $command['description']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <br />
                    <div class="form-group">
                        <label class="col-md-4 control-label">{{Nom}}</label>
                        <div class="col-md-6">
                            <input type="text" class="cmdAttr form-control" data-l1key="name" placeholder="{{Nom}}" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">{{Description}}</label>
                        <div class="col-md-6">
                            <textarea rows="2" class="cmdAttr form-control input-sm" data-l1key="description" placeholder="{{Description}}"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-4 control-label">{{Commande}}</label>
                        <div class="col-md-6">
                            <textarea rows="2" class="cmdAttr form-control input-sm" data-l1key="ssh-command" placeholder="{{Commande SSH}}"></textarea>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
</div>

<?php
include_file('desktop', 'mod.commands', 'js', 'sshmanager');
?>