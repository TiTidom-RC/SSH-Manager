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
    if (window.sshManagerCommandsInit) return;
    window.sshManagerCommandsInit = true;

    // Commands cache (shared between functions)
    let commands = {};

    // Initialize once DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCommands);
    } else {
        initCommands();
    }

    function initCommands() {
        document.addEventListener('loadSelectCommands', () => buildSelectCommands(''));
        
        const selectCmd = document.querySelector('.selectCmdTemplate[data-l1key="ssh-select"]');
        if (selectCmd) {
            selectCmd.addEventListener("change", handleCommandChange);
        }
        
        triggerLoadSelectCommands();
    }

    function handleCommandChange(event) {
        const tr = event.target.closest("#mod_commands");
        const value = event.target.value;
        
        if (value == "") {
            tr.querySelector('.cmdAttr[data-l1key="name"]').value = "";
            tr.querySelector('.cmdAttr[data-l1key="description"]').value = "";
            tr.querySelector('.cmdAttr[data-l1key="ssh-command"]').value = "";
            tr.querySelector('.cmdAttr[data-l1key="type"]').value = "";
            tr.querySelector('.cmdAttr[data-l1key="subtype"]').value = "";
        } else {
            tr.querySelector('.cmdAttr[data-l1key="name"]').value = commands[value]["name"];
            tr.querySelector('.cmdAttr[data-l1key="description"]').value = commands[value]["description"];
            tr.querySelector('.cmdAttr[data-l1key="ssh-command"]').value = commands[value]["command"];
            tr.querySelector('.cmdAttr[data-l1key="type"]').value = commands[value]["type"];
            tr.querySelector('.cmdAttr[data-l1key="subtype"]').value = commands[value]["subtype"];
        }
    }

    function triggerLoadSelectCommands() {
        document.dispatchEvent(new Event('loadSelectCommands'));
    }

    /**
     * Builds and populates a select element with SSH commands.
     *
     * @param {string} [currentValue] - The current value to be selected in the dropdown. If not provided, the current value of the select element will be used.
     * @returns {void}
     *
     * @description
     * This function fetches SSH command templates via an AJAX request and populates a select element with these commands.
     * If the `currentValue` parameter is not provided, the function will use the current value of the select element.
     * The function handles errors by displaying an alert message.
     *
     * @example
     * buildSelectCommands(); // Populates the select element with SSH commands and retains the current selection.
     *
     * @example
     * buildSelectCommands('someCommandKey'); // Populates the select element with SSH commands and sets 'someCommandKey' as the selected value.
     *
     * @async
     * @function buildSelectCommands
     */
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
                    commands = {};
                    
                    // Convert to array and sort by short_description
                    const sortedEntries = Object.entries(data.result).sort((a, b) => {
                        const descA = a[1]['short_description'].toLowerCase();
                        const descB = b[1]['short_description'].toLowerCase();
                        return descA.localeCompare(descB);
                    });
                    
                    // Add sorted options to select
                    for (const [key, value] of sortedEntries) {
                        commands[key] = value;
                        selectCmd.append(new Option(value['short_description'], key));
                    }
                    selectCmd.value = currentValue;
                }
            }
        });
    }

    // Expose buildSelectCommands globalement pour que d'autres scripts puissent l'appeler
    window.buildSelectCommands = buildSelectCommands;

})();