<?php
require_once '../../fpdf/code128.php';
$dbBodega = new PDO("sqlsrv:server=(local);database=BODEGA", 'sa', '$0ftland..');
$codigoBarra = $_GET["codigoBarra"];
$numeroDocumento = $_GET["NumeroDocumento"];
class PDF extends FPDF
{
    // Page header
    function Header()
    {
        // Logo
        //$this->Image('logo.png',10,6,30);
        // Arial bold 15
        $this->SetFont('Arial', 'B', 15);
        // Move to the right
        $this->Cell(80);
        // Title
        //$this->Cell(30,10,'Title',1,0,'C');
        // Line break
        $this->Ln(5);
    }

    // Page footer
    function Footer()
    {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}
$pdf = new PDF_Code128();
//$pdf->AddFont('PressStart2P','', 'PressStart2P-Regular.php');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->AddFont('NovaMono', '', 'NovaMono-Regular.php');
// Instanciation of inherited class
$fechaActual = date('Y-m-d');

$query = $dbBodega->prepare("SELECT REGISTRO.IdRegistro, REGISTRO.Libras, REGISTRO.CodigoBarra, REGISTRO.Articulo, 
REGISTRO.Descripcion, REGISTRO.ContadorImpresiones, REGISTRO.FechaCreacion FROM REGISTRO WHERE CodigoBarra = '$codigoBarra'");

$query->execute();
$data = $query->fetchAll();

$query = $dbBodega->prepare("SELECT CodigoBarra FROM TRANSACCION WHERE NumeroDocumento = :numeroDocumento");
$query->bindParam(':numeroDocumento', $numeroDocumento);
$query->execute();
$codigos = $query->fetchAll(PDO::FETCH_COLUMN);

if (empty($codigos)) {
    die('No se encontraron códigos de barra para el NumeroDocumento proporcionado.');
}

// Recorremos los códigos de barra obtenidos
foreach ($codigos as $codigoBarra) {
    
    // Configuración de fuente y tamaño para el código de barras
    $pdf->SetFont('NovaMono', '', 20);

    // Generación del código de barras
    $pdf->SetX(70);
    $pdf->Code128(10, 10, $codigoBarra, 60, 20);

    // Información adicional
    $pdf->SetY(30);
    $pdf->Cell(60, 10, $codigoBarra, 1, 0, 'L', false);

    $pdf->SetXY(70, 10);
    $pdf->Multicell(130, 10, "Fecha                " . $data[0]['FechaCreacion'], 1, 'L', false);

    $pdf->SetX(70);
    $pdf->Multicell(130, 10, $data[0]['Libras'] . " Lbs.", 1, 'L', false);

    $pdf->SetX(70);
    $pdf->Multicell(130, 10, utf8_decode($data[0]['Descripcion']), 1, 'L', false);
    $pdf->Rect(10, $pdf->GetY() + 10, 190, 0.1, 'D'); // Ajusta el tamaño y posición según sea necesario

    // Salto de línea después de cada etiqueta
    $pdf->Ln(20); // Ajusta el espacio vertical según tus necesidades
    $pdf->AddPage();
}

// $contador = 0;

// $updateImpresion = $dbBodega->prepare("UPDATE REGISTRO 
//     SET ContadorImpresiones = " . ($data[0]["ContadorImpresiones"] + 1) . " WHERE CodigoBarra ='" . $data[0]["CodigoBarra"] . "'");
// $updateImpresion->execute();





$pdf->Output('CodigoBarra-Barril.pdf', 'I');
