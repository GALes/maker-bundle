    /**
    * Bulk Action
    * @Route("/bulk-action/", name="<?= $route_name ?>_bulk_action", methods={"POST"})
    */
    public function bulkAction(Request $request)
    {
        $ids = $request->get("ids", array());
        $action = $request->get("bulk_action", "delete");
        
        if ($action == "delete") {
            try {
                $em = $this->getDoctrine()->getManager();
                $repository = $em->getRepository(<?= $entity_class_name ?>::class);
                
                foreach ($ids as $id) {
                    $<?= $entity_var_singular ?> = $repository->find($id);
                    $em->remove($<?= $entity_var_singular ?>);
                    $em->flush();
                }
                
                $this->get('session')->getFlashBag()->add('success', 'El/Los registro/s se ha/n eliminado exitosamente');
                
            } 
            catch (Exception $ex) {
                $this->get('session')->getFlashBag()->add('error', 'No se ha/n podido eliminar el/los registro/s');
            }
        }
        
        return $this->redirect($this->generateUrl('<?= $route_name ?>_index'));
    }
