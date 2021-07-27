    /**
     * @Route("/", name="<?= $route_name ?>_index", methods={"GET"})
     */
    public function index(Request $request, <?= $entity_class_name ?>CrudService $<?= $entity_var_singular ?>CrudService)
    {
        $isValid = true;
        $em = $this->getDoctrine()->getManager();
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $em->getRepository(<?= $entity_class_name ?>::class)->createQueryBuilder('zzz');
        $filter_response = $<?= $entity_var_singular ?>CrudService->filter($queryBuilder, $request);
        if ($filter_response instanceof RedirectResponse) {
            return $filter_response;
        }
        list($filterForm, $queryBuilder) = $filter_response;

        /*** Agregar condiciones de filtrado extra aquí, si se agregan antes del filter() se pierden ***/
        // $queryBuilder->innerJoin()->andWhere()->setParameters([]);

        $querryExport = $queryBuilder->getQuery();

        list($<?= $entity_var_plural; ?>, $pagerHtml) = $<?= $entity_var_singular ?>CrudService->paginator($queryBuilder, $request);

        $totalOfRecordsString = $<?= $entity_var_singular ?>CrudService->getTotalOfRecordsString($queryBuilder, $request);

        if (count($<?=$entity_var_plural; ?>) <= 0) {
            $this->get('session')->getFlashBag()->add('success', "No se han encontrado registros con los criterios dados" );
            $isValid = false;
        }

        if ($isValid == true && $request->get('filter_action') == 'exportXlsx') {
            $iterableResult = $querryExport->iterate();
            return $<?= $entity_var_singular ?>CrudService->exportXlsx($iterableResult);
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
    