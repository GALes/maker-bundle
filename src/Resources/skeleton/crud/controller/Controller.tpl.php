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

    /**
     * @Route("/", name="<?= $route_name ?>_index", methods={"GET"})
     */
    public function index(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $em->getRepository(<?=$entity_class_name ?>::class)->createQueryBuilder('zzz');

        $filter_response = $this->filter($queryBuilder, $request);
        if ($filter_response instanceof RedirectResponse) {
            return $filter_response;
        }
        list($filterForm, $queryBuilder) = $filter_response;

        list($<?=$entity_var_plural; ?>, $pagerHtml) = $this->paginator($queryBuilder, $request);

        $totalOfRecordsString = $this->getTotalOfRecordsString($queryBuilder, $request);

        return $this->render('<?= $templates_path ?>/index.html.twig', [
            '<?= $entity_twig_var_plural ?>' => $<?=$entity_var_plural ?>,
            'pagerHtml' => $pagerHtml,
            'filterForm' => $filterForm->createView(),
            'totalOfRecordsString' => $totalOfRecordsString,
        ]);
    }

    /**
    * Create filter form and process filter request (Input Type).
    *
    */
    protected function filter(QueryBuilder $queryBuilder, Request $request)
    {
        $session = $request->getSession();
        $filterForm = $this->createForm(<?= $form_filter_class_name ?>::class);
        
        // Bind values from the request
        $filterForm->handleRequest($request);
        
        //Reset Filters
        if ($request->get('filter_action') == 'reset') {
            $request->query->remove('filter_action');
            $session->remove('filterUrl');
        }
        else {
            if ($filterForm->isSubmitted() && $filterForm->isValid()) {
                $filterUrl = $request->query->all();
                $session->set('filterUrl', $filterUrl);
                // Build the query from the given form object
                $this->multiSearchBuilderService->searchForm( $queryBuilder, $filterForm->get('search'));
            }
            else if ( $request->get('pcg_page') || $request->get('pcg_sort_col') || $request->get('pcg_sort_order') ) {
                $filterUrl = $request->query->all();
                $session->set('filterUrl', $filterUrl);
            }
            else if ($session->has('filterUrl')) {
                $filterUrl = $session->get('filterUrl');
                $session->remove('filterUrl');
                return $this->redirectToRoute('<?= $route_name ?>_index', $filterUrl);
            }
        }
        
        return array($filterForm, $queryBuilder);
    }

    /**
    * Get results from paginator and get paginator view.
    *
    */
    protected function paginator(QueryBuilder $queryBuilder, Request $request)
    {
        //sorting
        $sortCol = $queryBuilder->getRootAlias().'.'.$request->get('pcg_sort_col', 'id');
        $queryBuilder->orderBy($sortCol, $request->get('pcg_sort_order', 'desc'));
        // Paginator
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($request->get('pcg_show' , 10));
        
        try {
            $pagerfanta->setCurrentPage($request->get('pcg_page', 1));
        } catch (\Pagerfanta\Exception\OutOfRangeCurrentPageException $ex) {
            $pagerfanta->setCurrentPage(1);
        }
        
        $entities = $pagerfanta->getCurrentPageResults();
        
        // Paginator - route generator
        $me = $this;
        $routeGenerator = function($page) use ($me, $request)
        {
            $requestParams = $request->query->all();
            $requestParams['pcg_page'] = $page;
            return $me->generateUrl('<?= $route_name ?>_index', $requestParams);
        };
        
        // Paginator - view
        $view = new TwitterBootstrap4View();
        $pagerHtml = $view->render($pagerfanta, $routeGenerator, [
            'proximity' => 3,
            'prev_message' => 'anterior',
            'next_message' => 'siguiente',
        ]);
        
        return array($entities, $pagerHtml);
    }

    /*
    * Calculates the total of records string
    */
    protected function getTotalOfRecordsString(QueryBuilder $queryBuilder, Request $request) {
        $totalOfRecords = $queryBuilder->select('COUNT(zzz.<?=$entity_identifier; ?>)')->getQuery()->getSingleScalarResult();
        $show = $request->get('pcg_show', 10);
        $page = $request->get('pcg_page', 1);
        
        $startRecord = ($show * ($page - 1)) + 1;
        $endRecord = $show * $page;
        
        if ($endRecord > $totalOfRecords) {
            $endRecord = $totalOfRecords;   
        }
        return "Mostrando $startRecord - $endRecord de $totalOfRecords Registros.";
    }

    /**
     * @Route("/new", name="<?= $route_name ?>_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $<?= $entity_var_singular ?> = new <?= $entity_class_name ?>();
        $form = $this->createForm(<?= $form_class_name ?>::class, $<?= $entity_var_singular ?>);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($<?= $entity_var_singular ?>);
            $entityManager->flush();

            $editLink = $this->generateUrl('<?= $route_name ?>_edit', array('id' => $<?= $entity_var_singular ?>->getId()));
            $this->get('session')->getFlashBag()->add('success', "<a href='$editLink'>El nuevo registro se ha creado exitosamente.</a>" );
            
            $nextAction=  $request->get('submit') == 'save' ? '<?= $route_name ?>_index' : '<?= $route_name ?>_new';
            return $this->redirectToRoute($nextAction);
        }

        return $this->render('<?= $templates_path ?>/new.html.twig', [
            '<?= $entity_twig_var_singular ?>' => $<?= $entity_var_singular ?>,
            'form' => $form->createView(),
        ]);
    }

    /**
    * @Route("/{<?= $entity_identifier ?>}", name="<?= $route_name ?>_show", methods={"GET"})
    */
    public function show(<?= $entity_class_name ?> $<?= $entity_var_singular ?>): Response
    {
        $deleteForm = $this->createDeleteForm($<?= $entity_var_singular ?>);

        return $this->render('<?= $templates_path ?>/show.html.twig', [
            '<?= $entity_twig_var_singular ?>' => $<?= $entity_var_singular ?>,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @Route("/{<?= $entity_identifier ?>}/edit", name="<?= $route_name ?>_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, <?= $entity_class_name ?> $<?= $entity_var_singular ?>): Response
    {
        $deleteForm = $this->createDeleteForm($<?= $entity_var_singular ?>);
        $form = $this->createForm(<?= $form_class_name ?>::class, $<?= $entity_var_singular ?>);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->get('session')->getFlashBag()->add('success', 'El registro se ha editado exitosamente');
            return $this->redirectToRoute('<?= $route_name ?>_edit', array('id' => $<?= $entity_var_singular ?>->getId()));
        }

        return $this->render('<?= $templates_path ?>/edit.html.twig', [
            '<?= $entity_twig_var_singular ?>' => $<?= $entity_var_singular ?>,
            'form' => $form->createView(),
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @Route("/{<?= $entity_identifier ?>}", name="<?= $route_name ?>_delete", methods={"DELETE"})
     */
    public function delete(Request $request, <?= $entity_class_name ?> $<?= $entity_var_singular ?>): Response
    {
        $form = $this->createDeleteForm($<?= $entity_var_singular ?>);
        $form->handleRequest($request);

//        if ($this->isCsrfTokenValid('delete' . $<?= $entity_var_singular ?>->get<?= ucfirst($entity_identifier) ?>(), $request->request->get('_token'))) {
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($<?= $entity_var_singular ?>);
            $entityManager->flush();
            $this->get('session')->getFlashBag()->add('success', 'El registro se ha eliminado exitosamente');
        }
        else {
            $this->get('session')->getFlashBag()->add('error', 'No se ha podido eliminar el registro');
        }

        return $this->redirectToRoute('<?= $route_name ?>_index');
    }

    /**
    * Creates a form to delete a {{ entity }} entity.
    *
    * @param <?= $entity_class_name ?> $<?= $entity_var_singular ?> The <?= $entity_class_name ?> entity
    *
    * @return Form The form
    */
    private function createDeleteForm(<?= $entity_class_name ?> $<?= $entity_var_singular ?>)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('<?= $route_name ?>_delete', array('id' => $<?= $entity_var_singular ?>->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
    
    /**
    * Delete <?= $entity_class_name ?> by id
    *
    * @Route("/delete/{id}", name="<?= $route_name ?>_by_id_delete", methods={"GET"})
    */
    public function deleteByIdAction(<?= $entity_class_name ?> $<?= $entity_var_singular ?>){
        $em = $this->getDoctrine()->getManager();
        
        try {
            $em->remove($<?= $entity_var_singular ?>);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'El registro se ha eliminado exitosamente');
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('error', 'No se ha podido eliminar el registro');
        }
        
        return $this->redirect($this->generateUrl('<?= $route_name ?>_index'));
    
    }

    /**
    * Bulk Action
    * @Route("/bulk-action/", name="<?= $route_name ?>_bulk_action", methods={"POST"})
    */
    public function bulkAction(Request $request)
    {
        $ids = $request->get("ids", array());
        $action = $request->get("bulk_action", "delete");
        
        if ($action == "delete") {
            try {
                $em = $this->getDoctrine()->getManager();
                $repository = $em->getRepository(<?= $entity_class_name ?>::class);
                
                foreach ($ids as $id) {
                    $<?= $entity_var_singular ?> = $repository->find($id);
                    $em->remove($<?= $entity_var_singular ?>);
                    $em->flush();
                }
                
                $this->get('session')->getFlashBag()->add('success', 'El/Los registro/s se ha/n eliminado exitosamente');
                
            } 
            catch (Exception $ex) {
                $this->get('session')->getFlashBag()->add('error', 'No se ha/n podido eliminar el/los registro/s');
            }
        }
        
        return $this->redirect($this->generateUrl('<?= $route_name ?>_index'));
    }


}
