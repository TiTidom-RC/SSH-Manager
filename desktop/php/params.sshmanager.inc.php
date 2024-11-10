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
            <label class="col-md-4 control-label">{{Clé SSH}}
            <sup><i class="fas fa-play-circle icon_orange tooltips" title="{{Cliquez ici pour formater la clé SSH en blocs de 64 caractères}}" onclick="reformatSSHKey()"></i></sup>
                <script>
                    function reformatSSHKey() {
                        var sshKeyField = document.querySelector('[data-l2key="<?= sshmanager::CONFIG_SSH_KEY ?>"]');
                        var sshKey = sshKeyField.value;

                        // Regular expressions to match the header and footer of the key
                        var headerRegex = /-----BEGIN [A-Z ]+ KEY-----/;
                        var footerRegex = /-----END [A-Z ]+ KEY-----/;

                        // Extract the header and footer of the key
                        var headerMatch = sshKey.match(headerRegex);
                        var footerMatch = sshKey.match(footerRegex);

                        if (headerMatch && footerMatch) {
                            var header = headerMatch[0];
                            var footer = footerMatch[0];

                            // Remove the header and footer from the key and trim it
                            var keyBody = sshKey.replace(header, "").replace(footer, "").trim();

                            // Check if the key body is already formatted
                            var isFormatted = keyBody.split('\n').every(line => line.length <= 64);

                            if (!isFormatted) {
                                // Format the key body in blocks of 64 characters
                                var formattedKeyBody = keyBody.replace(/(.{64})/g, "$1\n");

                                // Reconstruct the key with header and footer
                                var formattedKey = header + "\n" + formattedKeyBody + "\n" + footer;

                                // Update the input field with the formatted key
                                sshKeyField.value = formattedKey;
                                jeedomUtils.showAlert({
                                    title: "SSH Manager - Format SSH Key",
                                    message: "Formatage de la clé SSH en blocs de 64 caractères :: OK",
                                    level: 'success',
                                    emptyBefore: false
                                });
                            } else {
                                jeedomUtils.showAlert({
                                    title: "SSH Manager - Format SSH Key",
                                    message: "{{La clé SSH est déjà formatée en blocs de 64 caractères !}}",
                                    level: 'info',
                                    emptyBefore: false
                                });
                                console.error("SSH key is already formatted in blocks of 64 characters");
                            }

                        } else {
                            jeedomUtils.showAlert({
                                title: "SSH Manager - Format SSH Key",
                                message: "{{Format de la clé SSH invalide !}}",
                                level: 'warning',
                                emptyBefore: false
                            });
                            console.error("Invalid SSH key format");
                        }
                    }
                </script>
            </label>
            <div class="col-md-8">
                <textarea class="eqLogicAttr form-control" rows="5" data-l1key="configuration" data-l2key="<?= sshmanager::CONFIG_SSH_KEY ?>" placeholder="{{Saisir la clé SSH}}" wrap="off" spellcheck="false"></textarea>
            </div>
        </div>
    </div>
</div>
<?php include_file('desktop', 'params.sshmanager', 'js', 'sshmanager'); ?>