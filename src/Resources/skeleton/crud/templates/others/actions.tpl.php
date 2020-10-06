                        <a  onclick="return confirm('¿Está seguro que desea eliminar el registro?');" title="Eliminar" class="btn btn-danger btn-sm" href="{{ path('<?= $route_name ?>_delete', { 'id': <?=$entity_twig_var_singular ;?>.<?=$entity_identifier ?>}) }}">
                            <span class="fa fa-times" aria-hidden="true"></span>
                        </a>
                        <a title='Ver' class="btn btn-info btn-sm" href="{{ path('<?= $route_name ?>_show', { 'id': <?=$entity_twig_var_singular ;?>.<?=$entity_identifier ?> }) }}">
                            <span class="fa fa-eye" aria-hidden="true"></span>
                        </a>
                        <a  title='Editar' class="btn btn-primary btn-sm" href="{{ path('<?= $route_name ?>_edit', { 'id': <?=$entity_twig_var_singular ;?>.<?=$entity_identifier ?> }) }}">
                            <span class="fa fa-pencil" aria-hidden="true"></span>
                        </a>
