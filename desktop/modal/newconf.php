<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}

sendVarToJS('CONFIG_AUTH_METHOD', sshmanager::CONFIG_AUTH_METHOD);

?>

<div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
    <div role="tabpanel" class="tab-pane active" id="addnewssh">
        <legend><i class="fas fa-list-alt"></i> {{Ajout d'un nouvel équipement SSH Manager :}}</legend>
        <br />
        <div>
            <div class="col-sm-10">
                <form class="form-horizontal">
                    <fieldset>
                        <div class="form-group">
                            <label class="col-md-4 control-label">{{Nom de l'équipement}}</label>
                            <div class="col-md-6">
                                <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom Equipement SSH Manager}}" />
                            </div>
                        </div>
                        <br />
                        <div class="form-group">
                            <label class="col-md-4 control-label">{{Password ou Clé ?}}</label>
                            <div class="col-md-6">
                                <select id="pwdorkey" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="<?= sshmanager::CONFIG_AUTH_METHOD ?>">
                                    <option value="<?= sshmanager::AUTH_METHOD_PASSWORD ?>" selected>{{Mot de Passe}}</option>
                                    <option value="<?= sshmanager::AUTH_METHOD_SSH_KEY ?>">{{Clé SSH}}</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <div class="form-group">
                                <label class="col-md-4 control-label">{{Adresse IP}}</label>
                                <div class="col-md-6">
                                    <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="<?= sshmanager::CONFIG_HOST ?>" type="text" placeholder="{{Saisir l'adresse IP}}" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-4 control-label">{{Port SSH}}
                                    <sup><i class="fas fa-question-circle tooltips" title="{{Port SSH (par défaut : 22)}}"></i></sup>
                                </label>
                                <div class="col-md-6">
                                    <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="<?= sshmanager::CONFIG_PORT ?>" type="text" placeholder="{{Saisir le port SSH}}" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-4 control-label">{{Timeout SSH}}
                                    <sup><i class="fas fa-question-circle tooltips" title="{{Durée maximale (en secondes) avant expiration de la connexion SSH (par défaut : 30s)}}"></i></sup>
                                </label>
                                <div class="col-md-6">
                                    <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="<?= sshmanager::CONFIG_TIMEOUT ?>" type="text" placeholder="{{Saisir le timeout SSH}}" />
                                </div>
                            </div>
                            <br />
                            <div class="form-group">
                                <label class="col-md-4 control-label">{{Identifiant}}</label>
                                <div class="col-md-6">
                                    <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="<?= sshmanager::CONFIG_USERNAME ?>" type="text" autocomplete="ssh-user" placeholder="{{Saisir le login}}" />
                                </div>
                            </div>
                            <div class="remote-pwd" style="display:block;">
                                <div class="form-group">
                                    <label class="col-md-4 control-label">{{Mot de passe}}</label>
                                    <div class="col-md-6 input-group">
                                        <input type="password" id="ssh-password" class="eqLogicAttr form-control roundedLeft" data-l1key="configuration" data-l2key="<?= sshmanager::CONFIG_PASSWORD ?>" placeholder="{{Saisir le password}}" />
                                        <span class="input-group-btn">
                                            <a class="btn btn-default form-control roundedRight" onclick="toggleSSHPassword()"><i id="btnToggleSSHPasswordIcon" class="fas fa-eye"></i></a>
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
                                        <input type="password" id="ssh-passphrase" class="eqLogicAttr form-control roundedLeft" data-l1key="configuration" data-l2key="<?= sshmanager::CONFIG_SSH_PASSPHRASE ?>" placeholder="{{Saisir la passphrase SSH}}" />
                                        <span class="input-group-btn">
                                            <a class="btn btn-default form-control roundedRight" onclick="toggleSSHPassphrase()"><i id="btnToggleSSHPassphraseIcon" class="fas fa-eye"></i></a>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-md-4 control-label">{{Clé SSH}}</label>
                                    <div class="col-md-8">
                                        <textarea class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="<?= sshmanager::CONFIG_SSH_KEY ?>" placeholder="{{Saisir la clé SSH}}" wrap="off" spellcheck="false"></textarea>
                                    </div>
                                </div>
                            </div>
                            <br />
                            <div class='form-group'>
                                <label class="col-md-4 control-label">{{Sauvegarder}}
                                    <sup><i class="fas fa-question-circle tooltips" title="{{Sauvegarder les informations de connexion SSH}}"></i></sup>
                                </label>
                                <div class="col-lg-4">
                                    <a class="btn btn-success btn-xm" onclick="saveNewSSH()"><i id="btnSaveNewSSH" class="fas fa-save"> {{Sauvegarder Hôte SSH}}</i></a>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include_file('desktop', 'newconf', 'js', 'sshmanager');
?>