import $ from 'jquery';
import 'popper.js';
import 'bootstrap';

import bsCustomFileInput from 'bs-custom-file-input';
import 'flatpickr';
import 'flatpickr/dist/l10n/es.js';
import Swal from "sweetalert2";

import '../css/app-bundle.scss';

import { toggleAll, bulkSubmitBtnManage } from './petkopara-crud-generator-webpack';

function InitAppBundle() {
    $('#check-all-thead').on('click', ($e) => toggleAll($e.target) );
    $('.check-all-row').on('click', () => bulkSubmitBtnManage() );

    bsCustomFileInput.init();
    /* Mostrar el boton de visualizacion en los fileInputs */
    let fileInputs = $(".custom-file");
    let i = 0;
    fileInputs.each(function () {

        const fileId        = $(this).find('input').data('fileid');
        const fileUrl       = $(this).find('input').data('fileurl');
        const fileDisabled  = $(this).find('input').attr('disabled') === 'disabled';
        const fileCodigo    = $(this).find('input').data('filecodigo');

        // console.log('fileId: ', fileId, 'fileUrl: ', fileUrl, 'fileDisabled: ', fileDisabled, 'fileCodigo: ', fileCodigo); // TODO:  borrar

        $(this).before(`<div id="input-group-file${i}" class="input-group"></div>`)
        // console.log($(this)); // TODO:  borrar
        $(this).detach();
        $(this).appendTo("#input-group-file" + i++);

        if (fileUrl) {
            $(this).after(
                `<div class="input-group-append download-file">
                    <a href="${fileUrl}" target="_blank" class="btn btn-outline-primary"><span class="fa fa-download"></a>
                </div>`
            );
            const formName = $(this).closest('form').attr('name');
            $(this).find('.custom-file-label').html($(this).find('input').attr('placeholder'));
            const deletedFilesInput = $(this).closest('form').find(`:hidden#${formName}_deletedFiles`);
            if ( deletedFilesInput.length > 0 && !fileDisabled ) {
                $(this).after(
                    `<div class="input-group-append delete-file">
                    <a 
                        id="delete-file-${fileId}" 
                        class="btn btn-outline-danger"
                    >
                    <span class="fa fa-trash"></a>
                </div>`
                );
                $(`#delete-file-${fileId}`).click((e) => {
                    e.preventDefault();
                    const fileCodigo    = $(this).find('input').data('filecodigo');
                    const fileRequired  = $(this).find('input').data('filerequired');

                    Swal.fire({
                        title: '¿Está seguro de realizar la acción?',
                        text: "Seleccione una opción",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Si',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $(this).find('.custom-file-input').val('');
                            // $(this).find('.custom-file-input').attr('placeholder', '');
                            $(this).find('.custom-file-label').html('<b>El archivo será eliminado cuando guarde el formulario</b>');
                            $(this).parent().find('.download-file').hide();
                            $(this).parent().find('.delete-file').hide();
                            if (fileRequired) {
                                $(this).closest('.form-group').find(`label`).addClass('required');
                                $(this).find('input:file').attr('required', 'required');
                            }

                            const formName = $(this).closest('form').attr('name');
                            const deletedFilesInput = $(this).closest('form').find(`:hidden#${formName}_deletedFiles`);
                            let deletedFiles = JSON.parse(deletedFilesInput.val());
                            (deletedFiles.indexOf(fileCodigo) === -1) && deletedFiles.push(fileCodigo) && deletedFilesInput.val(JSON.stringify(deletedFiles));

                            Swal.fire(
                                '¡Se ha marcado para eliminar!',
                                'Para confirmar la acción guarde los cambios del formulario.',
                                'success'
                            )
                        }
                    });
                });
            }
        }
        else {
            $(this).find('.custom-file-label').html('Sin archivo seleccionado');
        }
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

    $("#reset-filters").on('click', function(e) {
        e.preventDefault();
        $("#filtros button[value=reset]").trigger('click');
    });
    $("#filters").on('show.bs.collapse', function() {
        $("#filters-btn-mostrar").html('Ocultar filtros');
    });
    $("#filters").on('hide.bs.collapse', function() {
        $('#filters-btn-mostrar').html('Mostrar filtros');
    });
    if ($("#filters").hasClass('show')) {
        $("#filters-btn-mostrar").html('Ocultar filtros');
    }
    else {
        $('#filters-btn-mostrar').html('Mostrar filtros');
    }

    $("#filtros").find('input, select, textarea').each(function() {
        let valor = $(this).val().trim();
        let hayValor = false;

        console.log('Campo ' + $(this).attr('name'));
        // Verifica si el campo tiene un valor no vacío y si no es un select con la opción vacía
        if (valor !== '' && !(this.tagName === 'SELECT' && valor === '')) {

            hayValor = true;
            console.log('Hay valor en el campo ' + $(this).attr('name'));
            // $("#filters").collapse('show');
            $(".navbarAlert").removeClass('d-none');
            return false; // corta el loop si ya encontró un campo con valor
        }
    });
}

$( () => {
    InitAppBundle();
})

document.addEventListener("turbo:frame-load", function (e) {
    InitAppBundle();
})



/***
 <div class="input-group mb-3">
     {{ form_label(form.certificado_mipymeFile) }}
     <div id="input-group-file0" class="input-group">
         <div class="custom-file">
             {{ form_widget(form.certificado_mipymeFile) }}
             <label for="" class="custom-file-label"></label>
         </div>
     </div>
 </div>

 NOTA: modificar input-group-file0 con el nro que corresponda, arrancando desde 0 y aumentando

 */