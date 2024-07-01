<?php
require_once '../../fpdf/code128.php';
$dbBodega = new PDO("sqlsrv:server=(local);database=BODEGA", 'sa', '$0ftland..');
$codigoBarra = $_GET["codigoBarra"];
$idRegistro = $_GET["IdRegistro"];
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
REGISTRO.Descripcion, REGISTRO.ContadorImpresiones, REGISTRO.FechaCreacion FROM REGISTRO WHERE CodigoBarra = '$codigoBarra' AND IdRegistro = $idRegistro");

$query->execute();
$data = $query->fetchAll();


$contador = 0;

$updateImpresion = $dbBodega->prepare("UPDATE REGISTRO 
    SET ContadorImpresiones = " . ($data[0]["ContadorImpresiones"] + 1) . " WHERE CodigoBarra ='" . $data[0]["CodigoBarra"] . "'");
$updateImpresion->execute();


//PRIMERA ETIQUETA
$pdf->SetFont('NovaMono', '', 20);

$pdf->SetX(70);
$pdf->Code128(10, 10, $codigoBarra, 60, 20);
$pdf->SetY(30);
$pdf->Cell(60, 10, $data[0]['CodigoBarra'], 1, 0, 'L', false);

$pdf->SetXY(70, 10);
$pdf->Multicell(130, 10, "Fecha                " . $data[0]['FechaCreacion'], 1, 'L', false);
$pdf->SetX(70);
$pdf->Multicell(130, 10, $data[0]['Libras'] . " Lbs.", 1, 'L', false);
$pdf->SetX(70);
$pdf->Multicell(130, 10, utf8_decode($data[0]['Descripcion']), 1, 'L', false);


$pdf->Output('CodigoBarra-Barril.pdf', 'I');
