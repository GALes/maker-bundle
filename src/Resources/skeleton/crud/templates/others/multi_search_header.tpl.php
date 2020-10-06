    <div class="col-md-3 pull-left">
        <!-- PAGE SIZE -->
        <div class="pagination form-inline ">
            <select class = "form-control"  onchange="window.location = this.value" >
                <option value='{{ path('<?= $route_name ?>_index', app.request.query.all|merge({'pcg_show': '10'})) }}' {% if app.request.get('pcg_show') == 10 %} selected {% endif %}>10</option>
                <option value='{{ path('<?= $route_name ?>_index', app.request.query.all|merge({'pcg_show': '20'})) }}' {% if app.request.get('pcg_show') == 20 %} selected {% endif %}>20</option>
                <option value='{{ path('<?= $route_name ?>_index', app.request.query.all|merge({'pcg_show': '50'})) }}' {% if app.request.get('pcg_show') == 50 %} selected {% endif %}>50</option>
                <option value='{{ path('<?= $route_name ?>_index', app.request.query.all|merge({'pcg_show': '100'})) }}' {% if app.request.get('pcg_show') == 100 %} selected {% endif %}>100</option>
                <option value='{{ path('<?= $route_name ?>_index', app.request.query.all|merge({'pcg_show': '500'})) }}' {% if app.request.get('pcg_show') == 500 %} selected {% endif %}>500</option>
            </select>
        </div>
        <!-- END PAGE SIZE -->
    </div>

    <!-- FILTERING -->
    <div class="col-md-6">
        <form action={{ path('<?= $route_name ?>_index') }} method="get" >

        <div class="input-group mb-3">
            {{ form_widget(filterForm.search, { 'attr': {'class': 'form-control'} }) }}
            {{ form_rest(filterForm) }}
            <div class="input-group-append">
                <a class="btn btn-info" href={{ path('<?= $route_name ?>_index', {'filter_action': 'reset'}) }}>
                <span class="fa fa-remove" aria-hidden="true"></span>
                </a>
            </div>
            <div class="input-group-append">
                <button class="btn btn-primary" type="submit">
                    <span class="fa fa-search"></span>
                </button>
            </div>
        </div>
        </form>
    </div>
    <!-- END FILTERING -->

    <div class="col-md-3">
        <a class="btn btn-primary h3 pull-right" href="{{ path('solicitud_empresa_new') }}" style="margin-bottom:10px">
            <span class="fa fa-plus" aria-hidden="true"></span> Nuevo
        </a>
    </div>
