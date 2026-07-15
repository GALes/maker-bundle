<?= $custom_helper->getHeadPrintCode($custom_helper->asHumanWords($entity_class_name), $template_base_twig ) ?>

{% block body %}

{{ include('bundles/GALesMaker/_components/_flashMessages.html.twig') }}

<div class="mb-4">
    <h1 class="mb-page-title">Visualizaci&oacute;n de <?= $custom_helper->asHumanWords($entity_class_name) ?> <span class="fa fa-eye" aria-hidden="true"></span></h1>
</div>

<div class="mb-card">
    <div class="mb-card__body">
        <div class="row g-4">
<?php foreach ($entity_fields as $field): ?>
            <div class="col-md-6">
                <div class="mb-form-label"><?= $custom_helper->asHumanWords(ucfirst($field['fieldName'])) ?></div>
                <div>
                    {{ <?= $custom_helper->getEntityFieldPrintCode($entity_twig_var_singular, $field) ?> }}
                </div>
            </div>
<?php endforeach; ?>
        </div>

        <hr class="my-4"/>

        {% set hide_edit, hide_delete, hide_new = true, false, true %}
<?php include 'others/record_actions.tpl.php' ?>
    </div>
</div>

{% endblock %}
