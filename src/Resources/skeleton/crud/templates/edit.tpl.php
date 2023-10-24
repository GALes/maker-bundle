<?= $custom_helper->getHeadPrintCode('Edición de ' . $custom_helper->asHumanWords($entity_class_name), $template_base_twig ) ?>

{% block body %}

<div class="row">
    {{ include('bundles/GALesMaker/_components/_flashMessages.html.twig') }}
    <div class="col-lg-12 mt-2">
        <h4>Edici&oacute;n de <?= $custom_helper->asHumanWords($entity_class_name) ?> <span class="fa fa-pencil" aria-hidden="true"></span> </h4>
    </div>
</div>

<div class="">
    {{ form_start( form, { 'action': path('<?= $route_name; ?>_edit', {'id': <?= $entity_twig_var_singular ?>.id }) } ) }}

    {{ form_widget(form) }}
    <p>
        <button type="submit" class="btn btn-outline-primary">
            <span class="fa fa-check" aria-hidden="true"></span> Guardar
        </button>
    </p>
    {{ form_end(form) }}

    <hr/>

    {% set hide_edit, hide_delete, hide_new = true, true, true %}
<?php include 'others/record_actions.tpl.php' ?>
</div>
{% endblock %}
