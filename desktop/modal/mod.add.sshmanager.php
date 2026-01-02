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

?>

<div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
    <div role="tabpanel" class="tab-pane active">
        <legend id="sshModalLegend"><i class="fas fa-list-alt"></i> {{Configuration de l'équipement SSH Manager}}</legend>
        <br />
        <div class="col-sm-12">
            <form class="form-horizontal">
                <fieldset>
                    <input type="hidden" class="eqLogicAttr" data-l1key="id" />
                    <div class="form-group">
                        <label class="col-md-4 control-label">{{Nom de l'équipement}}</label>
                        <div class="col-md-6">
                            <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}" />
                        </div>
                    </div>

                    <br />
                    <?php include_file('desktop', 'params.sshmanager.inc', 'php', 'sshmanager'); ?>
                    <br />
                </fieldset>
            </form>
        </div>

    </div>
</div>

<?php
include_file('desktop', 'mod.add.sshmanager', 'js', 'sshmanager');
?>