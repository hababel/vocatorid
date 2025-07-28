document.addEventListener('DOMContentLoaded', function () {
    // Objeto global para gestionar los inputs de teléfono
    window.phoneInputManager = {
        instances: {},
        
        // --- INICIO DE CAMBIOS ---
        // Nueva función para establecer el valor de un campo de teléfono internacional
        setPhoneNumber: function(phoneInputId, fullNumber) {
            const instance = this.instances[phoneInputId];
            if (!instance || !fullNumber) return;

            const { countrySelect, phoneInput, countries } = instance;

            // Encontrar el código de país que mejor coincida
            let bestMatch = null;
            countries.forEach(country => {
                if (fullNumber.startsWith(country.dial_code)) {
                    if (!bestMatch || country.dial_code.length > bestMatch.dial_code.length) {
                        bestMatch = country;
                    }
                }
            });

            if (bestMatch) {
                countrySelect.value = bestMatch.code;
                const nationalNumber = fullNumber.substring(bestMatch.dial_code.length);
                phoneInput.value = nationalNumber;
            } else {
                // Si no hay coincidencia, simplemente mostrar el número
                phoneInput.value = fullNumber;
            }
             // Actualizar el valor oculto
            instance.updateFullNumber();
        }
        // --- FIN DE CAMBIOS ---
    };

    const PHONE_INPUTS_CONFIG = [
        { formId: 'formRegistroAnonimo', phoneInputId: 'telefono', hiddenInputName: 'telefono' },
        { formId: 'formNuevoContacto', phoneInputId: 'telefono_nuevo', hiddenInputName: 'telefono' },
        { formId: 'formEditarContacto', phoneInputId: 'telefono_editar_visible', hiddenInputName: 'telefono_editar' },
        { formId: 'formAgregarInvitacion', phoneInputId: 'telefono_manual', hiddenInputName: 'telefono_manual' }
    ];

    async function initializePhoneInput(form, phoneInput, hiddenInputName) {
        if (!form || !phoneInput) return;

        const wrapper = document.createElement('div');
        wrapper.className = 'input-group';

        const countrySelect = document.createElement('select');
        countrySelect.className = 'form-select';
        countrySelect.style.maxWidth = '120px';

        const hiddenFullNumberInput = document.createElement('input');
        hiddenFullNumberInput.type = 'hidden';
        hiddenFullNumberInput.name = hiddenInputName;

        phoneInput.parentNode.insertBefore(wrapper, phoneInput);
        wrapper.appendChild(countrySelect);
        wrapper.appendChild(phoneInput);
        form.appendChild(hiddenFullNumberInput);

        phoneInput.placeholder = 'Número Móvil';
        phoneInput.required = true;

        try {
            const response = await fetch(URL_PATH + 'core/customassets/js/countries.json');
            const countries = await response.json();

            countries.forEach(country => {
                const option = document.createElement('option');
                option.value = country.code;
                option.dataset.dialCode = country.dial_code;
                // --- INICIO DE CAMBIOS ---
                // Corregido para mostrar bandera y código correctamente
                option.textContent = `${country.flag} ${country.dial_code}`;
                // --- FIN DE CAMBIOS ---
                countrySelect.appendChild(option);
            });
            
            // --- INICIO DE CAMBIOS ---
            // Guardar la instancia para acceso futuro
            const instance = {
                countrySelect,
                phoneInput,
                countries,
                updateFullNumber: function() {
                    const selectedOption = countrySelect.options[countrySelect.selectedIndex];
                    const dialCode = selectedOption.dataset.dialCode;
                    const phoneNumber = phoneInput.value.replace(/\D/g, '');
                    hiddenFullNumberInput.value = dialCode + phoneNumber;
                }
            };
            window.phoneInputManager.instances[phoneInput.id] = instance;
            // --- FIN DE CAMBIOS ---

            try {
                const geoResponse = await fetch('https://ipinfo.io/json');
                const geoData = await geoResponse.json();
                if (geoData.country) {
                    countrySelect.value = geoData.country;
                }
            } catch (error) {
                console.warn('No se pudo detectar la geolocalización, se usará un valor por defecto.');
                countrySelect.value = 'CO';
            }
            
            countrySelect.addEventListener('change', instance.updateFullNumber);
            phoneInput.addEventListener('input', instance.updateFullNumber);

            instance.updateFullNumber();

        } catch (error) {
            console.error('Error al cargar la lista de países:', error);
        }
    }

    PHONE_INPUTS_CONFIG.forEach(config => {
        const form = document.getElementById(config.formId);
        const phoneInput = document.getElementById(config.phoneInputId);
        if (form && phoneInput) {
            initializePhoneInput(form, phoneInput, config.hiddenInputName);
        }
    });
});