<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use <?= $entity_full_class_name ?>;
use <?= $form_full_class_name ?>;
use <?= $form_filter_full_class_name ?>;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Yectep\PhpSpreadsheetBundle\Factory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
<?php if ( $filter_type === 'input' ): ?>
use Petkopara\MultiSearchBundle\Service\MultiSearchBuilderService;
<?php elseif ( $filter_type === 'form' ): ?>
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdater;
<?php endif; ?>
use Pagerfanta\Pagerfanta;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\View\TwitterBootstrap4View;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use GALes\MakerBundle\Helper\OrderByHelper;


/**
 * Servicio de exportación de registros del CRUD
 */
class <?= $class_name ?>
{
    /**
     * @var Factory
     */
    private $phpExcelFactory;
    /**
    * @var EntityManagerInterface
    */
    private $em;

    public function __construct(
        Factory                     $phpExcelFactory,
        EntityManagerInterface      $em,
        FormFactoryInterface        $formFactory,
        UrlGeneratorInterface       $urlGenerator,
<?php if ( $filter_type === 'input' ): ?>
        MultiSearchBuilderService   $multiSearchBuilderService
<?php elseif ( $filter_type === 'form' ): ?>
        FilterBuilderUpdater $lexikQueryBuilderUpdater
<?php endif; ?>
    )
    {
        $this->phpExcelFactory = $phpExcelFactory;
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->urlGenerator = $urlGenerator;
<?php if ( $filter_type === 'input' ): ?>
        $this->multiSearchBuilderService = $multiSearchBuilderService;
<?php elseif ( $filter_type === 'form' ): ?>
        $this->lexikQueryBuilderUpdater = $lexikQueryBuilderUpdater;
<?php endif; ?>
    }

    public function exportXlsx(iterable $iterableResult)
    {
        ini_set('memory_limit', -1);
        ini_set('max_execution_time', 600);
        ini_set('X-Accel-Buffering', 'no');

        $actualDate = new \DateTime('now');

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator("CRUD Generator")
            ->setTitle("Registros - {$actualDate->format('d-m-Y H:m:s')}")
            ->getDescription("Registros a la fecha: {$actualDate->format('d-m-Y H:m:s')}")
        ;

        $sheet = $spreadsheet->setActiveSheetIndex(0)->setTitle("Registros_{$actualDate->format('d-m-Y_H.m.s')}");
        $sheet
<?php $columna = 'A'; ?>
<?php foreach ($entity_fields as $field): ?>
            ->setCellValue("<?= $columna ?>1", "<?= ucfirst($custom_helper->asHumanWords($field['metadata']['fieldName'])) ?>")
<?php $columna++ ?>
<?php endforeach; ?>
        ;

        $response = $this->phpExcelFactory->createStreamedResponse($spreadsheet, 'Xlsx');

        foreach ($iterableResult as $key => $row) {
            /** @var <?= $entity_class_name; ?> $<?= $entity_var_singular ?> */
            $<?= $entity_var_singular ?> = $row;
            $rowNumber = $key + 2; // Los datos comienzan desde la fila 2
            $sheet
<?php $columna = 'A'; ?>
<?php foreach ($entity_fields as $field): ?>
                ->setCellValue("<?= $columna ?>$rowNumber", $<?= $entity_var_singular ?>->get<?= ucfirst($field['metadata']['fieldName']); ?>())
<?php $columna++ ?>
<?php endforeach; ?>
            ;
            $this->em->detach($row);
        }

        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            "exportacion_<?= $entity_var_plural ?>_{$actualDate->format('Y-m-d_H.m.s')}.xlsx"
        );

        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);
        $response->headers->set('Set-Cookie:FileLoading=true', '');

        return $response;
    }

    /**
    * Create filter form and process filter request (Input Type).
    *
    */
    public function filter(QueryBuilder $queryBuilder, Request $request)
    {
        $session = $request->getSession();
        $filterForm = $this->formFactory->create(<?= $form_filter_class_name ?>::class);

        // Para evitar error cuando se utilizan joins oneToMany y manyToMany:
        // Iterate with fetch join in using association not allowed.
//        $queryBuilder->distinct();
        //Reset Filters
        if ($request->get('filter_action') == 'reset') {
            $request->query->remove('filter_action');
            $session->remove('filterUrl');
        }
        else {
            // Filter action
            if ($request->get('filter_action') == 'filter' || $request->get('filter_action') == 'exportXlsx') {
                // Bind values from the request
                $filterForm->handleRequest($request);
                $session->remove('filterUrl');

                // Quito la accion de exportación para no guardarla en session
                $exportXlsx = false;
                if ( $request->get('filter_action') == 'exportXlsx' ) {
                    $request->query->set('filter_action', 'filter');
                    $exportXlsx = true;
                }

                // Si se presiono el boton de busqueda del filtro
                if ($filterForm->isSubmitted() && $filterForm->isValid()) {
                    // Build the query from the given form object
<?php if ( $filter_type === 'input' ): ?>
                    $this->multiSearchBuilderService->searchForm($queryBuilder, $filterForm->get('search'));
<?php elseif ( $filter_type === 'form' ): ?>
                    $this->lexikQueryBuilderUpdater->addFilterConditions($filterForm, $queryBuilder);
<?php endif; ?>

                    // Save filter to session
                    $filterUrl = $request->query->all();
                    $session->set('filterUrl', $filterUrl);

                    // Restituyo la acción para generar el excel en el index
                    if ($exportXlsx) {
                        $request->query->set('filter_action', 'exportXlsx');
                    }
                }
            }
            // Si entra por paginacion o por ordenamiento de columna
            else if ( $request->get('pcg_page') || $request->get('pcg_sort_col') || $request->get('pcg_sort_order') ) {
                $filterUrl = $request->query->all();
                $session->set('filterUrl', $filterUrl);
            }
            // Sino si solo se abre la url base y se tienen filtros en session, los restituye y renderiza nuevamente
            else if ($session->has('filterUrl')) {
                $filterUrl = $session->get('filterUrl');
                $session->remove('filterUrl');
                $url = $this->urlGenerator->generate('<?= $route_name ?>_index', $filterUrl);
                return new RedirectResponse($url);
            }
        }
        
        return array($filterForm, $queryBuilder);
    }

    /**
    * Get results from paginator and get paginator view.
    *
    */
    public function paginator(QueryBuilder $queryBuilder, Request $request)
    {
        //sorting
        $sortCol    = $request->get('pcg_sort_col', 'id');
        $sortOrder  = $request->get('pcg_sort_order', 'desc');
        $queryBuilder = OrderByHelper::addOrderByToQuery($queryBuilder, $sortCol, $sortOrder);

        // Paginator
        $adapter = new QueryAdapter($queryBuilder);
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
            return $me->urlGenerator->generate('<?= $route_name ?>_index', $requestParams);
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
    public function getTotalOfRecordsString(QueryBuilder $queryBuilder, Request $request) {
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
    * Creates a form to delete a <?= $entity_class_name; ?> entity.
    *
    * @param <?= $entity_class_name; ?> $<?= $entity_var_singular ?> The User entity
    *
    * @return Form The form
    */
    public function createDeleteForm(<?= $entity_class_name; ?> $<?= $entity_var_singular ?>)
    {
        return $this->formFactory->createBuilder()
            ->setAction($this->urlGenerator->generate('<?= $entity_var_singular ?>_delete', array('id' => $<?= $entity_var_singular ?>->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }



}
