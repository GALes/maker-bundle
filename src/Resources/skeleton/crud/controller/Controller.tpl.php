<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use <?= $entity_full_class_name ?>;
use <?= $form_full_class_name ?>;
use <?= $form_filter_full_class_name ?>;
<?php if (isset($repository_full_class_name)): ?>
use <?= $repository_full_class_name ?>;
<?php endif ?>
use Symfony\Bundle\FrameworkBundle\Controller\<?= $parent_class_name ?>;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Form;
use Doctrine\ORM\QueryBuilder;

use Petkopara\MultiSearchBundle\Service\MultiSearchBuilderService;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrap4View;

/**
 * <?= $entity_class_name ?> Controller
 *
 * @Route("<?= $route_path ?>")
 */
class <?= $class_name ?> extends <?= $parent_class_name; ?><?= "\n" ?>
{
    /**
    * @var MultiSearchBuilderService
    */
    private $multiSearchBuilderService;
    
    public function __construct(MultiSearchBuilderService $multiSearchBuilderService)
    {
        $this->multiSearchBuilderService = $multiSearchBuilderService;
    }

<?php include 'actions/index.tpl.php' ?>

<?php include 'actions/new.tpl.php' ?>

<?php include 'actions/show.tpl.php' ?>

<?php include 'actions/edit.tpl.php' ?>

<?php include 'actions/delete.tpl.php' ?>

<?php include 'actions/bulk.tpl.php' ?>

}
