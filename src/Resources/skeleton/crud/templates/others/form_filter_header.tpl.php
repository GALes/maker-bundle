   

<!-- FILTERING -->
<div class="col-md-12">
    <div class="card mb-2">
        <div class="card-header">
            <b>Filtros</b>
        </div>
        <div class="card-body">
            <div  id="filters" class="collapse in show">
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
                                <button type="submit" class="btn btn-warning" name="filter_action" value="filter"> <span class="fa fa-search" aria-hidden="true"></span> Buscar</button>
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

<div class="col-md-6">


</div>

<div class="col-md-3">

    {# if 'new' in actions #}
        <a class="btn btn-primary h3 pull-right ml-1" href={{ path('<?= $route_name ?>_new') }} style="margin-bottom:10px">
            <span class="fa fa-plus" aria-hidden="true"></span> Nuevo
        </a>
    {# endif #}
    <a class="btn dropdown-toggle pull-right h3 ml-1" data-toggle="collapse" data-target="#filters" style="background-color: #428bca; color: white">
        Filtros
        <span class="caret"></span>
    </a>
</div>







