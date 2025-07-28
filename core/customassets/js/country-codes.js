document.addEventListener('DOMContentLoaded', function () {
    const PHONE_INPUTS_CONFIG = [
        // --- Configuración para los distintos formularios ---
        // Registro Anónimo
        { formId: 'formRegistroAnonimo', phoneInputId: 'telefono', hiddenInputName: 'telefono' },
        // Añadir Contacto Manual (Libreta)
        { formId: 'formNuevoContacto', phoneInputId: 'telefono_nuevo', hiddenInputName: 'telefono' },
        // Editar Contacto
        { formId: 'formEditarContacto', phoneInputId: 'telefono_editar_visible', hiddenInputName: 'telefono_editar' },
        // Añadir Invitado Manual (Evento)
        { formId: 'formAgregarInvitacion', phoneInputId: 'telefono_manual', hiddenInputName: 'telefono_manual' }
    ];

    // Función para inicializar un selector de país para un campo de teléfono
    async function initializePhoneInput(form, phoneInput, hiddenInputName) {
        if (!form || !phoneInput) return;

        // 1. Crear la estructura HTML del selector
        const wrapper = document.createElement('div');
        wrapper.className = 'input-group';

        const countrySelect = document.createElement('select');
        countrySelect.className = 'form-select';
        countrySelect.style.maxWidth = '120px';

        const hiddenFullNumberInput = document.createElement('input');
        hiddenFullNumberInput.type = 'hidden';
        hiddenFullNumberInput.name = hiddenInputName;

        // Reemplazar el input original con la nueva estructura
        phoneInput.parentNode.insertBefore(wrapper, phoneInput);
        wrapper.appendChild(countrySelect);
        wrapper.appendChild(phoneInput);
        form.appendChild(hiddenFullNumberInput);

        phoneInput.placeholder = 'Número Móvil';
        phoneInput.required = true;

        // 2. Cargar datos y popular el selector
        try {
            const response = await fetch(URL_PATH + 'core/customassets/js/countries.json');
            const countries = await response.json();

            countries.forEach(country => {
                const option = document.createElement('option');
                option.value = country.code;
                option.dataset.dialCode = country.dial_code;
                option.textContent = `${country.flag} ${country.code} (${country.dial_code})`;
                countrySelect.appendChild(option);
            });

            // 3. Detectar el país del usuario
            try {
                // --- INICIO DE CAMBIOS ---
                // Se utiliza una API más confiable que no requiere clave para HTTPS
                const geoResponse = await fetch('https://ipinfo.io/json');
                const geoData = await geoResponse.json();
                // La nueva API devuelve el código en la propiedad "country"
                if (geoData.country) {
                    countrySelect.value = geoData.country;
                }
                // --- FIN DE CAMBIOS ---
            } catch (error) {
                console.warn('No se pudo detectar la geolocalización, se usará un valor por defecto.');
                countrySelect.value = 'CO'; // Fallback a Colombia
            }

            // 4. Función para actualizar el número completo
            function updateFullNumber() {
                const selectedOption = countrySelect.options[countrySelect.selectedIndex];
                const dialCode = selectedOption.dataset.dialCode;
                const phoneNumber = phoneInput.value.replace(/\D/g, ''); // Limpiar no dígitos
                hiddenFullNumberInput.value = dialCode + phoneNumber;
            }

            // 5. Añadir listeners para actualizar en tiempo real
            countrySelect.addEventListener('change', updateFullNumber);
            phoneInput.addEventListener('input', updateFullNumber);

            // Inicializar valor
            updateFullNumber();

        } catch (error) {
            console.error('Error al cargar la lista de países:', error);
        }
    }

    // Inicializar todos los campos de teléfono definidos en la configuración
    PHONE_INPUTS_CONFIG.forEach(config => {
        const form = document.getElementById(config.formId);
        const phoneInput = document.getElementById(config.phoneInputId);
        if (form && phoneInput) {
            initializePhoneInput(form, phoneInput, config.hiddenInputName);
        }
    });
});