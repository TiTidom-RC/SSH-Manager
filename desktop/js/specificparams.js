document.querySelector('.eqLogicAttr[data-l2key="' + CONFIG_AUTH_METHOD + '"]').addEventListener('change', function () {
    if (this.selectedIndex == 0) {
        document.querySelector('.remote-pwd').style.display = "block";
        document.querySelector('.remote-key').style.display = "none";
    } else if (this.selectedIndex == 1) {
        document.querySelector('.remote-pwd').style.display = "none";
        document.querySelector('.remote-key').style.display = "block";
    } else {
        document.querySelector('.remote-pwd').style.display = "none";
        document.querySelector('.remote-key').style.display = "none";
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