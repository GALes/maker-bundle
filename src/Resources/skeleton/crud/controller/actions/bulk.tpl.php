    /**
    * Bulk Action
    */
    #[Route("/bulk-action/", name: "<?= $route_name ?>_bulk_action", methods: ["POST"])]
    public function bulkAction(Request $request, BulkActionRunner $bulkRunner): Response
    {
        if (!$this->isCsrfTokenValid(self::BULK_CSRF_ID, (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'El token de seguridad no es válido o expiró. Intente nuevamente.');

            return $this->redirectToRoute('<?= $route_name ?>_index');
        }

        $ids = $request->request->all('ids');
        $action = (string) $request->request->get('bulk_action', '');

        if ('' === $action) {
            $this->addFlash('error', 'Seleccione una acción.');

            return $this->redirectToRoute('<?= $route_name ?>_index');
        }

        try {
            // La AccessDeniedException no se captura: una denegación responde 403 sin ejecutar nada.
            $result = $bulkRunner->run(<?= $entity_class_name ?>::class, $action, $ids, self::BULK_ACTIONS);

            if (0 === $result->affected) {
                $this->addFlash('error', 'No se encontraron registros para aplicar la acción.');
            } else {
                $this->addFlash('success', $result->message ?? sprintf('La acción se aplicó a %d registro/s.', $result->affected));
            }
        } catch (UnknownBulkActionException) {
            throw new BadRequestHttpException('Acción masiva no válida.');
        } catch (DBALException) {
            $this->addFlash('error', 'No se ha/n podido aplicar la acción a el/los registro/s');
        }

        return $this->redirectToRoute('<?= $route_name ?>_index');
    }
