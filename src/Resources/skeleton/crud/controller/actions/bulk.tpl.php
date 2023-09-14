    /**
    * Bulk Action
    */
    #[Route("/bulk-action/", name: "<?= $route_name ?>_bulk_action", methods: ["POST"])]
    public function bulkAction(Request $request)
    {
        $ids = $request->get("ids", array());
        $action = $request->get("bulk_action", "delete");
        
        if ($action == "delete") {
            try {
                $repository = $this->entityManager->getRepository(<?= $entity_class_name ?>::class);
                
                foreach ($ids as $id) {
                    $<?= $entity_var_singular ?> = $repository->find($id);
                    $this->entityManager->remove($<?= $entity_var_singular ?>);
                    $this->entityManager->flush();
                }

                $this->addFlash('success', 'El/Los registro/s se ha/n eliminado exitosamente');
            } 
            catch (Exception $ex) {
                $this->addFlash('error', 'No se ha/n podido eliminar el/los registro/s');
            }
        }
        
        return $this->redirect($this->generateUrl('<?= $route_name ?>_index'));
    }
