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


/* Fonction permettant l'affichage des commandes dans l'équipement */
function addCmdToTable(_cmd) {
	if (!isset(_cmd)) {
		var _cmd = { configuration: {} }
	}
	if (!isset(_cmd.configuration)) {
		_cmd.configuration = {}
	}

	var selCmdType = '<select style="width : 120px;" class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="cmdType">'
	selCmdType += '<option value="command">{{SSH}}</option>'
	selCmdType += '<option value="refresh">{{Refresh}}</option>'
	selCmdType += '<option hidden value="refreshAll">{{Refresh All}}</option>'
	selCmdType += '</select>'

	let tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">'
	
	// ID
	tr += '<td class="hidden-xs">'
	tr += '<span class="cmdAttr" data-l1key="id"></span>'
	tr += '</td>'
	
	// Nom de la commande
	tr += '<td>'
	tr += '<div class="input-group">'
	tr += '<input class="cmdAttr form-control input-sm roundedLeft" data-l1key="name" placeholder="{{Nom de la commande}}">'
	tr += '<span class="input-group-btn"><a class="cmdAction btn btn-sm btn-default" data-l1key="chooseIcon" title="{{Choisir une icône}}"><i class="fas fa-icons"></i></a></span>'
	tr += '<span class="cmdAttr input-group-addon roundedRight" data-l1key="display" data-l2key="icon" style="font-size:19px;padding:0 5px 0 0!important;"></span>'
	tr += '</div>'
	if (init(_cmd.logicalId) != 'refresh') {
		tr += '<select class="cmdAttr form-control input-sm" data-l1key="value" style="display:none;margin-top:5px;" title="{{Commande info liée}}">'
		tr += '<option value="">{{Aucune}}</option>'
		tr += '</select>'
	}
	tr += '</td>'
	
	var displayRefresh = init(_cmd.logicalId) != 'refresh' ? 'block' : 'none'

	// Type Cmd
	tr += '<td>'
  	tr += '<span class="cmdType" style="display:' + displayRefresh + ';" type="' + init(_cmd.configuration.cmdType) + '" >' + selCmdType
  	tr += '</td>'

	// Type
	tr += '<td>'
	tr += '<span class="type" style="display:' + displayRefresh + ';" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>'
	tr += '<span class="subType" style="display:' + displayRefresh + ';" subType="' + init(_cmd.subType) + '"></span>'
	tr += '</td>'

	// Request
	tr += '<td>'
	tr += '<textarea rows="2" class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="ssh-command"></textarea>'
	tr += '</td>'

	// Paramètres
	tr += '<td class="tdOptions">'

	// Paramètres->Auto-Refresh
	tr += '<div class="cmdOptionAutoRefresh">'
	tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="configuration" data-l2key="autorefresh" checked />{{Auto-Refresh}}</label>'
	tr += '</div>'
	
	// Paramètres->RefreshCmdSelect
	tr += '<select class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="cmdToRefresh" style="display:none;margin-top:5px;" title="{{Commande à rafraîchir}}">'
	tr += '<option value="">{{Aucune}}</option>'
	tr += '</select>'

	// Paramètres->cmdCronRefresh
	tr += '<div class="input-group divCmdCronRefresh" style="display:none;margin-top:5px;">'
	tr += '<input class="cmdAttr form-control input-sm roundedLeft" data-l1key="configuration" data-l2key="cmdCronRefresh" placeholder="{{Cron (? = Assistant)}}">'
	tr += '<span class="input-group-btn"><a class="btn btn-sm btn-default cursor jeeHelper roundedRight" data-helper="cron" title="{{Assistant Cron}}"><i class="fas fa-question-circle"></i></a></span>'
	tr += '</div>'

	// Paramètres->Service
	tr += '<div class="cmdTypeConfig" data-type="service" style="display: none;">'
	tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="serviceName" placeholder="{{Nom du Service}}">'
	tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="serviceAction" placeholder="{{Action du Service}}">'
	tr += '</div>'
	
	tr += '</td>'

	// Options
	tr += '<td>'
	tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked />{{Afficher}}</label> '
	tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isHistorized" />{{Historiser}}</label> '
	tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="display" data-l2key="invertBinary"/>{{Inverser}}</label> '
	tr += '<div style="margin-top:7px;">'
	tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
	tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
	tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="unite" placeholder="Unité" title="{{Unité}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
	tr += '</div>'
	tr += '</td>'
	
	// Etat (State)
	tr += '<td>'
	tr += '<span class="cmdAttr" data-l1key="htmlstate"></span>'
	tr += '</td>'

	// Actions
	tr += '<td>'
	if (is_numeric(_cmd.id)) {
		tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fas fa-cogs"></i></a> '
		tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> {{Tester}}</a>'
	}
	tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove" title="{{Supprimer la commande}}"></i></td>'

	tr += '</tr>'

	let newRow = document.createElement('tr')
	newRow.innerHTML = tr
	newRow.addClass('cmd')
	newRow.setAttribute('data-cmd_id', init(_cmd.id))
	document.getElementById('table_cmd').querySelector('tbody').appendChild(newRow)

	if (isset(_cmd.configuration.cmdType)) {
		newRow.querySelector('.cmdAttr[data-l1key="configuration"][data-l2key="cmdType"]').dispatchEvent(new Event('change'));
	}

	jeedom.eqLogic.buildSelectCmd({
		id: document.querySelector('.eqLogicAttr[data-l1key="id"]').jeeValue(),
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

document.querySelectorAll('.pluginAction[data-action=openLocation]').forEach(function (element) {
	element.addEventListener('click', function () {
		window.open(this.getAttribute("data-location"), "_blank", null);
	});
});

document.getElementById('div_pageContainer').addEventListener("change", function(event) {
	if (event.target.classList.contains("cmdAttr") && event.target.getAttribute("data-l1key") === "type") {
		var tr = event.target.closest("tr");
		var type = event.target.value;

		if (type === "info") {
			tr.querySelector(".cmdOptionAutoRefresh").style.display = "block";
		} else {
			tr.querySelector(".cmdOptionAutoRefresh").style.display = "none";
		}
	}

	if (event.target.classList.contains("cmdAttr") && event.target.getAttribute("data-l1key") === "configuration" && event.target.getAttribute("data-l2key") === "cmdType") {
		var tr = event.target.closest("tr");

		tr.querySelectorAll(".cmdTypeConfig").forEach(config => config.style.display = "none");

		/* console.log(event.target.value); */
		if (event.target.value === "refreshAll") {
			tr.querySelector(".cmdAttr[data-l1key='configuration'][data-l2key='ssh-command']").style.display = "none";
			tr.querySelector('.cmdAttr[data-l1key="configuration"][data-l2key="cmdToRefresh"]').style.display = "none";
			tr.querySelector('.divCmdCronRefresh').style.display = "none";
		
		} else if (event.target.value === "refresh" ) {
			tr.querySelector(".cmdAttr[data-l1key='type']").value = "action";
			tr.querySelector(".cmdAttr[data-l1key='type']").triggerEvent("change");
			tr.querySelector(".type").style.display = "none";	
			tr.querySelector(".subType").style.display = "none";

			tr.querySelector(".cmdOptionAutoRefresh").style.display = "none";
			tr.querySelector(".cmdAttr[data-l1key='configuration'][data-l2key='ssh-command']").style.display = "none";
			tr.querySelector('.cmdAttr[data-l1key="configuration"][data-l2key="cmdToRefresh"]').style.display = "block";
			tr.querySelector('.divCmdCronRefresh').style.display = "table";
		
		} else if (event.target.value === "command") {
			tr.querySelector(".type").style.display = "block";
			tr.querySelector(".subType").style.display = "block";

			tr.querySelector(".cmdAttr[data-l1key='configuration'][data-l2key='ssh-command']").style.display = "block";
			tr.querySelector('.cmdAttr[data-l1key="configuration"][data-l2key="cmdToRefresh"]').style.display = "none";
			tr.querySelector('.divCmdCronRefresh').style.display = "none";
			
		} else {
			tr.querySelector(".cmdOptionAutoRefresh").style.display = "none";
			tr.querySelector(".cmdAttr[data-l1key='configuration'][data-l2key='ssh-command']").style.display = "none";
			tr.querySelector('.cmdAttr[data-l1key="configuration"][data-l2key="cmdToRefresh"]').style.display = "none";
			tr.querySelector('.divCmdCronRefresh').style.display = "none";
			
		}
	}
});

document.getElementById('div_pageContainer').addEventListener("click", function(event) {
	if (event.target.classList.contains("btnTemplateCmds")) {
		jeeDialog.dialog({
			id: 'mod_commands',
			title: '{{Commandes SSH (SSH Manager)}}',
			width: 850,
			height: 600,
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
							let new_subtype = response.querySelector('.cmdAttr[data-l1key="subType"]').value;

							// addtoTableCmd
							addCmdToTable({
								'type': new_type,
								'subType': new_subtype,
								'name': new_name,

								configuration: {
									'cmdType': 'command',
									'ssh-command': new_cmd,
									'autorefresh': 1,
								}
							});
							// document.querySelectorAll('.cmdAttr[data-l1key="type"]').last().triggerEvent('change')
							// document.querySelectorAll('.cmdAttr[data-l1key="subType"]').last().triggerEvent('change')
    						// jeeFrontEnd.modifyWithoutSave = true
    						modifyWithoutSave = true

							jeedomUtils.showAlert({
								title: "SSH Manager	- Commands",
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
								title: "SSH Manager	- Commands",
								message: "Cancel :: Action annulée",
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
