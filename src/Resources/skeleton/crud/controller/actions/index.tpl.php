    /**
     * @Route("/", name="<?= $route_name ?>_index", methods={"GET"})
     */
    public function index(Request $request, <?= $entity_class_name ?>ExportService $<?= $entity_var_singular ?>ExportService)
    {
        $isValid = true;
        $em = $this->getDoctrine()->getManager();
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $em->getRepository(<?= $entity_class_name ?>::class)->createQueryBuilder('zzz');

        $filter_response = $this->filter($queryBuilder, $request);
        if ($filter_response instanceof RedirectResponse) {
            return $filter_response;
        }
        list($filterForm, $queryBuilder) = $filter_response;
        $querryExport = $queryBuilder->getQuery();

        list($<?= $entity_var_plural; ?>, $pagerHtml) = $this->paginator($queryBuilder, $request);

        $totalOfRecordsString = $this->getTotalOfRecordsString($queryBuilder, $request);

        if (count($<?=$entity_var_plural; ?>) <= 0) {
            $this->get('session')->getFlashBag()->add('success', "No se han encontrado registros con los criterios dados" );
            $isValid = false;
        }

        if ($isValid == true && $request->get('filter_action') == 'exportXlsx') {
            $iterableResult = $querryExport->iterate();
            return $<?= $entity_var_singular ?>ExportService->exportXlsx($iterableResult);
        }
        else {
            return $this->render('<?= $templates_path ?>/index.html.twig', [
                '<?= $entity_twig_var_plural ?>' => $<?=$entity_var_plural ?>,
                'pagerHtml' => $pagerHtml,
                'filterForm' => $filterForm->createView(),
                'totalOfRecordsString' => $totalOfRecordsString,
            ]);
        }
    }

    /**
    * Create filter form and process filter request (Input Type).
    *
    */
    protected function filter(QueryBuilder $queryBuilder, Request $request)
    {
        $session = $request->getSession();
        $filterForm = $this->createForm(<?= $form_filter_class_name ?>::class);

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
                    $this->multiSearchBuilderService->searchForm($queryBuilder, $filterForm->get('search'));

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
