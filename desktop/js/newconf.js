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

document.querySelector('.eqLogicAttr[data-l2key="' + CONFIG_AUTH_METHOD + '"]').addEventListener('change', function () {
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

function saveNewSSH() {
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

	domUtils.ajax({
		type: 'POST',
		url: 'plugins/sshmanager/core/ajax/sshmanager.ajax.php',
		data: {
			action: 'addNewSSH',
			name: new_name,
			host: new_host,
			port: new_port,
			timeout: new_timeout,
			username: new_user,
			password: new_password,
			ssh_key: new_key,
			ssh_passphrase: new_passphrase,
			auth_method: new_auth_method
		},
		dataType: 'json',
		success: function (data) {
			if (data.state != 'ok') {
				jeedomUtils.showAlert({
					title: "SSH Manager - Add New SSH Conf",
					message: "Error: " + data.result,
					level: 'danger',
					emptyBefore: false
				});
			} else {
				jeedomUtils.showAlert({
					title: "SSH Manager - Add New SSH Conf",
					message: "Success: " + data.result,
					level: 'success',
					emptyBefore: false
				});
			}
		}
	});

	jeedomUtils.showAlert({
		title: "SSH Manager - Add New SSH Conf",
		message: "Click (Sauvegarder) :: " + new_name + " - " + new_host + " - " + new_port + " - " + new_timeout + " - " + new_user + " - " + new_password + " - " + new_key + " - " + new_passphrase + " - " + new_auth_method,
		level: 'danger',
		emptyBefore: false
	});
}