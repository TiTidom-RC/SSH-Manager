<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}

sendVarToJS(sshmanager::CONST_TO_JS);

?>

<div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
    <div role="tabpanel" class="tab-pane active">
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
                </fieldset>
            </form>
        </div>

    </div>
</div>

<?php
include_file('desktop', 'mod.add.sshmanager', 'js', 'sshmanager');
?>