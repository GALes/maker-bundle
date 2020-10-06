<?= $helper->getHeadPrintCode('Alta de ' . $entity_class_name) ?>


{% block body %}

<div class="row mt-5">
<?php include 'others/flash_messages.tpl.php' ?>
    <div class="col-lg-12">
        <h4>Alta de <?=$entity_twig_var_singular ;?> <span class="fa fa-file" aria-hidden="true"></span> </h4>
    </div>
</div>

<div class="">

    {{ form_start(form) }}
    {{ form_widget(form) }}
    <p>
        <button type="submit" name="submit" value="save" class="btn btn-primary">
            Guardar <span class="fa fa-check-circle" aria-hidden="true"></span>
        </button>
        <button type="submit" name="submit" value="saveAndAdd" class="btn btn-info">
            Guardar y Cargar Nuevo <span class="fa fa-plus" aria-hidden="true"></span>
        </button>
    </p>


    {{ form_end(form) }}

    <hr/>
    {% set hide_edit, hide_delete, hide_new = true, true, true %}
<?php include 'others/record_actions.tpl.php' ?>
</div>


{% endblock %}
