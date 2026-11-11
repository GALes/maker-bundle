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
php bin/console gales:make:crud Product input base.html.twig --roles=ROLE_REPORTE
php bin/console gales:make:crud Product input base.html.twig --route-prefix=/admin
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

## 🔒 Access roles

A generated CRUD is only accessible to `ROLE_ADMIN` unless you choose other roles when generating it:

- **Interactive:** after the base template, the command asks for extra roles one at a time, with autocompletion. Press Enter on an empty answer to finish.
- **Non interactive:** `--roles=ROLE_REPORTE,ROLE_CONSULTA` (or `--roles` repeated). With `-n` and no `--roles`, only `ROLE_ADMIN`.
  `--roles=` (empty) skips the question.

Every role must start with `ROLE_` and use only uppercase letters, digits and `_`. A role that the project does not define is
accepted with a warning.

The chosen roles are written in the generated Voter, which is the single place to change them later:

```php
// src/Security/ProductVoter.php
public const ROLES = [
    'ROLE_ADMIN',
    'ROLE_REPORTE',
];
```

- The controller requires `ProductVoter::ACCEDER` on the whole class (`#[IsGranted]`): listing, export, bulk actions and any action you add.
- `new` requires `ProductVoter::CREAR`; `show`, `edit` and `delete` keep `VER`, `EDITAR` and `ELIMINAR` on the entity; bulk actions keep their attribute (`ELIMINAR` for delete).
- By default every attribute grants access to any role in `ROLES` (role hierarchy included). Change `voteOnAttribute()` for finer rules, for example a role that can list but not delete.
- If the route ends up under a prefix protected by `access_control`, that rule must also allow the chosen roles: the first matching rule wins.

### Configuration

```yaml
# config/packages/gales_maker.yaml
gales_maker:
    crud:
        # Static method or constant that returns the roles offered with autocompletion (the array values are used).
        roles_source: 'App\Entity\User::getRolesDetalle'
        # Roles that always have access. Default: [ROLE_ADMIN]
        default_roles: [ROLE_ADMIN]
```

The suggestions are the roles from `roles_source` followed by the keys and values of `security.role_hierarchy`, without the
default roles, `ROLE_USER` (any authenticated user) and the impersonation roles. If `roles_source` cannot be read, the command warns and goes on.

## 🛣️ Route prefix

By default the controller route comes from the entity name: `Solicitud` -> `/solicitud`, `NotaPedido` -> `/nota/pedido`.
If the back office is protected by route prefix (for example `access_control` with `^/admin`), generate the CRUD under it:

```bash
php bin/console gales:make:crud Solicitud input base.html.twig --route-prefix=/admin -n
# #[Route("/admin/solicitud")]
```

- The leading `/` is optional and a trailing one is ignored: `admin`, `/admin` and `/admin/` are the same. Nested prefixes work (`/backoffice/admin`).
- Allowed characters: letters, digits, `-`, `_`, `.`, `/` and `{}` (for parameters such as `/{_locale}/admin`). Anything else stops the command before writing files.
- The route **name** (`solicitud_index`, ...) does not change, only the path.
- The final route is shown in the last line of the command (`Next: Check your new CRUD by going to /admin/solicitud/`).
- `--route-prefix=` (empty) forces no prefix, even if a default is configured.

To avoid passing it every time, set a default in the bundle configuration (`--route-prefix` overrides it):

```yaml
# config/packages/gales_maker.yaml
gales_maker:
    crud:
        # Default route prefix of generated CRUDs. Default: none
        route_prefix: '/admin'
```

Regenerating an existing CRUD with a different prefix replaces its controller only if you confirm (or use `--force`); the other files do not depend on the path.

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

### Upgrading from v2.2

CRUDs generated before v2.3 check nothing in `index` and `new`: they depend on `access_control`. To protect one without regenerating it:

1. In the Voter, add `ROLES`, `ACCEDER` and `CREAR` as in the generated template (`src/Resources/skeleton/security/Voter.tpl.php`):
   both attributes are supported only with a `null` subject and vote with the roles in `ROLES`.
2. In the controller, add `use Symfony\Component\Security\Http\Attribute\IsGranted;` and `#[IsGranted(ProductVoter::ACCEDER)]` on the class.
3. In `new()`, add `$this->denyAccessUnlessGranted(ProductVoter::CREAR);` as the first line.

Regenerating only the controller while keeping an old Voter stops the command before writing anything, with a message that names the missing constants.

