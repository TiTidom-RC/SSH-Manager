<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}

sendVarToJS([
    'CONFIG_HOST' => sshmanager::CONFIG_HOST,
    'CONFIG_PORT' => sshmanager::CONFIG_PORT,
    'CONFIG_TIMEOUT' => sshmanager::CONFIG_TIMEOUT,
    'CONFIG_USERNAME' => sshmanager::CONFIG_USERNAME,
    'CONFIG_PASSWORD' => sshmanager::CONFIG_PASSWORD,
    'CONFIG_SSH_KEY' => sshmanager::CONFIG_SSH_KEY,
    'CONFIG_SSH_PASSPHRASE' => sshmanager::CONFIG_SSH_PASSPHRASE,
    'CONFIG_AUTH_METHOD' => sshmanager::CONFIG_AUTH_METHOD,
    'AUTH_METHOD_PASSWORD' => sshmanager::AUTH_METHOD_PASSWORD,
    'AUTH_METHOD_SSH_KEY' => sshmanager::AUTH_METHOD_SSH_KEY,
    'AUTH_METHOD_AGENT' => sshmanager::AUTH_METHOD_AGENT
]);

?>

<div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
    <div role="tabpanel" class="tab-pane active" id="addnewssh">
        <legend><i class="fas fa-list-alt"></i> {{Ajout d'un nouvel équipement SSH Manager :}}</legend>
        <br />
        <div class="col-sm-12">
            <form class="form-horizontal">
                <fieldset>
                    <div class="form-group">
                        <label class="col-md-4 control-label">{{Nom de l'équipement}}</label>
                        <div class="col-md-6">
                            <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}" />
                        </div>
                    </div>

                    <br />
                    <?php include_file('desktop', 'params.sshmanager.inc', 'php', 'sshmanager'); ?>
                    <br />
                    <div class='form-group'>
                        <label class="col-md-4 control-label">{{Sauvegarder}}
                            <sup><i class="fas fa-question-circle tooltips" title="{{Sauvegarder les informations de connexion SSH}}"></i></sup>
                        </label>
                        <div class="col-lg-4">
                            <a class="btn btn-success btn-xm" onclick="saveNewSSH()"><i id="btnSaveNewSSH" class="fas fa-save"></i> {{Sauvegarder Hôte SSH}}</a>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>
        
    </div>
</div>

<?php
include_file('desktop', 'mod.add.sshmanager', 'js', 'sshmanager');
?>