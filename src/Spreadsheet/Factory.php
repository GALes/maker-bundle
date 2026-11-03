<?php

namespace GALes\MakerBundle\Spreadsheet;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\IWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Fábrica de objetos PhpSpreadsheet para los servicios de exportación generados.
 *
 * Reemplaza a Yectep\PhpSpreadsheetBundle\Factory (ese bundle limita phpoffice/phpspreadsheet
 * a la rama 1.x) conservando la firma de sus métodos: para migrar un proyecto alcanza con
 * cambiar el `use` de la clase. Funciona con phpoffice/phpspreadsheet 1.30.7+ y 5.10+.
 */
class Factory
{
    /**
     * Crea una planilla vacía o, si se indica $filename, carga ese archivo detectando su formato.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function createSpreadsheet(?string $filename = null): Spreadsheet
    {
        return $filename === null ? new Spreadsheet() : IOFactory::load($filename);
    }

    /**
     * @param string $type Nombre del writer de PhpSpreadsheet (ej. 'Xlsx', 'Csv', 'Ods').
     *
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function createWriter(Spreadsheet $spreadsheet, string $type): IWriter
    {
        return IOFactory::createWriter($spreadsheet, $type);
    }

    /**
     * @param string $type Nombre del reader de PhpSpreadsheet (ej. 'Xlsx', 'Csv', 'Ods').
     *
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function createReader(string $type): IReader
    {
        return IOFactory::createReader($type);
    }

    /**
     * Devuelve una respuesta que escribe la planilla directamente en la salida.
     *
     * El archivo se genera recién cuando se envía la respuesta, por lo que se pueden seguir
     * cargando filas en $spreadsheet después de llamar a este método.
     *
     * @param array<string, mixed> $headers
     * @param array<string, mixed> $writerOptions Métodos del writer => argumento(s), ej. ['setDelimiter' => ';'].
     *
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function createStreamedResponse(
        Spreadsheet $spreadsheet,
        string $type,
        int $status = 200,
        array $headers = [],
        array $writerOptions = []
    ): StreamedResponse {
        $writer = $this->createWriter($spreadsheet, $type);

        foreach ($writerOptions as $method => $arguments) {
            if (method_exists($writer, $method)) {
                $writer->$method(...(is_array($arguments) ? $arguments : [$arguments]));
            }
        }

        return new StreamedResponse(
            static function () use ($writer): void {
                $writer->save('php://output');
            },
            $status,
            $headers
        );
    }
}
