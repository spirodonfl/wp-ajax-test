class Orb {
    constructor() {
        // Grab all elements
        this.returnElement = document.querySelector('#return');
        this.nameField = document.querySelector('#full_name');
        this.submitAjaxElement = document.querySelector('#submit_ajax');
        this.submitLegitElement = document.querySelector('#submit_legit'); // Nothing to do here I guess

        // Initialize name value
        this.name = '';

        // Bind element buttons
        this.bindSubmit();
    }

    /**
     * Binds the ajax submit button and makes it so it doesn't submit to backend
     * 
     * @return {boolean} True or false depending on existence of element on page
     */
    bindSubmit() {
        if (this.submitAjaxElement instanceof HTMLElement) {
            this.submitAjaxElement.addEventListener('click', (event) => {
                event.preventDefault();
                this.submitRequest();
                return false;
            });
            return true;
        }
        return false;
    }

    /**
     * Simply grabs the name from the form
     * 
     * @return {boolean} True or false depending on existence of element on page
     */
    getName() {
        if (this.nameField instanceof HTMLElement) {
            this.name = this.nameField.value;
            return true;
        }
        return false;
    }

    /**
     * Makes a fetch API call to the orb backend
     * 
     * @return {void}
     */
    submitRequest() {
        if (this.getName()) {
            this.submitAjaxElement.innerHTML = 'Submitting request';
            this.submitAjaxElement.setAttribute('disabled', 'disabled');
            fetch('https://orbisius.com/apps/qs_cmd/?json', {
                method: 'POST',
                cache: 'no-cache',
                credentials: 'omit',
                //headers: {
                //    'content-type': 'application/json'
                //},
                method: 'POST',
                mode: 'cors',
                redirect: 'follow',
                referrer: 'no-referrer',
                body: JSON.stringify({name: this.name}),
            }).then((response) => {
                if (response.ok) {
                    return response.json();
                } else {
                    throw new Error('Bad request!');
                }
            }).then((json) => {
                if (json.status === 1) {
                    let dataRows = '';
                    for (let i in json) {
                        if (i !== 'raw_post') {
                            dataRows += `<tr><th scope="row">${i}</th><td>${json[i]}</td></tr>`;
                        } else {
                            dataRows += `<tr><th scope="row">${i}</th><td>${JSON.stringify(json[i])}</td></tr>`;
                        }
                    }
                    this.returnElement.innerHTML = `
<table class="form-table">
    ${dataRows}
</table>
`;
                } else {
                    this.returnElement.innerHTML = '<strong>Error making the request!</strong>';
                }
                this.submitAjaxElement.removeAttribute('disabled');
                this.submitAjaxElement.innerHTML = 'Submit (Ajax Style)!';
            }).catch((error) => {
                console.log(error);
                this.submitAjaxElement.removeAttribute('disabled');
                this.submitAjaxElement.innerHTML = 'Submit (Ajax Style)!';
            });
        } else {
            this.nameField.classList.add('error');
            this.nameField.value = 'Please enter a name here';
        }
    }
}

window.addEventListener('load', () => {
    const orb = new Orb();
});