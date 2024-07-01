<?php
require_once '../../fpdf/code128.php';
$dbBodega = new PDO("sqlsrv:server=(local);database=BODEGA", 'sa', '$0ftland');
$consecutivo = $_GET["consecutivo"];
$fecha = $_GET["fecha"];

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
REGISTRO.Descripcion, REGISTRO.ContadorImpresiones, REGISTRO.FechaCreacion FROM REGISTRO WHERE DOCUMENTO_INV = '$consecutivo' AND FechaCreacion = '$fecha'");

$query->execute();
$data = $query->fetchAll();

$yBarcode = 30;
$yCell = 50;
foreach ($data as $index => $barril) {
    $updateImpresion = $dbBodega->prepare("UPDATE REGISTRO 
    SET ContadorImpresiones = " . ($barril["ContadorImpresiones"] + 1) . " WHERE CodigoBarra ='" . $barril["CodigoBarra"] . "'");
    $updateImpresion->execute();

    //PRIMERA ETIQUETA
    $pdf->SetFont('NovaMono', '', 20);

    $pdf->Code128(10, $yBarcode, $barril["CodigoBarra"] . ' - ' . $barril["Libras"], 60, 20);

    $pdf->SetY($yCell);
    $pdf->Cell(60, 10, $barril['CodigoBarra'], 1, 0, 'L', false);

    $pdf->SetXY(70, $yBarcode);
    $pdf->Multicell(130, 10, "Fecha                " . $barril['FechaCreacion'], 1, 'L', false);
    $pdf->SetX(70);
    $pdf->Multicell(130, 10, $barril['Articulo'] . ' - ' .  $barril['Descripcion'], 1, 'L', false);
    $pdf->SetX(70);
    $pdf->Multicell(130, 10,  $barril["Libras"] . $index, 1, 'L', false);

    if ($index % 4 == 0 && $index >= 4) {
        $pdf->AddPage();
        $yBarcode = 30;
        $yCell = 50;
    } else {
        $yBarcode = $yBarcode + 50;
        $yCell = $yCell + 50;
    }
}

$pdf->Output('CodigoBarra-Barriles.pdf', 'I');
