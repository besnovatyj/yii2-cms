/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

// https://gist.github.com/jpatters/4553139
class PasswordDecoder {
    constructor(parentId) {
        this.parentElement = document.getElementById(parentId);
        if (!this.parentElement) {
            throw new Error(`Element with ID ${parentId} not found`);
        }
        this.init();
    }

    init() {
        // Create form elements
        const form = document.createElement('form');
        const input = document.createElement('input');
        const button = document.createElement('button');
        const output = document.createElement('div');

        // Set attributes
        input.type = 'text';
        input.placeholder = 'Enter encrypted password';
        button.type = 'submit';
        button.textContent = 'Decode';
        output.id = 'decodedOutput';

        // Style elements
        form.style.margin = '20px';
        input.style.padding = '8px';
        input.style.marginRight = '10px';
        button.style.padding = '8px 16px';
        output.style.marginTop = '10px';

        // Add event listener
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const decoded = this.heidiDecode(input.value);
            output.textContent = `Decoded password: ${decoded}`;
        });

        // Append elements
        form.appendChild(input);
        form.appendChild(button);
        this.parentElement.appendChild(form);
        this.parentElement.appendChild(output);
    }

    heidiDecode(hex) {
        var str = '';
        var shift = parseInt(hex.substr(-1));
        hex = hex.substr(0, hex.length - 1);
        for (var i = 0; i < hex.length; i += 2)
            str += String.fromCharCode(parseInt(hex.substr(i, 2), 16) - shift);
        return str;
    }
}
