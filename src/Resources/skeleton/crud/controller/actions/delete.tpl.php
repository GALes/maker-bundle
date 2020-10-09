    /**
     * @Route("/{<?= $entity_identifier ?>}", name="<?= $route_name ?>_delete", methods={"DELETE"})
     */
    public function delete(Request $request, <?= $entity_class_name ?> $<?= $entity_var_singular ?>): Response
    {
        $form = $this->createDeleteForm($<?= $entity_var_singular ?>);
        $form->handleRequest($request);

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
