# GALes Maker Bundle for CRUD Creation

Symfony4 / 5 CRUD generator bundle with pagination, filtering, Twitter bootstrap v4.4.1 markup and many other features. It's Simple to use (TODO: and fully customizable).

Designed to bring the functionality of PetkoparaCrudGeneratorBundle, but reusing the logic from Symfony's Maker Bundle.

<b>It's still a work in progress but fully functional with the limitations of not being so configurable yet.
It requires dev-master branch of petkopara/multi-search-bundle allowing the use of it on Symfony 4 / 5</b>

## Pasos para desarrollo y pruebas locales
Clonar el repositorio del bundle fuera del proyecto al que se quiera agregar, ej estando dentro de la raiz del proyecto:

    mkdir ../bundles
    cd ../bundles
    git clone https://github.com/GALes/maker-bundle.git

Agregar al composer.json del proyecto los siguientes repositorios:

    "repositories":[
        {
            "type": "path",
            "url": "../bundles/make-bundle"
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
