                        <a title="Eliminar" aria-label="Eliminar" class="mb-icon-btn text-danger" href="{{ path('<?= $route_name ?>_delete', { 'id': <?=$entity_twig_var_singular ;?>.<?=$entity_identifier ?>}) }}">
                            <span class="fa fa-times" aria-hidden="true"></span>
                        </a>
                        <a title="Ver" aria-label="Ver" class="mb-icon-btn" href="{{ path('<?= $route_name ?>_show', { 'id': <?=$entity_twig_var_singular ;?>.<?=$entity_identifier ?> }) }}">
                            <span class="fa fa-eye" aria-hidden="true"></span>
                        </a>
                        <a title="Editar" aria-label="Editar" class="mb-icon-btn" href="{{ path('<?= $route_name ?>_edit', { 'id': <?=$entity_twig_var_singular ;?>.<?=$entity_identifier ?> }) }}">
                            <span class="fa fa-pencil" aria-hidden="true"></span>
                        </a>
