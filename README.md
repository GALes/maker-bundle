# GALes Maker Bundle for CRUD Creation

A powerful Symfony CRUD generator bundle with pagination, filtering, Bootstrap v5.2 markup and many advanced features. 
It's simple to use and fully customizable.

Designed to bring back the functionality of PetkoparaCrudGeneratorBundle, but reusing the logic from Symfony's Maker Bundle.

## 📋 Requirements

- **PHP:** 8.1+
- **Symfony:** 6.3, 7.0+
- **Doctrine ORM:** 2.5+ or 3.0+
- **PhpSpreadsheet:** `phpoffice/phpspreadsheet` 1.30.7+ or 5.10+ (the Excel export of the generated services)

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

### Running the command again on an existing CRUD

When a file the command is about to generate already exists (controller, form types, `<entity_name>CrudService`, views, Voter
and their tests), the command shows a yellow warning (`<path> ya existe. Si lo reemplazás se pierde cualquier lógica previa…`) and asks `¿Reemplazarlo?` for each one. Answering yes replaces the file,
**losing any logic it had**; the default answer is no, which skips it (`Omitido: <path> ya existe y no se sobrescribe.`).
Missing files are always created and the command exits with status 0. With `--no-interaction` every existing file is skipped.

```bash
php bin/console gales:make:crud Product input base.html.twig --force
```

`--force` replaces every existing file without asking, after a yellow warning at the start. Use it only on a clean working tree
and review `git diff` afterwards: it also overwrites the controller's `BULK_ACTIONS` map (your own bulk action classes are not touched).
If the generation fails, the files that were about to be replaced are restored.

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

## 📦 Bulk actions

Every generated CRUD lets the user select rows and apply a bulk action. Actions are classes tagged with
`gales_maker.bulk_action`, and each controller declares which ones it exposes in an explicit map
(action name => Voter attribute that authorizes it):

```php
private const BULK_ACTIONS = [
    'delete' => ProductVoter::ELIMINAR,
];
```

- **There is no default action:** a controller without `BULK_ACTIONS` entries exposes none, and an action that is not in the map answers `400`.
- `GALes\MakerBundle\Bulk\BulkActionRunner` runs the action: it sanitizes the ids, loads the entities, checks the Voter attribute
  on **every** entity of the batch before running anything (if one is denied the response is `403` and nothing is executed),
  and runs the action in a single transaction. The action must not call `flush()`: the runner's transaction does it.
- The form carries a CSRF token (`bulk_<route_name>`); a missing or wrong token only shows an error flash and executes nothing.
  It requires `framework.csrf_protection` enabled and `symfony/security-csrf`.
- Only entities with a single identifier are supported.
- `delete` (`DeleteBulkAction`) ships with the bundle.

### Adding your own bulk action

1. Implement the action in the project (autoconfiguration adds the tag):

```php
namespace App\Bulk;

use GALes\MakerBundle\Bulk\BulkActionInterface;
use GALes\MakerBundle\Bulk\BulkResult;

final class RenombrarBulkAction implements BulkActionInterface
{
    public function getName(): string { return 'renombrar'; }
    public function getLabel(): string { return 'RENOMBRAR'; }
    public function getConfirmMessage(): string { return '¿Renombrar los registros seleccionados?'; }
    public function supports(string $entityClass): bool { return Product::class === $entityClass; }

    public function execute(array $entities): BulkResult
    {
        foreach ($entities as $product) {
            $product->setName(strtoupper($product->getName()));
        }

        return new BulkResult(count($entities), 'Registros renombrados');
    }
}
```

2. Add the constant to the Voter (`RENOMBRAR = 'product_renombrar'`), include it in `supports()` and `voteOnAttribute()`.
3. Register it in the controller map: `'renombrar' => ProductVoter::RENOMBRAR,`.

`--force` overwrites `BULK_ACTIONS` with the original map; your action classes are not touched.

### Upgrading from v2.1.0

`BulkDeleteService` and `tests/Service/BulkDeleteServiceTest.php` generated by v2.1.0 are no longer used.
You can delete them after regenerating the controllers.

## 📊 Excel export

The generated `<entity_name>CrudService` exports the filtered listing to `.xlsx` through
`GALes\MakerBundle\Spreadsheet\Factory`, a service of this bundle that is autowired like any other.
It wraps `PhpOffice\PhpSpreadsheet\IOFactory` and works with `phpoffice/phpspreadsheet` 1.30.7+ and 5.10+,
so each project decides when to move to PhpSpreadsheet 5.

### Upgrading from 1.x (`yectep/phpspreadsheet-bundle`)

From 2.0 the bundle no longer depends on `yectep/phpspreadsheet-bundle`, which limits PhpSpreadsheet to the 1.x branch.
`Factory` keeps the same methods (`createSpreadsheet`, `createWriter`, `createReader`, `createStreamedResponse`),
so in code generated by 1.x it is enough to change the import:

```diff
-use Yectep\PhpSpreadsheetBundle\Factory;
+use GALes\MakerBundle\Spreadsheet\Factory;
```

Then remove `Yectep\PhpSpreadsheetBundle\PhpSpreadsheetBundle` from `config/bundles.php` and
`composer remove yectep/phpspreadsheet-bundle` (unless you use it elsewhere). The row handling of the generated
`exportXlsx(iterable $iterableResult)` expects `Query::toIterable()` (entities, not `[0 => entity]` rows): `Query::iterate()` and
`IterableResult` no longer exist in Doctrine ORM 3.


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
