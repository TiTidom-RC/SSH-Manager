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

(function() {
    'use strict';

    // Flag to prevent multiple event attachments (SPA protection)
    if (window.sshManagerInit) return;
    window.sshManagerInit = true;

    // DOM Selectors constants (better minification + no string repetition + immutable)
    const SELECTORS = Object.freeze({
        TABLE_CMD: '#table_cmd',
        EQ_ID: '.eqLogicAttr[data-l1key=id]',
        PAGE_CONTAINER: '#div_pageContainer'
    });

    /**
     * Fonction permettant l'affichage des commandes dans l'équipement
     * @param {Object} _cmd - Commande à ajouter
     */
    function addCmdToTable(_cmd) {
        if (!isset(_cmd)) {
            _cmd = { configuration: {} }
        }
        if (!isset(_cmd.configuration)) {
            _cmd.configuration = {}
        }

        const displayRefresh = init(_cmd.logicalId) != 'refresh' ? 'block' : 'none';
        const showValueSelect = init(_cmd.logicalId) != 'refresh';
        
        const selCmdType = `
            <select style="width:120px;" class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="cmdType">
                <option value="command">{{SSH}}</option>
                <option value="refresh">{{Refresh}}</option>
                <option hidden value="refreshAll">{{Refresh All}}</option>
            </select>
        `;

        const tr = `
            <tr class="cmd" data-cmd_id="${init(_cmd.id)}">
                <!-- ID -->
                <td class="hidden-xs">
                    <span class="cmdAttr" data-l1key="id"></span>
                </td>
                
                <!-- Nom de la commande -->
                <td>
                    <div class="input-group">
                        <input class="cmdAttr form-control input-sm roundedLeft" data-l1key="name" placeholder="{{Nom de la commande}}">
                        <span class="input-group-btn"><a class="cmdAction btn btn-sm btn-default" data-l1key="chooseIcon" title="{{Choisir une icône}}"><i class="fas fa-icons"></i></a></span>
                        <span class="cmdAttr input-group-addon roundedRight" data-l1key="display" data-l2key="icon" style="font-size:19px;padding:0 5px 0 0!important;"></span>
                    </div>
                    ${showValueSelect ? `
                    <select class="cmdAttr form-control input-sm" data-l1key="value" style="display:none;margin-top:5px;" title="{{Commande info liée}}">
                        <option value="">{{Aucune}}</option>
                    </select>
                    ` : ''}
                </td>
                
                <!-- Type Cmd -->
                <td>
                    <span class="cmdType" style="display:${displayRefresh};" type="${init(_cmd.configuration.cmdType)}">${selCmdType}</span>
                </td>

                <!-- Type -->
                <td>
                    <span class="type" style="display:${displayRefresh};" type="${init(_cmd.type)}">${jeedom.cmd.availableType()}</span>
                    <span class="subType" style="display:${displayRefresh};" subType="${init(_cmd.subType)}"></span>
                </td>

                <!-- Request -->
                <td>
                    <textarea rows="2" class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="ssh-command"></textarea>
                </td>

                <!-- Paramètres -->
                <td class="tdOptions">
                    <!-- Paramètres->Auto-Refresh -->
                    <div class="cmdOptionAutoRefresh">
                        <label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="configuration" data-l2key="autorefresh" checked />{{Auto-Refresh}}</label>
                    </div>
                    
                    <!-- Paramètres->RefreshCmdSelect -->
                    <select class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="cmdToRefresh" style="display:none;margin-top:5px;" title="{{Commande à rafraîchir}}">
                        <option value="">{{Aucune}}</option>
                    </select>

                    <!-- Paramètres->cmdCronRefresh -->
                    <div class="input-group divCmdCronRefresh" style="display:none;margin-top:5px;">
                        <input class="cmdAttr form-control input-sm roundedLeft" data-l1key="configuration" data-l2key="cmdCronRefresh" placeholder="{{Cron (? = Assistant)}}">
                        <span class="input-group-btn"><a class="btn btn-sm btn-default cursor jeeHelper roundedRight" data-helper="cron" title="{{Assistant Cron}}"><i class="fas fa-question-circle"></i></a></span>
                    </div>

                    <!-- Paramètres->Service -->
                    <div class="cmdTypeConfig" data-type="service" style="display:none;">
                        <input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="serviceName" placeholder="{{Nom du Service}}">
                        <input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="serviceAction" placeholder="{{Action du Service}}">
                    </div>
                </td>

                <!-- Options -->
                <td>
                    <label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked />{{Afficher}}</label>
                    <label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isHistorized" />{{Historiser}}</label>
                    <label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="display" data-l2key="invertBinary"/>{{Inverser}}</label>
                    <div style="margin-top:7px;">
                        <input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">
                        <input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">
                        <input class="tooltips cmdAttr form-control input-sm" data-l1key="unite" placeholder="Unité" title="{{Unité}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">
                    </div>
                </td>
                
                <!-- Etat (State) -->
                <td>
                    <span class="cmdAttr" data-l1key="htmlstate"></span>
                </td>

                <!-- Actions -->
                <td>
                    ${is_numeric(_cmd.id) ? `
                    <a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fas fa-cogs"></i></a>
                    <a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> {{Tester}}</a>
                    ` : ''}
                    <i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove" title="{{Supprimer la commande}}"></i>
                </td>
            </tr>
        `;

        // Create and configure row element (optimal: Object.assign for batch properties)
        const newRow = Object.assign(document.createElement('tr'), {
            className: 'cmd',
            innerHTML: tr
        });
        newRow.setAttribute('data-cmd_id', init(_cmd.id));
        
        // Cache table body for performance
        const tableBody = document.querySelector(`${SELECTORS.TABLE_CMD} tbody`);
        if (!tableBody) return console.error('Table body not found');
        
        tableBody.appendChild(newRow);

        if (isset(_cmd.configuration.cmdType)) {
            newRow.querySelector('.cmdAttr[data-l1key="configuration"][data-l2key="cmdType"]').dispatchEvent(new Event('change'));
        }

        // Cache eqLogic ID to avoid multiple DOM queries
        const eqLogicIdElement = document.querySelector(SELECTORS.EQ_ID);
        if (!eqLogicIdElement) return console.error('Equipment ID element not found');

        jeedom.eqLogic.buildSelectCmd({
            id: eqLogicIdElement.jeeValue(),
            filter: { type: 'info' },
            error: function (error) {
                jeedomUtils.showAlert({ message: error.message, level: 'danger' })
            },
            success: function (result) {
                newRow.querySelector('.cmdAttr[data-l1key="value"]')?.insertAdjacentHTML('beforeend', result)
                newRow.querySelector('.cmdAttr[data-l1key="configuration"][data-l2key="cmdToRefresh"]')?.insertAdjacentHTML('beforeend', result)
                newRow.setJeeValues(_cmd, '.cmdAttr')
                jeedom.cmd.changeType(newRow, init(_cmd.subType))
            }
        })
    }

    // Event delegation pour openLocation (global, attaché une seule fois)
    if (!window.sshManagerOpenLocationAttached) {
        window.sshManagerOpenLocationAttached = true;
        
        document.addEventListener('click', (event) => {
            const target = event.target.closest('.pluginAction[data-action=openLocation]');
            if (target) {
                event.preventDefault();
                window.open(target.getAttribute('data-location'), '_blank', null);
            }
        });
    }

    document.querySelector(SELECTORS.PAGE_CONTAINER).addEventListener("change", function(event) {
        if (event.target.classList.contains("cmdAttr") && event.target.getAttribute("data-l1key") === "type") {
            const tr = event.target.closest("tr");
            const type = event.target.value;
            const autoRefresh = tr.querySelector(".cmdOptionAutoRefresh");

            if (!autoRefresh) return;
            
            if (type === "info") {
                autoRefresh.seen();
            } else {
                autoRefresh.unseen();
            }
        }

        if (event.target.classList.contains("cmdAttr") && event.target.getAttribute("data-l1key") === "configuration" && event.target.getAttribute("data-l2key") === "cmdType") {
            const tr = event.target.closest("tr");

            tr.querySelectorAll(".cmdTypeConfig").forEach(config => config.unseen());

            if (event.target.value === "refreshAll") {
                tr.querySelector(".cmdAttr[data-l1key='configuration'][data-l2key='ssh-command']")?.unseen();
                tr.querySelector('.cmdAttr[data-l1key="configuration"][data-l2key="cmdToRefresh"]')?.unseen();
                tr.querySelector('.divCmdCronRefresh')?.unseen();
            
            } else if (event.target.value === "refresh" ) {
                const typeSelect = tr.querySelector(".cmdAttr[data-l1key='type']");
                typeSelect.value = "action";
                typeSelect.dispatchEvent(new Event("change"));
                tr.querySelector(".type")?.unseen();
                tr.querySelector(".subType")?.unseen();

                tr.querySelector(".cmdOptionAutoRefresh")?.unseen();
                tr.querySelector(".cmdAttr[data-l1key='configuration'][data-l2key='ssh-command']")?.unseen();
                tr.querySelector('.cmdAttr[data-l1key="configuration"][data-l2key="cmdToRefresh"]')?.seen();
                tr.querySelector('.divCmdCronRefresh')?.seen();
            
            } else if (event.target.value === "command") {
                tr.querySelector(".type")?.seen();
                tr.querySelector(".subType")?.seen();

                tr.querySelector(".cmdAttr[data-l1key='configuration'][data-l2key='ssh-command']")?.seen();
                tr.querySelector('.cmdAttr[data-l1key="configuration"][data-l2key="cmdToRefresh"]')?.unseen();
                tr.querySelector('.divCmdCronRefresh')?.unseen();
            
            } else {
                tr.querySelector(".cmdOptionAutoRefresh")?.unseen();
                tr.querySelector(".cmdAttr[data-l1key='configuration'][data-l2key='ssh-command']")?.unseen();
                tr.querySelector('.cmdAttr[data-l1key="configuration"][data-l2key="cmdToRefresh"]')?.unseen();
                tr.querySelector('.divCmdCronRefresh')?.unseen();
            
            }
        }
    });

    /**
     * Charge les informations d'utilisation de l'équipement SSH
     * @param {Object} _eqLogic - Equipement Jeedom
     */
    function printEqLogic(_eqLogic) {
        if (!_eqLogic) return;
        
        domUtils.ajax({
            type: 'POST',
            url: 'plugins/sshmanager/core/ajax/sshmanager.ajax.php',
            data: {
                action: 'getUsedBy',
                eqLogic_id: _eqLogic.id
            },
            dataType: 'json',
            error: function (error) {
                jeedomUtils.showAlert({ message: error.message, level: 'danger' })
            },
            success: function (data) {
                if (data.state != 'ok') {
                    jeedomUtils.showAlert({
                        title: "SSH Manager - UsedBy",
                        message: "Error :: Retrieving children eqLogic !",
                        level: 'warning',
                        emptyBefore: false
                    });
                    return;
                }
                document.getElementById('div_eqLogicList').innerHTML = data.result;
            }
        });
    }

    document.querySelector(SELECTORS.PAGE_CONTAINER).addEventListener("click", function(event) {
        if (event.target.classList.contains("btnTemplateCmds")) {
            jeeDialog.dialog({
                id: 'mod_commands',
                title: '{{Commandes SSH (SSH Manager)}}',
                width: 850,
                height: 615,
                top: '10vh',
                contentUrl: 'index.php?v=d&plugin=sshmanager&modal=mod.commands',
                callback: function () {
                },
                buttons: {
                    confirm: {
                        label: '{{Ajouter}}',
                        className: 'success',
                        callback: {
                            click: function (event) {
                                let response = jeeDialog.get('#mod_commands', 'content')
                                let new_name = response.querySelector('.cmdAttr[data-l1key="name"]').value;
                                let new_cmd = response.querySelector('.cmdAttr[data-l1key="ssh-command"]').value;
                                let new_type = response.querySelector('.cmdAttr[data-l1key="type"]').value;
                                let new_subtype = response.querySelector('.cmdAttr[data-l1key="subtype"]').value;

                                if (new_name === '' || new_cmd === '' || new_type === '' || new_subtype === '') {
                                    jeedomUtils.showAlert({
                                        title: "SSH Manager - Commands",
                                        message: "Error :: Please select a valid command !",
                                        level: 'warning',
                                        emptyBefore: false
                                    });
                                    return
                                }

                                addCmdToTable({
                                    'type': (new_type === '' ? 'info' : new_type),
                                    'subType': (new_subtype === '' ? 'string' : new_subtype),
                                    'name': new_name,
                                    configuration: {
                                        'cmdType': 'command',
                                        'ssh-command': new_cmd
                                    }
                                });
                                modifyWithoutSave = true;

                                jeedomUtils.showAlert({
                                    title: "SSH Manager - Commands",
                                    message: "Selected Command (Name, Command) :: " + new_name + " :: " + new_cmd,
                                    level: 'success',
                                    emptyBefore: false
                                });
                                jeeDialog.get('#mod_commands').destroy()
                            }
                        }
                    },
                    cancel: {
                        label: '{{Annuler}}',
                        className: 'warning',
                        callback: {
                            click: function (event) {
                                jeedomUtils.showAlert({
                                    title: "SSH Manager - Commands",
                                    message: "Ajout de commande :: Action annulée",
                                    level: 'warning',
                                    emptyBefore: false
                                });
                                jeeDialog.get('#mod_commands').destroy()
                            }
                        }
                    }
                }
            });
        }
    });

    // Expose functions globally for Jeedom to call them
    window.addCmdToTable = addCmdToTable;

})();
