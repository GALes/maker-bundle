
    <div class="form-group">
        <a class="btn btn-secondary" href="{{ path('<?=$route_name ?>_index') }}">
            <span class="fa fa-list" aria-hidden="true"></span>
            Volver al listado
        </a>
    {% if (not hide_edit) %}
        <a class="btn btn-primary" href="{{ path('<?=$route_name ?>_edit', { 'id': <?=$entity_twig_var_singular ;?>.<?=$entity_identifier ?> }) }}">
            <span class="fa fa-pencil" aria-hidden="true"></span>
            Editar
        </a>
    {% endif %}
    {% if (not hide_new) %}
        <a class="btn btn-info" href="{{ path('<?=$route_name ?>_new') }}">
            <span class="fa fa-plus" aria-hidden="true"></span>
            Nuevo
        </a>
    {% endif %}
    {% if (not hide_delete) %}
        <form action="{{ path('<?=$route_name ?>_delete', { 'id': <?=$entity_twig_var_singular ;?>.<?=$entity_identifier ?> }) }}" method="post" style="display: inline-block">
            <input type="hidden" name="_method" value="DELETE" />
            {{ form_widget(delete_form) }}
            <button class="btn btn-danger" type="submit" onclick="return confirm('¿Está seguro que desea eliminar el/los registro/s?');">
                <span class="fa fa-times" aria-hidden="true"></span>
                Eliminar
            </button>
        </form>
    {% endif %}
    </div>
