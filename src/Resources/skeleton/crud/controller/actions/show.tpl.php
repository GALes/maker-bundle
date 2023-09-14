    #[Route("/{<?= $entity_identifier ?>}", name: "<?= $route_name ?>_show", methods: ["GET"])]
    public function show(<?= $entity_class_name ?> $<?= $entity_var_singular ?>): Response
    {
        $deleteForm = $this-><?= $entity_var_singular ?>CrudService->createDeleteForm($<?= $entity_var_singular ?>);

        return $this->render('<?= $templates_path ?>/show.html.twig', [
            '<?= $entity_twig_var_singular ?>' => $<?= $entity_var_singular ?>,
            'delete_form' => $deleteForm->createView(),
        ]);
    }
