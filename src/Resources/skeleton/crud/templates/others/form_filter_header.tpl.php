<!-- FILTERING -->
<div class="col-md-12">
    <div class="card mb-2">
        {% include 'bundles/GALesMaker/_components/_filterHeader.html.twig' %}
        <div class="card-body">
            <div  id="filters" class="collapse">
                <form class="well" id="filtros" action={{ path('<?= $route_name ?>_index') }} method="get" >
                    <div class="row">
<?php foreach ($entity_fields as $field=>$metadata): ?>
                            <div class="col-md-6">{{ form_row(filterForm.<?= $field ?>) }}</div>
<?php endforeach; ?>
<!--                        <div class="col-md-6">-->
<!--                            {{ form_rest(filterForm) }}-->
<!--                        </div>-->
                        <div class="col-md-12">
                            <div class="form-group">
                                <button type="submit" class="btn btn-outline-warning" name="filter_action" value="filter"> <span class="fa fa-search" aria-hidden="true"></span> Buscar</button>
                                <button type="submit" class="btn btn-outline-dark" name="filter_action" value="reset"><span class="fa fa-minus" aria-hidden="true"></span> Limpiar Filtros</button>
                                <button type="submit" class="btn btn-outline-dark" name="filter_action" value="exportXlsx", id="button-export" data-turbo="false"><span class="fa fa-cloud-download" aria-hidden="true"></span> Exportar Registros</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- END FILTERING -->

<div class="col-md-3 pull-left">
<?php include 'page_size.tpl.php' ?>
</div>







