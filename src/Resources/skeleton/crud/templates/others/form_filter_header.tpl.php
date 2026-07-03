<!-- FILTERING -->
<div class="mb-card mb-3">
    {% include 'bundles/GALesMaker/_components/_filterHeader.html.twig' %}
    <div id="filters" class="collapse">
        <div class="mb-card__body">
            <form id="filtros" action="{{ path('<?= $route_name ?>_index') }}" method="get">
                <div class="row g-3">
<?php foreach ($entity_fields as $field=>$metadata): ?>
                    <div class="col-md-6">{{ form_row(filterForm.<?= $field ?>) }}</div>
<?php endforeach; ?>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <button type="submit" class="mb-btn-primary" name="filter_action" value="filter">
                        <span class="fa fa-search" aria-hidden="true"></span> Buscar
                    </button>
                    <button type="submit" class="mb-btn-outline" name="filter_action" value="reset">
                        <span class="fa fa-minus" aria-hidden="true"></span> Limpiar Filtros
                    </button>
                    <button type="submit" class="mb-btn-outline" name="filter_action" value="exportXlsx" id="button-export" data-turbo="false">
                        <span class="fa fa-cloud-download" aria-hidden="true"></span> Exportar Registros
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- END FILTERING -->

<div class="d-flex mb-3">
<?php include 'page_size.tpl.php' ?>
</div>
