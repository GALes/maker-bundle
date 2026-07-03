    <div class="d-flex align-items-center flex-wrap gap-3 mb-3">
<?php include 'page_size.tpl.php' ?>

        <!-- FILTERING -->
        <form action="{{ path('<?= $route_name ?>_index') }}" method="get" id="filters" class="mb-search-bar flex-grow-1">
            {{ form_widget(filterForm.search, { 'attr': {'class': 'form-control'} }) }}
            {{ form_rest(filterForm) }}
            <button class="mb-icon-btn" type="submit" name="filter_action" value="filter" title="Buscar" aria-label="Buscar">
                <span class="fa fa-search" aria-hidden="true"></span>
            </button>
            <button class="mb-icon-btn" type="submit" name="filter_action" value="reset" title="Limpiar" aria-label="Limpiar">
                <span class="fa fa-remove" aria-hidden="true"></span>
            </button>
            <button class="mb-icon-btn" type="submit" name="filter_action" id="button-export" value="exportXlsx" data-turbo="false" title="Exportar" aria-label="Exportar">
                <span class="fa fa-download" aria-hidden="true"></span>
            </button>
        </form>
        <!-- END FILTERING -->
    </div>
