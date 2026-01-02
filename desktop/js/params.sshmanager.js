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
    if (window.sshManagerParamsInit) return;
    window.sshManagerParamsInit = true;

    // Use configuration constants injected by PHP via sendVarToJS()
    // These are set by sshmanager.php, mod.add.sshmanager.php, and mod.commands.php
    
    // Configuration keys (attribute names in data-l2key)
    const CONFIG_AUTH_METHOD = window.CONFIG_AUTH_METHOD || 'auth-method';
    const CONFIG_SSH_KEY = window.CONFIG_SSH_KEY || 'ssh-key';
    
    // Authentication method values (user choices)
    const AUTH_METHOD_PASSWORD = window.AUTH_METHOD_PASSWORD || 'password';
    const AUTH_METHOD_SSH_KEY = window.AUTH_METHOD_SSH_KEY || 'ssh-key';
    const AUTH_METHOD_AGENT = window.AUTH_METHOD_AGENT || 'agent';

    // DOM Selectors constants (better minification + no string repetition + immutable)
    const SELECTORS = Object.freeze({
        AUTH_METHOD: `.eqLogicAttr[data-l2key="${CONFIG_AUTH_METHOD}"]`,
        SSH_KEY_FIELD: `[data-l2key="${CONFIG_SSH_KEY}"]`,
        REMOTE_PWD: '.remote-pwd',
        REMOTE_KEY: '.remote-key',
        PWD_CONTAINER: '#pwdorpassphrase',
        REFORMAT_BTN: '.bt_reformatSSHKey'
    });

    // Initialize once DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initParams);
    } else {
        initParams();
    }

    function initParams() {
        // Use event delegation for authentication method change (works everywhere)
        document.addEventListener('change', function(event) {
            const authMethodSelect = event.target.closest(SELECTORS.AUTH_METHOD);
            if (authMethodSelect) {
                handleAuthMethodChange(event);
            }
        });

        // Event delegation for password/passphrase visibility toggle
        document.addEventListener('click', handlePasswordToggle);

        // Event delegation for reformatSSHKey button
        document.addEventListener('click', handleReformatSSHKey);
        
        // Watch for authentication method select to appear/change (for Jeedom's setValues)
        const observer = new MutationObserver(() => {
            const authMethodSelect = document.querySelector(SELECTORS.AUTH_METHOD);
            if (authMethodSelect && authMethodSelect.value) {
                handleAuthMethodChange({ currentTarget: authMethodSelect });
            }
        });
        
        // Observe the entire page container for DOM changes
        observer.observe(document.body, { 
            childList: true, 
            subtree: true, 
            attributes: true, 
            attributeFilter: ['value']
        });
    }

    function handleAuthMethodChange(event) {
        // Compare against actual values (more robust than selectedIndex)
        const selectedMethod = event.currentTarget.value;
        
        // Recapture elements each time (important for modals with dynamic DOM)
        const remotePwd = document.querySelector(SELECTORS.REMOTE_PWD);
        const remoteKey = document.querySelector(SELECTORS.REMOTE_KEY);
        
        switch (selectedMethod) {
            case AUTH_METHOD_PASSWORD:
                remotePwd?.seen();
                remoteKey?.unseen();
                break;
            case AUTH_METHOD_SSH_KEY:
                remotePwd?.unseen();
                remoteKey?.seen();
                break;
            case AUTH_METHOD_AGENT:
                remotePwd?.unseen();
                remoteKey?.unseen();
                break;
            default:
                // Fallback for unknown values
                remotePwd?.unseen();
                remoteKey?.unseen();
        }
    }

    function handlePasswordToggle(event) {
        const toggleBtn = event.target.closest('a.bt_togglePass');
        if (!toggleBtn) return;
        
        event.stopPropagation();
        
        const input = toggleBtn.closest('.input-group').querySelector('input');
        const icon = toggleBtn.querySelector('.fas');
        
        // Toggle input type
        input.type = input.type === 'password' ? 'text' : 'password';
        
        // Toggle icon
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    }

    function handleReformatSSHKey(event) {
        if (!event.target.closest(SELECTORS.REFORMAT_BTN)) return;
        
        event.preventDefault();
        reformatSSHKey();
    }

    /**
     * Reformats SSH key to 64-character blocks (PEM format standard)
     * @returns {void}
     */
    function reformatSSHKey() {
        const sshKeyField = document.querySelector(SELECTORS.SSH_KEY_FIELD);
        if (!sshKeyField) {
            console.error('SSH Key field not found');
            return;
        }
        
        const sshKey = sshKeyField.value;
        
        // Regular expressions for header and footer
        const headerRegex = /-----BEGIN [A-Z ]+ KEY-----/;
        const footerRegex = /-----END [A-Z ]+ KEY-----/;
        
        const headerMatch = sshKey.match(headerRegex);
        const footerMatch = sshKey.match(footerRegex);
        
        if (!headerMatch || !footerMatch) {
            jeedomUtils.showAlert({
                title: 'SSH Manager - Format SSH Key',
                message: '{{Format de la clé SSH invalide !}}',
                level: 'warning',
                emptyBefore: false
            });
            console.error('Invalid SSH key format');
            return;
        }
        
        const header = headerMatch[0];
        const footer = footerMatch[0];
        
        // Remove header/footer and trim
        const keyBody = sshKey.replace(header, '').replace(footer, '').trim();
        
        // Check if already formatted (all lines ≤ 64 chars)
        const isFormatted = keyBody.split('\n').every(line => line.length <= 64);
        
        if (!isFormatted) {
            // Format in 64-char blocks
            const formattedKeyBody = keyBody.replace(/(.{64})/g, '$1\n');
            const formattedKey = `${header}\n${formattedKeyBody}\n${footer}`;
            
            sshKeyField.value = formattedKey;
            
            jeedomUtils.showAlert({
                title: 'SSH Manager - Format SSH Key',
                message: 'Formatage de la clé SSH en blocs de 64 caractères :: OK',
                level: 'success',
                emptyBefore: false
            });
        } else {
            jeedomUtils.showAlert({
                title: 'SSH Manager - Format SSH Key',
                message: '{{La clé SSH est déjà formatée en blocs de 64 caractères !}}',
                level: 'info',
                emptyBefore: false
            });
        }
    }

})();