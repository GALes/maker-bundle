<?= $custom_helper->getHeadPrintCode('Edición de ' . $custom_helper->asHumanWords($entity_class_name), $template_base_twig ) ?>

{% block body %}

{{ include('bundles/GALesMaker/_components/_flashMessages.html.twig') }}

<div class="mb-4">
    <h1 class="mb-page-title">Edici&oacute;n de <?= $custom_helper->asHumanWords($entity_class_name) ?> <span class="fa fa-pencil" aria-hidden="true"></span></h1>
</div>

<div class="mb-card">
    <div class="mb-card__body">
        {{ form_start( form, { 'action': path('<?= $route_name; ?>_edit', {'id': <?= $entity_twig_var_singular ?>.id }) } ) }}

        {{ form_widget(form) }}
        <div class="d-flex flex-wrap gap-2 mt-3">
            <button type="submit" class="mb-btn-primary">
                <span class="fa fa-check" aria-hidden="true"></span> Guardar
            </button>
        </div>
        {{ form_end(form) }}

        <hr class="my-4"/>

        {% set hide_edit, hide_delete, hide_new = true, true, true %}
<?php include 'others/record_actions.tpl.php' ?>
    </div>
</div>
{% endblock %}
