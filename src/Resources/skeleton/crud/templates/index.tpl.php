<?= $custom_helper->getHeadPrintCode('Listado de ' . $custom_helper->asHumanWords($entity_class_name), $template_base_twig ); ?>

{% block body %}

{{ include('bundles/GALesMaker/_components/_flashMessages.html.twig') }}

<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
    <h1 class="mb-page-title">Listado de <?= $custom_helper->asHumanWords($entity_twig_var_plural) ?></h1>
    <a class="mb-btn-primary" href="{{ path('<?= $route_name ?>_new') }}">
        <span class="fa fa-plus" aria-hidden="true"></span> Nuevo
    </a>
</div>

<?php
if ( $filter_type === 'input' )
    include 'others/multi_search_header.tpl.php';
else if ( $filter_type === 'form' )
    include 'others/form_filter_header.tpl.php';
?>
{%- if form_errors(filterForm) %}
<div class="alert alert-danger form-errors">
    {{ form_errors(filterForm) }}
</div>
{% endif %}

<div class="table-responsive">
    <form method="POST" id="bulk_form" action="{{ path('<?=$route_name ?>_bulk_action') }}">
    <table class="mb-table">
        <thead>
            <tr>
                <th><input id="check-all-thead" type="checkbox" class="check-all" aria-label="Seleccionar todos"></th>
                {% import "bundles/GALesMaker/_macros/_th_sortable.html.twig" as macros %}
<?php foreach ($entity_fields as $field): $fieldName = $field['fieldName'] ?>
                <th>{{macros.th_sortable('<?= $fieldName ?>', app.request.get('pcg_sort_col'), app.request.get('pcg_sort_order') , '<?= $route_name ?>_index', '<?= ucfirst($custom_helper->asHumanWords($field['fieldName'])) ?>')}}</th>
<?php endforeach; ?>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        {% for <?=$entity_twig_var_singular ;?> in <?=$entity_twig_var_plural ;?> %}
            <tr>
                <td data-label="Seleccionar"><input type="checkbox" name="ids[]" class="check-all check-all-row" value="{{<?=$entity_twig_var_singular ;?>.<?=$entity_identifier ?>}}" aria-label="Seleccionar registro"/></td>
<?php foreach ($entity_fields as $field): ?>
                <td data-label="<?= ucfirst($custom_helper->asHumanWords($field['fieldName'])) ?>">{{ <?= $custom_helper->getEntityFieldPrintCode($entity_twig_var_singular, $field) ?> }}</td>
<?php endforeach; ?>
                <td data-label="Acciones" class="actions">
<?php include 'others/actions.tpl.php' ?>
                </td>
            </tr>
        {% endfor %}
        </tbody>
    </table>
    </form>
</div>

<div class="d-flex align-items-center flex-wrap gap-3 mt-3">
    <div class="d-flex align-items-center gap-2">
        <select class="form-select form-select-sm w-auto not-selectized" name="bulk_action" aria-label="Acción masiva">
            <option value="delete">ELIMINAR</option>
        </select>
        <input type="submit" id='bulkSubmitBtn' onclick="return confirm('¿Está seguro?')" form="bulk_form"
               class="btn btn-sm btn-outline-danger" disabled value="APLICAR">
    </div>
    <span class="totalOfRecordsString mb-form-hint">{{ totalOfRecordsString }}</span>
    <div class="ms-auto">
        {{ pagerHtml|raw }}
    </div>
</div>
{% endblock %}
