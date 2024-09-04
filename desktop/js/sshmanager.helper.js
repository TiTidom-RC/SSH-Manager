document.querySelector('.sshmanagerHelper[data-helper=add]').addEventListener('click', function () {
    /* jeedomUtils.showAlert({
        title: "SSH Manager - Add New SSH Conf",
        message: "Add New SSH Conf",
        level: 'danger',
        emptyBefore: false
    }); */
    jeeDialog.dialog({
        id: 'mod_add_sshmanager',
        title: '{{SSH Manager}}',
        width: 750,
        height: 650,
        top: '10vh',
        contentUrl: 'index.php?v=d&plugin=sshmanager&modal=mod.add.sshmanager',
        callback: function () {
        },
        buttons: {
            confirm: {
                label: '{{Sauvegarder Hôte SSH}}',
                className: 'success',
                callback: {
                    click: function (event) {
                        let response = jeeDialog.get('#mod_add_sshmanager', 'content')

                        let new_name = response.querySelector('.eqLogicAttr[data-l1key="name"]').value
                        let new_host = response.querySelector('.eqLogicAttr[data-l2key="host"]').value
                        let new_port = response.querySelector('.eqLogicAttr[data-l2key="port"]').value
                        let new_timeout = response.querySelector('.eqLogicAttr[data-l2key="timeout"]').value
                        let new_user = response.querySelector('.eqLogicAttr[data-l2key="username"]').value
                        let new_password = response.querySelector('.eqLogicAttr[data-l2key="password"]').value
                        let new_key = response.querySelector('.eqLogicAttr[data-l2key="ssh-key"]').value
                        let new_passphrase = response.querySelector('.eqLogicAttr[data-l2key="ssh-passphrase"]').value
                        let new_auth_method = response.querySelector('.eqLogicAttr[data-l2key="' + CONFIG_AUTH_METHOD + '"]').value

                        jeedom.eqLogic.save({
                            type: 'sshmanager',
                            eqLogics: [{
                                name: new_name,
                                isEnable: 1,
                                isVisible: 0,
                                configuration: {
                                    'host': new_host,
                                    'port': new_port,
                                    'timeout': new_timeout,
                                    'username': new_user,
                                    'password': new_password,
                                    'ssh-key': new_key,
                                    'ssh-passphrase': new_passphrase,
                                    'auth-method': new_auth_method
                                }
                            }],
                            error: function (error) {
                                jeedomUtils.showAlert({
                                    title: "SSH Manager - Add New SSH",
                                    message: "Error :: " + error.message,
                                    level: 'danger',
                                    emptyBefore: false
                                });
                            },
                            success: function (data) {
                                jeedomUtils.showAlert({
                                    title: "SSH Manager - Add New SSH",
                                    message: "Success :: {{Equipement créé}} :: " + data.name + " (" + data.configuration['auth-method'] + ")",
                                    level: 'success',
                                    emptyBefore: false
                                });
                                buildSelectHost();
                                jeeDialog.get('#mod_add_sshmanager').destroy()
                            }

                        });
                    }
                }
            },
            cancel: {
                label: '{{Fermer}}',
                className: 'warning',
                callback: {
                    click: function (event) {
                        jeedomUtils.showAlert({
                            title: "SSH Manager	- Add New SSH Conf",
                            message: "Cancel :: Action annulée",
                            level: 'warning',
                            emptyBefore: false
                        });
                        jeeDialog.get('#mod_add_sshmanager').destroy()
                    }
                }
            }
        }
    })
});

function buildSelectHost(currentValue) {
    const selectHost = document.querySelector('.sshmanagerHelper[data-helper=list]');
    if (currentValue === undefined) {
        currentValue = selectHost.value;
    }
    //TODO test if selectHost is null
    selectHost.innerHTML = '';
    let option = document.createElement('option');
    option.text = '{{Sélectionner un hôte}}';
    option.value = '';
    selectHost.add(option);

    return domUtils.ajax({
        type: 'POST',
        url: 'plugins/sshmanager/core/ajax/sshmanager.ajax.php',
        data: {
            action: "getRemoteHosts",
        },
        dataType: 'json',
        // async: true,
        global: false,
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            if (data.state != 'ok') {
                jeedomUtils.showAlert({
                    title: "SSH Manager - Build Select Host",
                    message: data.result,
                    level: 'danger',
                    emptyBefore: false
                });
                return;
            } else {
                for (const id in data.result) {
                    selectHost.append(new Option(data.result[id], id));
                }
                selectHost.value = currentValue;
            }
        }
    });

    /* return $.ajax({
        type: "POST",
        url: "plugins/sshmanager/core/ajax/sshmanager.ajax.php",
        data: {
            action: "getRemoteHosts",
        },
        dataType: 'json',
        global: false,
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            if (data.state != 'ok') {
                $('#modal_alert').showAlert({ message: data.result, level: 'danger' });
                return;
            }
            for (const id in data.result) {
                selectHost.append(new Option(data.result[id], id));
            }
            selectHost.value = currentValue;
        }
    }); */
}