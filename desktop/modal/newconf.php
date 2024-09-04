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
            <div class="col-sm-12">
                <form class="form-horizontal">
                    <fieldset>
                        <div class="form-group">
                            <label class="col-md-4 control-label">{{Nom de l'équipement}}</label>
                            <div class="col-md-6">
                                <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom Equipement SSH Manager}}" />
                            </div>
                        </div>

                        <br />
                        <?php include_file('desktop', 'specificparams.inc', 'php', 'sshmanager'); ?>
                        <br />

                        <div class='form-group'>
                            <label class="col-md-4 control-label">{{Sauvegarder}}
                                <sup><i class="fas fa-question-circle tooltips" title="{{Sauvegarder les informations de connexion SSH}}"></i></sup>
                            </label>
                            <div class="col-lg-4">
                                <a class="btn btn-success btn-xm" onclick="saveNewSSH()"><i id="btnSaveNewSSH" class="fas fa-save"> {{Sauvegarder Hôte SSH}}</i></a>
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