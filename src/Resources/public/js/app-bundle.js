import $ from 'jquery';
import 'popper.js';
import 'bootstrap';

import 'selectize';
import bsCustomFileInput from 'bs-custom-file-input';
import 'flatpickr';
import 'flatpickr/dist/l10n/es.js';

import '../css/app-bundle.scss';

import { toggleAll, bulkSubmitBtnManage } from './petkopara-crud-generator-webpack';

// console.log('Hola desde GALesMakerBundle');

$( () => {
    $('#check-all-thead').on('click', ($e) => toggleAll($e.target) );
    $('.check-all-row').on('click', () => bulkSubmitBtnManage() );

    bsCustomFileInput.init();
    /* Mostrar el boton de visualizacion en los fileInputs */
    var fileInputs = $(".custom-file");
    var i = 0;
    fileInputs.each(function () {
        $(this).before(`<div id="input-group-file${i}" class="input-group"></div>`)
        $(this).detach();
        $(this).appendTo("#input-group-file" + i++);

        // var fileName = $(this).find('input').attr('placeholder');
        var fileUrl = $(this).find('input').data('fileurl');
        if (fileUrl) {
            $(this).after(
                `<div class="input-group-append">
                    <a href="${fileUrl}" target="_blank" class="btn btn-outline-primary"><span class="fa fa-download"></a>
                </div>`
            );
        }
    });

    $("select:not('.not-selectized')").selectize({
        create: true,
        sortField: 'text'
    });

    /* Mostrar los datepicker con y sin selección de hora */
    $(".datetimepicker").flatpickr({
        enableTime: true,altInput: true,
        altFormat: "d-m-Y H:i",
        dateFormat: "Y-m-d H:i",
        time_24hr:  true,
        locale: 'es',
    });

    $(".datepicker").flatpickr({
        enableTime: false,
        altFormat: "d-m-Y",
        dateFormat: "Y-m-d",
        time_24hr:  true,
        locale: 'es',
    });
})
