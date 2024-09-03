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

/* Permet la réorganisation des commandes dans l'équipement : plus nécessaire en 4.4
$("#table_cmd").sortable({
	axis: "y",
	cursor: "move",
	items: ".cmd",
	placeholder: "ui-state-highlight",
	tolerance: "intersect",
	forcePlaceholderSize: true
  }); */

/* Fonction permettant l'affichage des commandes dans l'équipement */
function addCmdToTable(_cmd) {
	if (!isset(_cmd)) {
		var _cmd = { configuration: {} };
	}

	// TODO: Ajouter l'affichage des commandes
	var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
	tr += '<td class="hidden-xs">';
	tr += '<span class="cmdAttr" data-l1key="id"></span>';
	tr += '</td>';
	tr += '<td>';
	tr += '<input class="cmdAttr form-control input-sm" data-l1key="type" value="info" style="display: none">';
	tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" placeholder="{{Nom de la commande}}" style="margin: 1px auto;">';
	tr += '</td>';
	tr += '<td>';
	tr += '</td>';

	tr += '<td>';
	tr += '</td>';

	tr += '<td>';
	tr += '</td>';

	tr += '<td>';
	tr += '<span class="cmdAttr" data-l1key="htmlstate"></span>';
	tr += '</td>';

	tr += '<td>';
	if (is_numeric(_cmd.id)) {
		tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fas fa-cogs"></i></a> ';
		tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> {{Tester}}</a>';
	}
	tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove" title="{{Supprimer la commande}}"></i>';
	tr += '</td>';

	tr += '</tr>';
	$('#table_cmd tbody').append(tr);
	var tr = $('#table_cmd tbody tr').last();
	jeedom.eqLogic.buildSelectCmd({
		id: $('.eqLogicAttr[data-l1key=id]').value(),
		filter: { type: 'info' },
		error: function (error) {
			$('#div_alert').showAlert({ message: error.message, level: 'danger' })
		},
		success: function (result) {
			tr.find('.cmdAttr[data-l1key=value]').append(result)
			tr.setValues(_cmd, '.cmdAttr')
			jeedom.cmd.changeType(tr, init(_cmd.subType))
		}
	});
}

document.querySelectorAll('.pluginAction[data-action=openLocation]').forEach(function (element) {
	element.addEventListener('click', function () {
		window.open(this.getAttribute("data-location"), "_blank", null);
	});
});

document.querySelector('#bt_confnewssh').addEventListener('click', function() {
	jeedomUtils.showAlert({
		title: "SSH Manager - Add New SSH Conf",
		message: "Add New SSH Conf", 
		level: 'danger',
		emptyBefore: false
	});
	jeeDialog.dialog({
		id: 'mod_addnewssh',
		title: '{{Ajouter un hôte SSH}}',
		width: 750,
		height: 550,
		top: '10vh',
		contentUrl: 'index.php?v=d&plugin=sshmanager&modal=newconf',
		callback: function() { 
			jeedomUtils.showAlert({
				title: "SSH Manager - Add New SSH Conf",
				message: "Callback", 
				level: 'danger',
				emptyBefore: false
			});
		 },
		buttons: {
		  confirm: {
			label: '{{Valider}}',
			className: 'success',
			callback: {
			  click: function(event) {
				let response = jeeDialog.get('#mod_addnewssh', 'content')
				
				let new_name = response.querySelector('.eqLogicAttr[data-l1key="name"]').value
				let new_host = response.querySelector('.eqLogicAttr[data-l2key="host"]').value
				let new_port = response.querySelector('.eqLogicAttr[data-l2key="port"]').value
				let new_timeout = response.querySelector('.eqLogicAttr[data-l2key="timeout"]').value
				let new_user = response.querySelector('.eqLogicAttr[data-l2key="username"]').value
				let new_password = response.querySelector('.eqLogicAttr[data-l2key="password"]').value
				let new_key = response.querySelector('.eqLogicAttr[data-l2key="ssh-key"]').value
				let new_passphrase = response.querySelector('.eqLogicAttr[data-l2key="ssh-passphrase"]').value
				let new_auth_method = response.querySelector('.eqLogicAttr[data-l2key="auth-method"]').value

				jeedomUtils.showAlert({
					title: "SSH Manager - Add New SSH Conf",
					message: "Click (Valider) :: " + new_name + " - " + new_host + " - " + new_port + " - " + new_timeout + " - " + new_user + " - " + new_password + " - " + new_key + " - " + new_passphrase + " - " + new_auth_method, 
					level: 'danger',
					emptyBefore: false
				});
				// jeeDialog.get('#mod_addnewssh').destroy()
			  }
			}
		  },
		  cancel: {
			label: '{{Annuler}}',
			className: 'warning',
			callback: {
			  	click: function(event) {
					jeedomUtils.showAlert({
						title: "SSH Manager	- Add New SSH Conf",
						message: "Click :: Cancel", 
						level: 'danger',
						emptyBefore: false
					});
					jeeDialog.get('#mod_addnewssh').destroy()
			  	}
			}
		  }
		}
	  })
});

document.querySelector('.eqLogicAttr[data-l2key="pull_use_custom"]').addEventListener('change', function () {
	if (this.checked) {
		document.querySelector('.pull_class').style.display = "block";
	} else {
		document.querySelector('.pull_class').style.display = "none";
	}
});

document.querySelector('.eqLogicAttr[data-l2key="auth-method"]').addEventListener('change', function () {
	if (this.selectedIndex == 0) {
		document.querySelector('.remote-pwd').style.display = "block";
		document.querySelector('.remote-key').style.display = "none";
	} else if (this.selectedIndex == 1) {
		document.querySelector('.remote-pwd').style.display = "none";
		document.querySelector('.remote-key').style.display = "block";
	}
});

function toggleSSHPassword() {
	var sshPasswordIcon = document.getElementById('btnToggleSSHPasswordIcon');
	var sshPasswordField = document.getElementById('ssh-password');
	sshPasswordIcon.className = sshPasswordField.type === "password" ? "fas fa-eye-slash" : "fas fa-eye";
	sshPasswordField.type = sshPasswordField.type === "password" ? "text" : "password";

}

function toggleSSHPassphrase() {
	var sshPassphraseIcon = document.getElementById('btnToggleSSHPassphraseIcon');
	var sshPassphraseField = document.getElementById('ssh-passphrase');
	sshPassphraseIcon.className = sshPassphraseField.type === "password" ? "fas fa-eye-slash" : "fas fa-eye";
	sshPassphraseField.type = sshPassphraseField.type === "password" ? "text" : "password";
}
