    /**
    * Bulk Action
    */
    #[Route("/bulk-action/", name: "<?= $route_name ?>_bulk_action", methods: ["POST"])]
    public function bulkAction(Request $request, <?= $bulk_service_class_name ?> $bulkDeleteService)
    {
        $ids = $request->request->all("ids");
        $action = $request->request->get("bulk_action", "delete");

        if ($action == "delete") {
            try {
                // La AccessDeniedException no se captura: una denegación responde 403 sin borrar nada.
                $bulkDeleteService->eliminar(<?= $entity_class_name ?>::class, $ids, <?= $voter_class_name ?>::ELIMINAR);
                $this->addFlash('success', 'El/Los registro/s se ha/n eliminado exitosamente');
            }
            catch (DBALException $ex) {
                $this->addFlash('error', 'No se ha/n podido eliminar el/los registro/s');
            }
        }

        return $this->redirect($this->generateUrl('<?= $route_name ?>_index'));
    }
