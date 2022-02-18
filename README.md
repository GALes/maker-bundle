# GALes Maker Bundle for CRUD Creation

Symfony4 / 5 CRUD generator bundle with pagination, filtering, Twitter bootstrap v4.4.1 markup and many other features. It's Simple to use (TODO: and fully customizable).

Designed to bring back the functionality of PetkoparaCrudGeneratorBundle, but reusing the logic from Symfony's Maker Bundle.

<b>It's still a work in progress but fully functional with the limitations of not being so configurable yet.
It requires dev-master branch of petkopara/multi-search-bundle allowing the use of it on Symfony 4 / 5</b>


## Pasos para la instalación

Agregar el Bundle con composer:

    composer require gales/maker-bundle:^0.1

## Pasos para desarrollo y pruebas locales
Clonar el repositorio del bundle fuera del proyecto al que se quiera agregar, ej estando dentro de la raiz del proyecto:

    mkdir ../bundles
    cd ../bundles
    git clone https://github.com/GALes/maker-bundle.git

Agregar al composer.json del proyecto los siguientes repositorios:

    "repositories":[
        {
            "type": "path",
            "url": "../bundles/maker-bundle"
        }
    ],
    "minimum-stability": "dev",
    "prefer-stable": true
    
Agregar con composer los siguientes Bundles:

    composer require gales/maker-bundle:*@dev

Luego hacer un:

    composer dump-autoload

Agregar en la configuracion de Twig que use Bootstrap 4 Form Theme

    # config/packages/twig.yaml
    twig:
        form_themes: ['bootstrap_4_layout.html.twig']
        
## Uso
Ejecutar el comando 

    php bin/console gales:make:crud

#### Notaciones:
- Listado: para definir el campo a utilizarse en el ordenamiento de una columna asociada a una entidad relacionada, 
  se puede utilizar la siguiente notación en dicha entidad (`@GalesMaker(orderBy=nombre_propiedad)`). Ej: El listado de 
  solicitudes posee la columna Estado la cual muestra la descripción del mismo, y se desea que se ordene por esta:

```php
// src/Entity/Solicitud.php
namespace App\Entity;
...
class SolicitudBanco
{
    ...
    /**
     * @ORM\ManyToOne(targetEntity=SolicitudEstado::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $estado;
    ...
```

```php
// src/Entity/SolicitudEstado.php
namespace App\Entity;
...
/**
 ...
 * @GalesMaker(orderBy="descripcion")
 */
class SolicitudEstado
{
    ...
    private $descripcion;
    ...

```
  

#### Archivos generados por el bundle:

Luego seleccionar la Entidad a la cual generar el ABM. Archivos que se generan

    created: src/Service/<entity_name>CrudService.php (Logica auxiliar para el funcionamiento del ABM)
    created: src/Controller/<entity_name>Controller.php (Controlador con la logica del ABM)
    created: src/Form/<entity_name>Type.php (Formulario para el alta/edicion de la entidad)
    created: src/Form/<entity_name>(Full)FilterType.php (Filtro del listado)
    created: templates/<entity_name>/edit.html.twig (Vista de edicion de la entidad)
    created: templates/<entity_name>/index.html.twig  (Vista para el listado de entidades)
    created: templates/<entity_name>/new.html.twig  (Vista de creacion de nueva entidad)
    created: templates/<entity_name>/show.html.twig (Vista de visualizacion de los datos de la entidad)
