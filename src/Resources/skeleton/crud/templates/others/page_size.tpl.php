        <!-- PAGE SIZE -->
        <select class="form-select form-select-sm w-auto not-selectized mb-page-size" onchange="window.location = this.value" aria-label="Registros por página">
            <option value='{{ path('<?= $route_name ?>_index', app.request.query.all|merge({'pcg_show': '10'})) }}' {% if app.request.get('pcg_show') == 10 %} selected {% endif %}>10</option>
            <option value='{{ path('<?= $route_name ?>_index', app.request.query.all|merge({'pcg_show': '20'})) }}' {% if app.request.get('pcg_show') == 20 %} selected {% endif %}>20</option>
            <option value='{{ path('<?= $route_name ?>_index', app.request.query.all|merge({'pcg_show': '50'})) }}' {% if app.request.get('pcg_show') == 50 %} selected {% endif %}>50</option>
            <option value='{{ path('<?= $route_name ?>_index', app.request.query.all|merge({'pcg_show': '100'})) }}' {% if app.request.get('pcg_show') == 100 %} selected {% endif %}>100</option>
            <option value='{{ path('<?= $route_name ?>_index', app.request.query.all|merge({'pcg_show': '500'})) }}' {% if app.request.get('pcg_show') == 500 %} selected {% endif %}>500</option>
        </select>
        <!-- END PAGE SIZE -->
