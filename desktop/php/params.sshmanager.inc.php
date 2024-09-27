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

require_once __DIR__  . '/../../../../core/php/core.inc.php';

?>

<div class="form-group">
    <label class="col-md-4 control-label">{{Méthode d'authentification}}</label>
    <div class="col-md-6">
        <select id="pwdorkey" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="<?= sshmanager::CONFIG_AUTH_METHOD ?>">
            <option value="<?= sshmanager::AUTH_METHOD_PASSWORD ?>" selected>{{Mot de Passe}}</option>
            <option value="<?= sshmanager::AUTH_METHOD_SSH_KEY ?>">{{Clé SSH}}</option>
            <option value="<?= sshmanager::AUTH_METHOD_AGENT ?>">{{Agent (non-supporté)}}</option>
        </select>
    </div>
</div>

<div class="form-group">
    <label class="col-md-4 control-label">{{Hôte distant}}
        <sup><i class="fas fa-question-circle tooltips" title="{{Adresse IP ou nom de l'hôte}}"></i></sup>
    </label>
    <div class="col-md-6">
        <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="<?= sshmanager::CONFIG_HOST ?>" type="text" placeholder="{{Saisir l'adresse IP ou le nom}}" />
    </div>
</div>
<div class="form-group">
    <label class="col-md-4 control-label">{{Port SSH}}
        <sup><i class="fas fa-question-circle tooltips" title="{{Laisser vide pour utiliser le port par défaut (recommandé)}}"></i></sup>
    </label>
    <div class="col-md-6">
        <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="<?= sshmanager::CONFIG_PORT ?>" type="text" placeholder="<?= sshmanager::DEFAULT_PORT ?>" />
    </div>
</div>
<div class="form-group">
    <label class="col-md-4 control-label">{{Timeout SSH}}
        <sup><i class="fas fa-question-circle tooltips" title="{{Durée maximale (en secondes) avant expiration de la connexion SSH<br/>Laisser vide pour utiliser le timeout par défaut (recommandé)}}"></i></sup>
    </label>
    <div class="col-md-6">
        <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="<?= sshmanager::CONFIG_TIMEOUT ?>" type="text" placeholder="<?= sshmanager::DEFAULT_TIMEOUT ?>" />
    </div>
</div>
<div class="form-group">
    <label class="col-md-4 control-label">{{Identifiant}}</label>
    <div class="col-md-6">
        <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="<?= sshmanager::CONFIG_USERNAME ?>" type="text" autocomplete="off" placeholder="{{Saisir le nom d'utilisateur}}" />
    </div>
</div>
<div id="pwdorpassphrase">
    <div class="remote-pwd" style="display:block;">
        <div class="form-group">
            <label class="col-md-4 control-label">{{Mot de passe}}</label>
            <div class="col-md-6 input-group">
                <input type="password" autocomplete="new-password" id="ssh-password" class="eqLogicAttr form-control roundedLeft" data-l1key="configuration" data-l2key="<?= sshmanager::CONFIG_PASSWORD ?>" placeholder="{{Saisir le password}}" />
                <span class="input-group-btn">
                    <a class="btn btn-default form-control roundedRight bt_togglePass"><i class="fas fa-eye"></i></a>
                </span>
            </div>
        </div>
    </div>
    <div class="remote-key" style="display:none;">
        <div class="form-group">
            <label class="col-md-4 control-label">{{Passphrase}}
                <sup><i class="fas fa-question-circle tooltips" title="{{Optionnel : Phrase secrète pour la clé SSH}}"></i></sup>
            </label>
            <div class="col-md-6 input-group">
                <input type="password" autocomplete="new-password" id="ssh-passphrase" class="eqLogicAttr form-control roundedLeft" data-l1key="configuration" data-l2key="<?= sshmanager::CONFIG_SSH_PASSPHRASE ?>" placeholder="{{Saisir la passphrase SSH}}" />
                <span class="input-group-btn">
                    <a class="btn btn-default form-control roundedRight bt_togglePass"><i class="fas fa-eye"></i></a>
                </span>
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label">{{Clé SSH}}</label>
            <div class="col-md-8">
                <textarea class="eqLogicAttr form-control" rows="5" data-l1key="configuration" data-l2key="<?= sshmanager::CONFIG_SSH_KEY ?>" placeholder="{{Saisir la clé SSH}}" wrap="off" spellcheck="false"></textarea>
            </div>
        </div>
    </div>
</div>
<?php include_file('desktop', 'params.sshmanager', 'js', 'sshmanager'); ?>