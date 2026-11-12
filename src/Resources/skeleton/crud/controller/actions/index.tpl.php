    #[Route("/", name: "<?= $route_name ?>_index", methods: ["GET"])]
    public function index(Request $request, BulkActionRegistry $bulkActions)
    {
        $isValid = true;
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->entityManager->getRepository(<?= $entity_class_name ?>::class)->createQueryBuilder('zzz');
<?php if ($filter_type !== 'none'): ?>
        $filter_response = $this-><?= $entity_var_singular ?>CrudService->filter($queryBuilder, $request);
        if ($filter_response instanceof RedirectResponse) {
            return $filter_response;
        }
        list($filterForm, $queryBuilder) = $filter_response;
<?php endif; ?>

        /*** Agregar condiciones de filtrado extra aquí, si se agregan antes del filter() se pierden ***/
        // $queryBuilder->innerJoin()->andWhere()->setParameters([]);

        $querryExport = $queryBuilder->getQuery();

        list($<?= $entity_var_plural; ?>, $pagerHtml) = $this-><?= $entity_var_singular ?>CrudService->paginator($queryBuilder, $request);

        $totalOfRecordsString = $this-><?= $entity_var_singular ?>CrudService->getTotalOfRecordsString($queryBuilder, $request);

        if (count($<?=$entity_var_plural; ?>) <= 0) {
            $this->addFlash('success', "No se han encontrado registros con los criterios dados" );
            $isValid = false;
        }

        if ($isValid == true && $request->query->get('filter_action') == 'exportXlsx') {
            $iterableResult = $querryExport->toIterable();
            return $this-><?= $entity_var_singular ?>CrudService->exportXlsx($iterableResult);
        }
        else {
            return $this->render('<?= $templates_path ?>/index.html.twig', [
                '<?= $entity_twig_var_plural ?>' => $<?=$entity_var_plural ?>,
                'pagerHtml' => $pagerHtml,
<?php if ($filter_type !== 'none'): ?>
                'filterForm' => $filterForm->createView(),
<?php endif; ?>
                'totalOfRecordsString' => $totalOfRecordsString,
                'bulkActions' => $bulkActions->describe(array_keys(self::BULK_ACTIONS), <?= $entity_class_name ?>::class),
            ]);
        }
    }
    