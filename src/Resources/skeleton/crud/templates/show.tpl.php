<?= $custom_helper->getHeadPrintCode($custom_helper->asHumanWords($entity_class_name), $template_base_twig ) ?>

{% block body %}

<div class="row mt-3">
<?php include 'others/flash_messages.tpl.php' ?>
    <div class="col-lg-12 mt-2">
        <h4>Visualizaci&oacute;n de <?= $custom_helper->asHumanWords($entity_class_name) ?> <span class="fa fa-eye" aria-hidden="true"></span>  </h4>
    </div>
</div>

<div class="row">

<?php foreach ($entity_fields as $field): ?>
    <div class="col-md-6">
        <p><strong><?= $custom_helper->asHumanWords(ucfirst($field['fieldName'])) ?></strong></p>
        <p>
            {{ <?= $custom_helper->getEntityFieldPrintCode($entity_twig_var_singular, $field) ?> }}
        </p>

    </div>
<?php endforeach; ?>

</div>

<hr/>

{% set hide_edit, hide_delete, hide_new= false, false, false %}
<?php include 'others/record_actions.tpl.php' ?>

{% endblock %}
