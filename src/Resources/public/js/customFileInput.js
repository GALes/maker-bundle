/**
 * Custom File Input Handler
 * Maneja la interacción de inputs de archivo con archivos existentes (Bootstrap 5)
 */

import $ from 'jquery';

/**
 * Inicializa el comportamiento de los inputs de archivo personalizados
 */
function initCustomFileInputs() {
    // Buscar todos los inputs de archivo que tengan archivo existente
    const fileInputs = $('input[type="file"]');

    fileInputs.each(function() {
        const fileInput = $(this);
        const fileInputId = fileInput.attr('id');
        const existingFile = fileInput.attr('data-existing-file');
        const fileUrl = fileInput.attr('data-file-url');

        // Solo procesar si tiene archivo existente
        if (!existingFile || !fileUrl || existingFile === '' || fileUrl === '') {
            return;
        }

        // Evitar inicializar múltiples veces
        if (fileInput.data('listener-added')) {
            return;
        }
        fileInput.data('listener-added', true);

        const displayInput = $(`#display-${fileInputId}`);
        const downloadBtn = $(`#download-btn-${fileInputId}`);

        // Al hacer clic en el input falso, abrir el selector de archivos
        if (displayInput.length) {
            displayInput.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Usar DOM nativo para abrir el selector
                const nativeInput = fileInput[0];
                if (nativeInput) {
                    nativeInput.click();
                }
            });

            // Soporte para teclado (accesibilidad)
            displayInput.on('keypress', function(e) {
                if (e.which === 13 || e.which === 32) { // Enter o Espacio
                    e.preventDefault();
                    const nativeInput = fileInput[0];
                    if (nativeInput) {
                        nativeInput.click();
                    }
                }
            });
        }

        // Cuando se selecciona un nuevo archivo
        fileInput.on('change', function(e) {
            if (e.target.files && e.target.files.length > 0) {
                // Ocultar input falso y mostrar input real con el archivo seleccionado
                if (displayInput.length) {
                    displayInput.hide();
                }
                fileInput.show();

                // Ocultar botón de descarga
                if (downloadBtn.length) {
                    downloadBtn.hide();
                }
            }
        });
    });
}

// Exportar para uso en app-bundle
export { initCustomFileInputs };