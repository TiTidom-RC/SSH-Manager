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

document.addEventListener('loadSelectCommands', buildSelectCommands);

function triggerLoadSelectCommands() {
  document.dispatchEvent(new Event('loadSelectCommands'));
}

document.querySelector('.selectCmdTemplate[data-l1key="ssh-select"').addEventListener("change", function(event) {
  var tr = event.target.closest("#mod_commands");
  var value = event.target.value;
  
  /* console.log(value);
  console.log(commands[value]);
  console.log(commands[value]["name"]);
  console.log(commands[value]["description"]);
  console.log(commands[value]["command"]); */

  if (value == "") {
    tr.querySelector('.cmdAttr[data-l1key="name"]').value = "";
    tr.querySelector('.cmdAttr[data-l1key="description"]').value = "";
    tr.querySelector('.cmdAttr[data-l1key="ssh-command"]').value = "";
    tr.querySelector('.cmdAttr[data-l1key="type').value = "";
    tr.querySelector('.cmdAttr[data-l1key="subtype').value = "";
  } else {
    tr.querySelector('.cmdAttr[data-l1key="name"]').value = commands[value]["name"];
    tr.querySelector('.cmdAttr[data-l1key="description"]').value = commands[value]["description"];
    tr.querySelector('.cmdAttr[data-l1key="ssh-command"]').value = commands[value]["command"];
    tr.querySelector('.cmdAttr[data-l1key="type').value = commands[value]["type"];
    tr.querySelector('.cmdAttr[data-l1key="subtype').value = commands[value]["subtype"];
  }
});

function buildSelectCommands(currentValue) {
  const selectCmd = document.querySelector('.selectCmdTemplate[data-l1key=ssh-select]');
  if (selectCmd === null) {
      return;
  }
  if (currentValue === undefined) {
      currentValue = selectCmd.value;
  }

  selectCmd.innerHTML = '';
  const option = document.createElement('option');
  option.text = '{{Sélectionner une commande}}';
  option.value = '';
  option.selected = true;
  selectCmd.add(option);

  return domUtils.ajax({
      type: 'POST',
      url: 'plugins/sshmanager/core/ajax/sshmanager.ajax.php',
      data: {
          action: "getTemplateCommands",
      },
      dataType: 'json',
      async: true,
      global: false,
      error: function (request, status, error) {
          handleAjaxError(request, status, error);
      },
      success: function (data) {
          if (data.state != 'ok') {
              jeedomUtils.showAlert({
                  title: "SSH Manager - Build Select Commands",
                  message: data.result,
                  level: 'danger',
                  emptyBefore: false
              });
              return;
          } else {
              commands = {}; // Initialize the commands object if not already initialized
              for (const [key, value] of Object.entries(data.result)) {
                commands[key] = value;
                selectCmd.append(new Option(value['short_description'], key));
              }
              selectCmd.value = currentValue;
          }
      }
  });
}

triggerLoadSelectCommands();