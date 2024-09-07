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
	selCmdType += '<option value="command">{{Commande}}</option>'
	selCmdType += '<option value="refresh">{{Refresh}}</option>'
	selCmdType += '<option value="service">{{Service}}</option>'
	selCmdType += '<option value="checkupdates">{{Check Updates}}</option>'
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
	tr += '<select class="cmdAttr form-control input-sm" data-l1key="value" style="display:none;margin-top:5px;" title="{{Commande info liée}}">'
	tr += '<option value="">{{Aucune}}</option>'
	tr += '</select>'
	tr += '</td>'
	
	// Type Cmd
	tr += '<td>'
	var displayRefresh = (_cmd.logicalId != 'refresh' ? 'block' : 'none')
  	tr += '<span class="cmdType" style="display: ' + displayRefresh + ';" type="' + init(_cmd.configuration.cmdType) + '" >' + selCmdType	
  	tr += '</td>'

	// Type
	tr += '<td>'
	tr += '<span class="type" style="display: ' + displayRefresh + ';" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>'
	tr += '<span class="subType" style="display: ' + displayRefresh + '" subType="' + init(_cmd.subType) + '"></span>'
	tr += '</td>'

	// Request
	tr += '<td>'
	tr += '<textarea rows="2" class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="ssh-command"></textarea>'
	tr += '</td>'

	// Paramètres
	tr += '<td class="tdOptions">'

	// Paramètres->Auto-Refresh
	tr += '<div class="cmdOptionRefresh">'
	tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="configuration" data-l2key="autoRefresh" checked />{{Auto-Refresh}}</label>'
	tr += '</div>'
	
	// Paramètres->RefreshCmdSelect
	tr += '<select class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="cmdToRefresh" style="display:none;margin-top:5px;" title="{{Commande à rafraîchir}}">'
	tr += '<option value="">{{Aucune}}</option>'
	tr += '</select>'

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

	if (isset(_cmd.configuration.requestType)) {
		document.querySelector('#table_cmd tbody tr:last .cmdAttr[data-l1key="configuration"][data-l2key="cmdType"]').dispatchEvent(new Event('change'));
	}

	jeedom.eqLogic.buildSelectCmd({
		id: document.querySelector('.eqLogicAttr[data-l1key="id"]').jeeValue(),
		filter: { type: 'info' },
		error: function (error) {
			jeedomUtils.showAlert({ message: error.message, level: 'danger' })
		},
		success: function (result) {
			newRow.querySelector('.cmdAttr[data-l1key="value"]').insertAdjacentHTML('beforeend', result)
			newRow.querySelector('.cmdAttr[data-l1key="configuration"][data-l2key="cmdToRefresh"]').insertAdjacentHTML('beforeend', result)
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

document.querySelector("#table_cmd tbody").addEventListener("change", function(event) {
	if (event.target.classList.contains("cmdAttr") && event.target.getAttribute("data-l1key") === "configuration" && event.target.getAttribute("data-l2key") === "cmdType") {
		var tr = event.target.closest("tr");
		
		tr.querySelectorAll(".cmdTypeConfig").forEach(config => config.style.display = "none");

		console.log(event.target.value);
		if (event.target.value === "refresh" ) {
			tr.querySelector(".cmdOptionRefresh").style.display = "none";
			tr.querySelector(".cmdAttr[data-l1key='configuration'][data-l2key='ssh-command']").style.display = "none";
			tr.querySelector('.cmdAttr[data-l1key="configuration"][data-l2key="cmdToRefresh"]').style.display = "block";
		} else if (event.target.value === "command") {
			tr.querySelector(".cmdOptionRefresh").style.display = "block";
			tr.querySelector(".cmdAttr[data-l1key='configuration'][data-l2key='ssh-command']").style.display = "block";
			tr.querySelector('.cmdAttr[data-l1key="configuration"][data-l2key="cmdToRefresh"]').style.display = "none";
			
		} else if (event.target.value === "service") {
			tr.querySelector(".cmdTypeConfig[data-type='" + event.target.value + "']").style.display = "block";
			tr.querySelector(".cmdOptionRefresh").style.display = "block";
			tr.querySelector(".cmdAttr[data-l1key='configuration'][data-l2key='ssh-command']").style.display = "none";
			tr.querySelector('.cmdAttr[data-l1key="configuration"][data-l2key="cmdToRefresh"]').style.display = "none";
			
		} else {
			tr.querySelector(".cmdOptionRefresh").style.display = "block";
			tr.querySelector(".cmdAttr[data-l1key='configuration'][data-l2key='ssh-command']").style.display = "none";
			tr.querySelector('.cmdAttr[data-l1key="configuration"][data-l2key="cmdToRefresh"]').style.display = "none";
			
		}
	}
});