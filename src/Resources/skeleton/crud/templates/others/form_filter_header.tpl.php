   

<!-- FILTERING -->
<div class="col-md-12">
    <div  id="filters" class="collapse">

        <form class="well" action="{{ "{{ path('"~ route_name_prefix ~"') }}" }}" method="get" >
            <div class="row">
                {% for field, metadata in fields %} 
                    <div class="col-md-6">{{"{{ form_row(filterForm."~field~") }}"}}</div>

                {% endfor %}
                <div class="col-md-6">
                    {{ "{{ form_rest(filterForm) }}" }}
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <button type="submit" class="btn btn-warning" name="filter_action" value="filter"> <span class="fa fa-filter" aria-hidden="true"></span> Filtrar</button>
                        <button type="submit" class="btn btn-secondary" name="filter_action" value="reset"><span class="fa fa-minus" aria-hidden="true"></span> Resetear</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END FILTERING -->


<div class="col-md-3 pull-left">
    {% include 'crud/views/others/page_size.html.twig.twig' %}
</div>

<div class="col-md-6">


</div>

<div class="col-md-3">

    {% if 'new' in actions %}
        <a class="btn btn-primary h3 pull-right" href="{{ "{{ path('"~ route_name_prefix ~"_new') }}" }}" style="margin-bottom:10px">
            <span class="fa fa-plus" aria-hidden="true"></span> Nuevo
        </a>
    {% endif %}
    <a class="btn dropdown-toggle pull-right h3" data-toggle="collapse" data-target="#filters">
        Filtrar
        <span class="caret"></span>
    </a>
</div>







