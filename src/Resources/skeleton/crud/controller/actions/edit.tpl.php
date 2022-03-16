    #[Route("/{<?= $entity_identifier ?>}/edit", name: "<?= $route_name ?>_edit", methods: ["GET","POST"])]
    public function edit(Request $request, <?= $entity_class_name ?> $<?= $entity_var_singular ?>): Response
    {
        $deleteForm = $this-><?= $entity_var_singular ?>CrudService->createDeleteForm($<?= $entity_var_singular ?>);
        $form = $this->createForm(<?= $form_class_name ?>::class, $<?= $entity_var_singular ?>);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'El registro se ha editado exitosamente');
            return $this->redirectToRoute('<?= $route_name ?>_edit', array('id' => $<?= $entity_var_singular ?>->getId()));
        }

        return $this->render('<?= $templates_path ?>/edit.html.twig', [
            '<?= $entity_twig_var_singular ?>'  => $<?= $entity_var_singular ?>,
            'form'          => $form->createView(),
            'delete_form'   => $deleteForm->createView(),
        ]);
    }
