<?= $custom_helper->getHeadPrintCode('Alta de ' . $custom_helper->asHumanWords($entity_class_name), $template_base_twig ) ?>


{% block body %}

{{ include('bundles/GALesMaker/_components/_flashMessages.html.twig') }}

<div class="mb-4">
    <h1 class="mb-page-title">Alta de <?=$custom_helper->asHumanWords($entity_class_name) ;?> <span class="fa fa-file" aria-hidden="true"></span></h1>
</div>

<div class="mb-card">
    <div class="mb-card__body">

        {{ form_start(form, { 'action': path('<?= $route_name; ?>_new') }) }}
        {{ form_widget(form) }}
        <div class="d-flex flex-wrap gap-2 mt-3">
            <button type="submit" name="submit" value="save" class="mb-btn-primary">
                Guardar <span class="fa fa-check-circle" aria-hidden="true"></span>
            </button>
            <button type="submit" name="submit" value="saveAndAdd" class="mb-btn-outline">
                Guardar y Cargar Nuevo <span class="fa fa-plus" aria-hidden="true"></span>
            </button>
        </div>
        {{ form_end(form) }}

        <hr class="my-4"/>
        {% set hide_edit, hide_delete, hide_new = true, true, true %}
<?php include 'others/record_actions.tpl.php' ?>
    </div>
</div>

{% endblock %}
