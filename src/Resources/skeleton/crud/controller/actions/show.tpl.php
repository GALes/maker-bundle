    #[Route("/{<?= $entity_identifier ?>}", name: "<?= $route_name ?>_show"<?php if ($entity_identifier_numeric): ?>, requirements: ["<?= $entity_identifier ?>" => "\d+"]<?php endif ?>, methods: ["GET"])]
    public function show(<?= $entity_class_name ?> $<?= $entity_var_singular ?>): Response
    {
        $this->denyAccessUnlessGranted(<?= $voter_class_name ?>::VER, $<?= $entity_var_singular ?>);

        $deleteForm = $this-><?= $entity_var_singular ?>CrudService->createDeleteForm($<?= $entity_var_singular ?>);

        return $this->render('<?= $templates_path ?>/show.html.twig', [
            '<?= $entity_twig_var_singular ?>' => $<?= $entity_var_singular ?>,
            'delete_form' => $deleteForm->createView(),
        ]);
    }
