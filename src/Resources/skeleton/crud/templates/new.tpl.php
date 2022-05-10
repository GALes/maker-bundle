<?= $custom_helper->getHeadPrintCode('Alta de ' . $custom_helper->asHumanWords($entity_class_name), $template_base_twig ) ?>


{% block body %}

<div class="row mt-3">
    {{ include('bundles/GALesMaker/_components/_flashMessages.html.twig') }}
    <div class="col-lg-12 mt-2">
        <h4>Alta de <?=$custom_helper->asHumanWords($entity_class_name) ;?> <span class="fa fa-file" aria-hidden="true"></span> </h4>
    </div>
</div>

<div class="">

    {{ form_start(form, { 'action': path('<?= $route_name; ?>_new') }) }}
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
