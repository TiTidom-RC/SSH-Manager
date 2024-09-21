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
    } else {
        document.querySelector('.remote-pwd').style.display = "none";
        document.querySelector('.remote-key').style.display = "none";
    }
});

// Events delegation for password and passphrase toggling:
document.getElementById('pwdorpassphrase')?.addEventListener('click', function(event) {
    var _target = null
    if (_target = event.target.closest('a.bt_togglePass')) {
      event.stopPropagation();
      var _el = event.target.matches('a.bt_togglePass') ? event.target : event.target.parentNode;
      var input = _el.closest('.input-group').querySelector('input');
      
      if (input.getAttribute('type') === 'password') {
          input.setAttribute('type', 'text');
      } else {
          input.setAttribute('type', 'password');
      }
  
      var icon = _el.querySelector('.fas');
      if (icon.classList.contains('fa-eye-slash')) {
          icon.classList.remove('fa-eye-slash');
          icon.classList.add('fa-eye');
      } else {
          icon.classList.remove('fa-eye');
          icon.classList.add('fa-eye-slash');
      }
      return;
    }
  })