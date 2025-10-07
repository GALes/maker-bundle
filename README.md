# GALes Maker Bundle for CRUD Creation

A powerful Symfony CRUD generator bundle with pagination, filtering, Bootstrap v5.2 markup and many advanced features. 
It's simple to use and fully customizable.

Designed to bring back the functionality of PetkoparaCrudGeneratorBundle, but reusing the logic from Symfony's Maker Bundle.

## 📋 Requirements

- **PHP:** 8.1+
- **Symfony:** 6.3, 7.0+
- **Doctrine ORM:** 2.5+ or 3.0+

## 📦 Installation

Add the Bundle with composer:

```bash
composer require gales/maker-bundle:^0.1
```

Configure Twig to use Bootstrap 5 Form Theme:

```yaml
# config/packages/twig.yaml
twig:
    form_themes: ['bootstrap_5_layout.html.twig']
```

## ⚡ Usage

Run the command:

```bash
php bin/console gales:make:crud
```

Using parameters:

```bash
php bin/console gales:make:crud <EntityClassName> [filter-type] [base-template]
```

**Example:**

```bash
php bin/console gales:make:crud Product input base.html.twig
```

## 📁 Generated Files

After selecting the Entity for which to generate the CRUD, the following files are created:

```
created: src/Service/<entity_name>CrudService.php          (Auxiliary logic for CRUD functionality)
created: src/Controller/<entity_name>Controller.php        (Controller with CRUD logic)
created: src/Form/<entity_name>Type.php                    (Form for entity creation/editing)
created: src/Form/<entity_name>(Full)FilterType.php        (Listing filter)
created: templates/<entity_name>/edit.html.twig            (Entity editing view)
created: templates/<entity_name>/index.html.twig           (Entity listing view)
created: templates/<entity_name>/new.html.twig             (New entity creation view)
created: templates/<entity_name>/show.html.twig            (Entity data visualization view)
```

## 🎯 Features

- ✅ **Full CRUD Operations** (Create, Read, Update, Delete)
- ✅ **Pagination** with configurable page size
- ✅ **Advanced Filtering** (input, select, date filters)
- ✅ **Bootstrap 5.2** responsive design
- ✅ **Multi-column Sorting** with custom ordering
- ✅ **Form Validation** with Symfony constraints
- ✅ **Service Layer** for business logic separation
- ✅ **Twig Templates** with inheritance support
- ✅ **Symfony 7** compatible



## 🚀 Development and Local Testing Setup

Clone the bundle repository outside your project, for example from your project root:

```bash
mkdir ../00_Bundles
cd ../00_Bundles
git clone https://github.com/GALes/maker-bundle.git
```

Add the following repositories to your project's `composer.json`:

```json
{
    "repositories":[
        {
            "type": "path",
            "url": "../00_Bundles/maker-bundle",
            "options": {
                "symlink": true
            }
        }
    ],
    "minimum-stability": "dev",
    "prefer-stable": true
}
```

Install the bundle with composer:

```bash
composer require gales/maker-bundle:*
```

Then run:

```bash
composer dump-autoload
```

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📄 License

This project is licensed under the MIT License.
