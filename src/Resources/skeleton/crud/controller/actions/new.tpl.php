    #[Route("/new", name: "<?= $route_name ?>_new", methods: ["GET","POST"])]
    public function new(Request $request): Response
    {
        $<?= $entity_var_singular ?> = new <?= $entity_class_name ?>();
        $form = $this->createForm(<?= $form_class_name ?>::class, $<?= $entity_var_singular ?>);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($<?= $entity_var_singular ?>);
            $this->entityManager->flush();

            $editLink = $this->generateUrl('<?= $route_name ?>_edit', array('id' => $<?= $entity_var_singular ?>->getId()));
            $this->addFlash('success', "<a href='$editLink'>El nuevo registro se ha creado exitosamente.</a>" );
            
            $nextAction=  $request->get('submit') == 'save' ? '<?= $route_name ?>_index' : '<?= $route_name ?>_new';
            return $this->redirectToRoute($nextAction);
        }

        return $this->render('<?= $templates_path ?>/new.html.twig', [
            '<?= $entity_twig_var_singular ?>' => $<?= $entity_var_singular ?>,
            'form' => $form->createView(),
        ]);
    }
