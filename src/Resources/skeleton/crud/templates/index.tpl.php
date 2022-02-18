<?= $custom_helper->getHeadPrintCode('Listado de ' . $custom_helper->asHumanWords($entity_class_name), $template_base_twig ); ?>

{% block body %}


<div class="row mt-5">
<?php include 'others/flash_messages.tpl.php' ?>
    <div class="col-lg-12">
        <h4>Listado de <?= $custom_helper->asHumanWords($entity_twig_var_plural) ?></h4>
    </div>

<?php
if ( $filter_type === 'input' )
    include 'others/multi_search_header.tpl.php';
else if ( $filter_type === 'form' )
    include 'others/form_filter_header.tpl.php';
?>

    <div class="col-md-12">
        {%- if form_errors(filterForm) %}
        <div class="alert alert-block alert-error fade in form-errors">
            {{ form_errors(filterForm) }}
        </div>
        {% endif %}
    </div>

</div> <!-- /#top -->

<div class="row">

    <div class="table-responsive col-md-12">
        <form method="POST" id="bulk_form" action={{ path('<?=$route_name ?>_bulk_action') }}>
        <table class="table table-striped table-hover table-bordered" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th width="20px"><input id="check-all-thead" type="checkbox" class="check-all"></th>

                {% import "@GALesMaker/macros/th_sortable.html.twig" as macros %}
<?php foreach ($entity_fields as $field): $fieldName = $field['fieldName'] ?>
    <?php if (isset($field['type']) && $field['type'] === 2): $fieldName = $field['fieldName'] . '.' . $field['orderBy'] ?><?php endif ?>
                    <th>{{macros.th_sortable('<?= $fieldName ?>', app.request.get('pcg_sort_col'), app.request.get('pcg_sort_order') , '<?= $route_name ?>_index', '<?= ucfirst($custom_helper->asHumanWords($field['fieldName'])) ?>')}}</th>
<?php endforeach; ?>
                    <th width = "130px">Acciones</th>
                </tr>
            </thead>
            <tbody>
            {% for <?=$entity_twig_var_singular ;?> in <?=$entity_twig_var_plural ;?> %}
                <tr>
                    <td><input type="checkbox" name="ids[]" onclick='bulkSubmitBtnManage();' class="check-all check-all-row" value="{{<?=$entity_twig_var_singular ;?>.<?=$entity_identifier ?>}}"/></td>
<?php foreach ($entity_fields as $field): ?>
                    <td>{{ <?= $custom_helper->getEntityFieldPrintCode($entity_twig_var_singular, $field) ?> }}</td>
<?php endforeach; ?>
                    <td class="actions">
<?php include 'others/actions.tpl.php' ?>
                    </td>
                
                </tr>
            {% endfor %}
            </tbody>
        </table>
        </form>


    </div>
</div> <!-- /#list -->

<div class="row">
    <div class="col-md-3 pull-left">
        <div class="input-group mb-3">
            <select class = "form-control not-selectized" name="bulk_action" >
                <option value="delete">ELIMINAR</option>
            </select>
            <div class="input-group-append">
                <input type="submit" id='bulkSubmitBtn' onclick="return confirm('¿Está seguro?')" form="bulk_form" class="form-control btn btn-danger mb-2" disabled>
            </div>
        </div>
    </div>

    <div class="col-md-1"></div>

    <div class='col-md-3 pagination text-center'>
        <p>{{totalOfRecordsString}}</p>
    </div>

    <div class="col-md-5 text-right">
        <div class="pull-right">
            {{ pagerHtml|raw }}
        </div>
    </div>
</div> <!-- /#bottom -->

{% endblock %}

{% block javascripts %}
    {{ parent() }}

{% endblock %}


