<?php

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
    <label class="col-md-4 control-label help" data-help="{{Adresse IP ou nom de l'hôte}}">{{Hôte distant}}</label>
    <div class="col-md-6">
        <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="<?= sshmanager::CONFIG_HOST ?>" type="text" placeholder="{{Saisir l'adresse IP ou le nom}}" />
    </div>
</div>
<div class="form-group">
    <label class="col-md-4 control-label help" data-help="{{Port par défaut: 22}}">{{Port SSH}}</label>
    <div class="col-md-6">
        <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="<?= sshmanager::CONFIG_PORT ?>" type="text" placeholder="{{Saisir le port SSH (par défaut: 22)}}" />
    </div>
</div>
<div class="form-group">
    <label class="col-md-4 control-label help" data-help="{{Durée maximale (en secondes) avant expiration de la connexion SSH (par défaut : 10s)}}">{{Timeout SSH}}</label>
    <div class="col-md-6">
        <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="<?= sshmanager::CONFIG_TIMEOUT ?>" type="text" placeholder="{{Saisir le timeout SSH (par défaut : 10s)}}" />
    </div>
</div>
<div class="form-group">
    <label class="col-md-4 control-label">{{Identifiant}}</label>
    <div class="col-md-6">
        <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="<?= sshmanager::CONFIG_USERNAME ?>" type="text" autocomplete="ssh-user" placeholder="{{Saisir le login}}" />
    </div>
</div>
<div class="remote-pwd" style="display:none;">
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
        <label class="col-md-4 control-label help" data-help="{{Optionnel : Phrase secrète pour la clé SSH}}">{{Passphrase}}</label>
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
            <textarea class="eqLogicAttr form-control" rows="10" data-l1key="configuration" data-l2key="<?= sshmanager::CONFIG_SSH_KEY ?>" placeholder="{{Saisir la clé SSH}}" wrap="off" spellcheck="false"></textarea>
        </div>
    </div>
</div>

<?php include_file('desktop', 'specificparams', 'js', 'sshmanager'); ?>