<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use <?= $entity_full_class_name ?>;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Yectep\PhpSpreadsheetBundle\Factory;

/**
 * Servicio de exportación de registros del CRUD
 */
class <?= $class_name ?>
{
    /**
     * @var Factory
     */
    private $phpExcelFactory;

    public function __construct(Factory $phpExcelFactory)
    {
        $this->phpExcelFactory = $phpExcelFactory;
    }

    public function exportXlsx($<?= $entity_var_plural ?>)
    {
        ini_set('memory_limit', -1);
        ini_set('max_execution_time', 600);

        $actualDate = new \DateTime('now');

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator("CRUD Generator")
            ->setTitle("Registros - {$actualDate->format('d-m-Y H:m:s')}")
            ->getDescription("Registros a la fecha: {$actualDate->format('d-m-Y H:m:s')}")
        ;

        $sheet = $spreadsheet->setActiveSheetIndex(0)->setTitle("Registros_{$actualDate->format('d-m-Y_H.m.s')}");
        $sheet
<?php $columna = 'A'; ?>
<?php foreach ($entity_fields as $field): ?>
            ->setCellValue("<?= $columna ?>1", "<?= ucfirst($custom_helper->asHumanWords($field['metadata']['fieldName'])) ?>")
<?php $columna++ ?>
<?php endforeach; ?>
        ;

        /**
         * @var integer $key
         * @var <?= $entity_class_name; ?> $<?= $entity_var_singular ?>
         */
        foreach ($<?= $entity_var_plural ?> as $key => $<?= $entity_var_singular ?>) {
            $row = $key + 2; // Los datos comienzan desde la fila 2
            $sheet
<?php $columna = 'A'; ?>
<?php foreach ($entity_fields as $field): ?>
                ->setCellValue("<?= $columna ?>$row", $<?= $entity_var_singular ?>->get<?= ucfirst($field['metadata']['fieldName']); ?>())
<?php $columna++ ?>
<?php endforeach; ?>
            ;
        }

        $response = $this->phpExcelFactory->createStreamedResponse($spreadsheet, 'Xlsx');
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            "exportacion_<?= $entity_var_plural ?>_{$actualDate->format('Y-m-d_H.m.s')}.xlsx"
        );

        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }
}