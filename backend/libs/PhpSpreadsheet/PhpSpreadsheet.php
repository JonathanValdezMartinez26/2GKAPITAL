<?php

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class PHPSpreadsheet
{
    private const formatoMoneda = '"$"#,##0.00';
    private const formatoPorcentaje = '0.00%';
    private const formatoFecha = 'dd/mm/yyyy';
    private const formatoFechaHora = 'dd/mm/yyyy hh:mm:ss';

    /**
     * ColumnaExcel
     * 
     * Genera una configuración de columna para Excel.
     *
     * @param string $letra La letra de la columna en Excel.
     * @param string $campo El nombre del campo asociado a la columna.
     * @param string $titulo (Opcional) El título de la columna. Si no se proporciona, se usará el nombre del campo.
     * @param array $estilo (Opcional) Un array con los estilos aplicables a la columna.
     * @param bool $total (Opcional) Indica si la columna es un total. Por defecto es false.
     *
     * @return array Un array con la configuración de la columna, incluyendo la letra, el campo, el estilo, el título y si es un total.
     */
    public static function ColumnaExcel($campo, $titulo = '', $configuracion = [])
    {
        $defecto = ['letra' => '', 'estilo' => [], 'total' => false];
        $configuracion = array_merge($defecto, $configuracion);

        $titulo = $titulo == '' ? $campo : $titulo;

        return [
            'campo' => $campo,
            'titulo' => $titulo,
            'estilo' => $configuracion['estilo'],
            'letra' => $configuracion['letra'],
            'total' => $configuracion['total'],
        ];
    }

    /**
     * GetEstilosExcel
     * 
     * Este método devuelve un array de estilos predefinidos para hojas de Excel.
     * Los estilos incluyen:
     * - 'titulo': Fuente en negrita, alineación centrada y bordes delgados alrededor de todas las celdas.
     * - 'centrado': Alineación centrada.
     * - 'moneda': Alineación a la derecha con un formato de número de moneda simple ($1,000.00).
     * - 'fecha': Alineación centrada con un formato de fecha (DD/MM/YYYY).
     * - 'fecha_hora': Alineación centrada con un formato de fecha y hora (DD/MM/YYYY HH:MM:SS).
     * 
     * @return array Un array asociativo de estilos para celdas de Excel.
     */
    public static function GetEstilosExcel()
    {
        return [
            'titulo' => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'alignment' => ['horizontal' => Style\Alignment::HORIZONTAL_CENTER],
                'borders' => [
                    'allBorders' => ['borderStyle' => Style\Border::BORDER_THIN]
                ],
                'fill' => [
                    'fillType' => Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '000000']
                ]
            ],
            'encabezado' => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Style\Alignment::HORIZONTAL_CENTER],
                'borders' => [
                    'allBorders' => ['borderStyle' => Style\Border::BORDER_THIN]
                ],
                'fill' => [
                    'fillType' => Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'A6A6A6']
                ]
            ],
            'centrado' => [
                'alignment' => ['horizontal' => Style\Alignment::HORIZONTAL_CENTER]
            ],
            'fecha' => [
                'alignment' => ['horizontal' => Style\Alignment::HORIZONTAL_CENTER],
                'numberFormat' => ['formatCode' => self::formatoFecha]
            ],
            'fecha_hora' => [
                'alignment' => ['horizontal' =>  Style\Alignment::HORIZONTAL_CENTER],
                'numberFormat' => ['formatCode' => self::formatoFechaHora]
            ],
            'moneda' => [
                'alignment' => ['horizontal' => Style\Alignment::HORIZONTAL_RIGHT],
                'numberFormat' => ['formatCode' => self::formatoMoneda]
            ],
            'porcentaje' => [
                'alignment' => ['horizontal' => Style\Alignment::HORIZONTAL_CENTER],
                'numberFormat' => ['formatCode' => self::formatoPorcentaje]
            ]
        ];
    }

    /**
     * GeneraExcel
     * 
     * Genera un archivo Excel con los datos proporcionados.
     *
     * @param string $nombre_archivo Nombre del archivo Excel a generar.
     * @param string $nombre_hoja Nombre de la hoja dentro del archivo Excel.
     * @param string $titulo_reporte Título del reporte que se mostrará en la primera fila.
     * @param array $columnas Arreglo de columnas con la estructura:
     *                        [
     *                          'letra' => 'A', // Letra de la columna
     *                          'titulo' => 'Título de la columna', // Título de la columna
     *                          'campo' => 'campo_de_datos', // Campo de los datos
     *                          'estilo' => [], // Estilo de la celda
     *                          'total' => true/false // Indica si se debe calcular el total de la columna
     *                        ]
     * @param array $filas Arreglo de filas con los datos a incluir en el reporte.
     *
     * @return void
     */
    public static function GeneraExcel($nombre_archivo, $nombre_hoja, $titulo_reporte, $columnas, $filas)
    {
        $totales = [];
        $libro = new Spreadsheet();
        $hoja = $libro->getActiveSheet();
        $hoja->setTitle($nombre_hoja);

        // Encabezados de columna
        foreach ($columnas as $key => $columna) {
            if (!isset($columna['letra']) || $columna['letra'] === '') {
                $columna['letra'] = self::getLetraColumna($key);
                $columnas[$key]['letra'] = $columna['letra'];
            }

            $hoja->setCellValue($columna['letra'] . '2', $columna['titulo']);
            $hoja->getStyle($columna['letra'] . '2')->applyFromArray(self::GetEstilosExcel()['encabezado']);
            $hoja->getColumnDimension($columna['letra'])->setAutoSize(true);
            if ($columna['total']) array_push($totales, $columna);
        }

        // Título del reporte
        $hoja->setCellValue('A1', $titulo_reporte);
        $hoja->mergeCells('A1:' . $columnas[count($columnas) - 1]['letra'] . '1');
        $hoja->getStyle('A1')->applyFromArray(self::GetEstilosExcel()['titulo']);

        // Filas de datos
        $noFila = 3;
        foreach ($filas as $key => $fila) {
            if ($noFila % 2 == 0) {
                $hoja->getStyle('A' . $noFila . ':' . $columnas[count($columnas) - 1]['letra'] . $noFila)
                    ->getFill()
                    ->setFillType(Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('C5D9F1');
            }

            foreach ($columnas as $key => $columna) {
                $estiloCelda = $columna['estilo'];
                $estiloCelda['borders']['left']['borderStyle'] = Style\Border::BORDER_THIN;
                $estiloCelda['borders']['right']['borderStyle'] = Style\Border::BORDER_THIN;

                if ($columna['estilo'] === self::GetEstilosExcel()['fecha'])
                    $hoja->setCellValue($columna['letra'] . $noFila, self::convierteFecha('d/m/Y', $fila[$columna['campo']]));
                else if ($columna['estilo'] === self::GetEstilosExcel()['fecha_hora'])
                    $hoja->setCellValue($columna['letra'] . $noFila, self::convierteFecha('d/m/Y H:i:s', $fila[$columna['campo']]));
                else
                    $hoja->setCellValue($columna['letra'] . $noFila, html_entity_decode($fila[$columna['campo']], ENT_QUOTES, "UTF-8"));

                $hoja->getStyle($columna['letra'] . $noFila)->applyFromArray($estiloCelda);
            }

            $noFila += 1;
        }

        // Poner borde a la última fila
        $hoja->getStyle('A' . ($noFila - 1) . ':' . $columnas[count($columnas) - 1]['letra'] . ($noFila - 1))
            ->applyFromArray([
                'borders' => [
                    'bottom' => ['borderStyle' => Style\Border::BORDER_THIN]
                ]
            ]);

        // Incluir totales si es necesario
        if (count($totales) > 0) {
            $noFila += 1;
            self::AddTotales($hoja, $noFila, $columnas, $totales);
        }

        // Seleccionar celda A1, congelar en la fila 3, aplicar filtro a las columnas
        $hoja->setSelectedCell('A3');
        $hoja->freezePane('A3');
        $hoja->setAutoFilter('A2:' . $columnas[count($columnas) - 1]['letra'] . '2');
        $hoja->setSelectedCell('A1');

        // Enviar descarga
        self::EnviarDescarga($libro, $nombre_archivo);
    }

    /**
     * AddTotales
     * 
     * Agrega una fila de totales a la hoja de cálculo.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $hoja La hoja de cálculo donde se agregarán los totales.
     * @param int $noFila El número de fila donde se colocarán los totales.
     * @param array $columnas Un arreglo de columnas que se utilizarán para aplicar estilos.
     * @param array $totales Un arreglo de totales que contiene la letra de la columna y el estilo a aplicar.
     *
     * @return void
     */
    public static function AddTotales($hoja, $noFila, $columnas, $totales)
    {
        $hoja->setCellValue('A' . $noFila, 'Totales');
        $hoja->getStyle('A' . $noFila)->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Style\Alignment::HORIZONTAL_CENTER]
        ]);

        // Poner estilo a la fila de totales
        $hoja->getStyle('A' . $noFila . ':' . $columnas[count($columnas) - 1]['letra'] . $noFila)
            ->applyFromArray([
                'borders' => [
                    'top' => ['borderStyle' => Style\Border::BORDER_THIN],
                    'bottom' => ['borderStyle' => Style\Border::BORDER_THIN],
                    'left' => ['borderStyle' => Style\Border::BORDER_THIN],
                    'right' => ['borderStyle' => Style\Border::BORDER_THIN]
                ],
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'A6A6A6']
                ]
            ]);

        // Poner fórmulas para totales
        foreach ($totales as $key => $total) {
            $hoja->setCellValue($total['letra'] . $noFila, '=SUBTOTAL(9,' . $total['letra'] . '3:' . $total['letra'] . ($noFila - 2) . ')');
            $hoja->getStyle($total['letra'] . $noFila)->applyFromArray($total['estilo']);
        }
    }

    /**
     * EnviarDescarga
     *
     * Este método configura los encabezados HTTP necesarios para la descarga de un archivo
     * Excel en formato .xlsx y envía el archivo al cliente.
     *
     * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $libro El objeto Spreadsheet que se va a descargar.
     * @param string $nombre_archivo El nombre del archivo que se va a descargar.
     *
     * @return void
     */
    public static function EnviarDescarga($libro, $nombre_archivo)
    {
        // Configuración de encabezados HTTP
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $nombre_archivo . '.xlsx"');
        header('Cache-Control: max-age=0');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Pragma: public');

        // Mandar la descarga del archivo
        $writer = new Xlsx($libro);
        $writer->save('php://output');
    }

    private static function getLetraColumna($indice)
    {
        $letra = '';
        while ($indice >= 0) {
            $letra = chr($indice % 26 + 65) . $letra;
            $indice = intval($indice / 26) - 1;
        }
        return $letra;
    }

    private static function convierteFecha($formato, $fecha)
    {
        if (!$fecha || empty($fecha) || $fecha === '') return null;
        $f = DateTime::createFromFormat($formato, $fecha);

        if ($f === false) return null;
        $f = Date::PHPToExcel($f);

        if ($f === false) return null;
        return $f;
    }
}
