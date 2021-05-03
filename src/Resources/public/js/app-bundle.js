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

    $("select").selectize({
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
