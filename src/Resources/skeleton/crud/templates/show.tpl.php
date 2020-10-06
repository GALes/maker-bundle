<?= $helper->getHeadPrintCode($entity_class_name) ?>

{% block body %}

<div class="row mt-5">
<?php include 'others/flash_messages.tpl.php' ?>
    <div class="col-lg-12">
        <h4>Visualizaci&oacute;n de <?= $entity_twig_var_singular ?> <span class="fa fa-eye" aria-hidden="true"></span>  </h4>
    </div>
</div>

<div class="row">

<?php foreach ($entity_fields as $field): ?>
    <div class="col-md-6">
        <p><strong><?= ucfirst($field['fieldName']) ?></strong></p>
        <p>
            {{ <?= $helper->getEntityFieldPrintCode($entity_twig_var_singular, $field) ?> }}
        </p>

    </div>
<?php endforeach; ?>

</div>

<hr/>

{% set hide_edit, hide_delete, hide_new= false, false, false %}
<?php include 'others/record_actions.tpl.php' ?>

{% endblock %}
