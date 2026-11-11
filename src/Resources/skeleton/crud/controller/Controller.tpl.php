<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use <?= $entity_full_class_name ?>;
use <?= $form_full_class_name ?>;
<?php if (isset($repository_full_class_name)): ?>
use <?= $repository_full_class_name ?>;
<?php endif ?>
use <?= $crud_service_full_class_name ?>;
use <?= $voter_full_class_name ?>;
use GALes\MakerBundle\Bulk\BulkActionRegistry;
use GALes\MakerBundle\Bulk\BulkActionRunner;
use GALes\MakerBundle\Bulk\UnknownBulkActionException;
use Doctrine\DBAL\Exception as DBALException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Form\Form;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;


/**
 * <?= $entity_class_name ?> Controller
 */
 #[Route("<?= $route_path ?>")]
 #[IsGranted(<?= $voter_class_name ?>::ACCEDER)]
class <?= $class_name; ?> extends AbstractController
{
    /** Acciones masivas de este CRUD: nombre de la acción => atributo del Voter que la autoriza. */
    private const BULK_ACTIONS = [
        'delete' => <?= $voter_class_name ?>::ELIMINAR,
    ];

    private const BULK_CSRF_ID = 'bulk_<?= $route_name ?>';

    private ObjectManager $entityManager;

    private <?= $entity_class_name ?>CrudService $<?= $entity_var_singular ?>CrudService;

    public function __construct(ManagerRegistry $managerRegistry, <?= $entity_class_name ?>CrudService $<?= $entity_var_singular ?>CrudService)
    {
        $this->entityManager = $managerRegistry->getManager();
        $this-><?= $entity_var_singular ?>CrudService = $<?= $entity_var_singular ?>CrudService;
    }

<?php include 'actions/index.tpl.php' ?>

<?php include 'actions/new.tpl.php' ?>

<?php include 'actions/show.tpl.php' ?>

<?php include 'actions/edit.tpl.php' ?>

<?php include 'actions/delete.tpl.php' ?>

<?php include 'actions/bulk.tpl.php' ?>

}
