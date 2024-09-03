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

document.querySelector(".eqLogicAttr[data-l2key='auth-method']").addEventListener('change', function (element) {
	if (element.selectedIndex == 0) {
		document.querySelector(".remote-pwd").style.display = "block";
		document.querySelector(".remote-key").style.display = "none";
	} else if (element.selectedIndex == 1) {
		document.querySelector(".remote-pwd").style.display = "none";
		document.querySelector(".remote-key").style.display = "block";
	}
});

function toggleSSHPassword() {
	var sshPasswordIcon = document.getElementById("btnToggleSSHPasswordIcon");
	var sshPasswordField = document.getElementById("ssh-password");
	sshPasswordIcon.className = sshPasswordField.type === "password" ? "fas fa-eye-slash" : "fas fa-eye";
	sshPasswordField.type = sshPasswordField.type === "password" ? "text" : "password";

}

function toggleSSHPassphrase() {
	var sshPassphraseIcon = document.getElementById("btnToggleSSHPassphraseIcon");
	var sshPassphraseField = document.getElementById("ssh-passphrase");
	sshPassphraseIcon.className = sshPassphraseField.type === "password" ? "fas fa-eye-slash" : "fas fa-eye";
	sshPassphraseField.type = sshPassphraseField.type === "password" ? "text" : "password";
}
