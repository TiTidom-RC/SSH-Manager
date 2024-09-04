document.querySelector('.sshmanagerHelper[data-helper=add]').addEventListener('click', function () {
    /* jeedomUtils.showAlert({
        title: "SSH Manager - Add New SSH Conf",
        message: "Add New SSH Conf",
        level: 'danger',
        emptyBefore: false
    }); */
    jeeDialog.dialog({
        id: 'mod_addnewssh',
        title: '{{SSH Manager}}',
        width: 750,
        height: 650,
        top: '10vh',
        contentUrl: 'index.php?v=d&plugin=sshmanager&modal=mod.add.sshmanager',
        callback: function () {
            /* jeedomUtils.showAlert({
                title: "SSH Manager - Add New SSH Conf",
                message: "Callback",
                level: 'danger',
                emptyBefore: false
            }); */
        },
        buttons: {
            confirm: {
                label: '{{OK}}',
                className: 'success',
                callback: {
                    click: function (event) {
                        jeeDialog.get('#mod_addnewssh').destroy()
                    }
                }
            },
            cancel: {
                label: '{{Fermer}}',
                className: 'warning',
                callback: {
                    click: function (event) {
                        /* jeedomUtils.showAlert({
                            title: "SSH Manager	- Add New SSH Conf",
                            message: "Click :: Cancel",
                            level: 'danger',
                            emptyBefore: false
                        }); */
                        jeeDialog.get('#mod_addnewssh').destroy()
                    }
                }
            }
        }
    })
});

function buildSelectHost(currentValue) {
    const selectHost = document.querySelector('.sshmanagerHelper[data-helper=list]');
    //TODO test if selectHost is null
    selectHost.innerHTML = '';
    let option = document.createElement('option');
    option.text = '{{Sélectionner un hôte}}';
    option.value = '';
    selectHost.add(option);

    return $.ajax({
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
            if (currentValue === undefined) {
                currentValue = selectHost.value;
            }
            for (const id in data.result) {
                selectHost.append(new Option(data.result[id], id));
            }
            selectHost.value = currentValue;
        }
    });
}