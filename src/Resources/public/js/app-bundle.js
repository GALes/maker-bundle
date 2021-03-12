import $ from 'jquery';
import 'popper.js';
import 'bootstrap';

import 'selectize';
import bsCustomFileInput from 'bs-custom-file-input';

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
})
