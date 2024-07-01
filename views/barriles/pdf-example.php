<?php
require_once '../../fpdf/code128.php';

// Conexión a la base de datos
$dbBodega = new PDO("sqlsrv:server=(local);database=BODEGA", 'sa', '$0ftland..');

// Obtener parámetros de la URL (asegúrate de validar y sanitizar estos valores)
$codigoBarra = $_GET["codigoBarra"];
$NumeroDocumento = $_GET["NumeroDocumento"];

// Clase extendida de FPDF para generar el PDF
class PDF extends FPDF
{
    function Header()
    {
        // Encabezado del documento si fuera necesario
    }

    function Footer()
    {
        // Pie de página con número de página
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

// Instancia del objeto PDF
$pdf = new PDF_Code128();
$pdf->AliasNbPages();
$pdf->AddPage();

// Consulta para obtener los códigos de barra asociados al NumeroDocumento
$query = $dbBodega->prepare("SELECT CodigoBarra FROM TRANSACCION WHERE NumeroDocumento = :NumeroDocumento");
$query->bindParam(':NumeroDocumento', $NumeroDocumento);
$query->execute();
$codigos = $query->fetchAll(PDO::FETCH_COLUMN);

if (empty($codigos)) {
    die('No se encontraron códigos de barra para el NumeroDocumento proporcionado.');
}

// Iterar sobre cada código de barra encontrado y generar una etiqueta para cada uno
foreach ($codigos as $codigo) {
    // Obtener datos del registro según el código de barras
    $queryRegistro = $dbBodega->prepare("SELECT Libras, Descripcion, FechaCreacion FROM REGISTRO WHERE CodigoBarra = :codigoBarra");
    $queryRegistro->bindParam(':codigoBarra', $codigo);
    $queryRegistro->execute();
    $data = $queryRegistro->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        die('No se encontraron datos para el código de barra: ' . $codigo);
    }

    // Configuración y generación de la etiqueta en el PDF
    $pdf->SetFont('Arial', '', 12);

    // Columna Izquierda: Código de barras y número
    $pdf->Cell(80, 20, $codigo, 1, 0, 'C');
    $pdf->Ln(); // Salto de línea

    // Columna Derecha: Fecha, Libras y Descripción
    $pdf->Cell(0, 10, 'Fecha:', 0, 1, 'L'); // Título de Fecha
    $pdf->Cell(80, 20, $data['FechaCreacion'], 1, 0, 'L'); // Valor de Fecha
    $pdf->Ln(); // Salto de línea

    $pdf->Cell(0, 10, 'Libras:', 0, 1, 'L'); // Título de Libras
    $pdf->Cell(80, 20, $data['Libras'] . ' Lbs.', 1, 0, 'L'); // Valor de Libras
    $pdf->Ln(); // Salto de línea

    // Descripción (sin título)
    $pdf->Cell(0, 10, '', 0, 1, 'L'); // Espacio antes de Descripción
    $pdf->MultiCell(0, 10, utf8_decode($data['Descripcion']), 1); // Valor de Descripción

    // Agregar una nueva página para la siguiente etiqueta si no es la última
    if ($codigo !== end($codigos)) {
        $pdf->AddPage();
    }
}

$pdf->Output('Etiquetas-Codigos-Barra.pdf', 'I'); // Salida del PDF
