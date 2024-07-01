<?php
require_once '../../fpdf/code128.php';
$dbBodega = new PDO("sqlsrv:server=(local);database=BODEGA", 'MCAMPOS', 'exmcampos');
$codigoBarra = $_GET["codigoBarra"];
$documentoInv = $_GET['consecutivo'];
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

$query = $dbBodega->prepare("SELECT REGISTRO.IdRegistro, REGISTRO.CodigoBarra, REGISTRO.Articulo, 
REGISTRO.Descripcion, REGISTRO.ContadorImpresiones, REGISTRO.FechaCreacion FROM REGISTRO WHERE CodigoBarra = '$codigoBarra'");
$query->execute();
$data = $query->fetchAll();

/*$updateImpresion = $dbBodega->prepare("UPDATE REGISTRO 
    SET ContadorImpresiones = " . ($data[0]["ContadorImpresiones"] + 1) . " WHERE CodigoBarra ='" . $data[0]["CodigoBarra"] . "'");
$updateImpresion->execute();*/


//PRIMERA ETIQUETA
$pdf->SetFont('NovaMono', '', 20);

$pdf->Code128(10, 10, $codigoBarra, 60, 20);
$pdf->SetY(30);
$pdf->Cell(60, 10, $data[0]['CodigoBarra'], 1, 0, 'L', false);

$pdf->SetXY(70, 10);
$pdf->Multicell(130, 10, "Fecha                " . $data[0]['FechaCreacion'], 1, 'L', false);
$pdf->SetX(70);
$pdf->Multicell(130, 10, $data[0]['Articulo'], 1, 'L', false);
$pdf->SetX(70);
$pdf->Multicell(130, 10, $data[0]['Descripcion'], 1, 'L', false);

// APUNTAR A TRANSACCION
$queryBarriles = $dbBodega->prepare("
SELECT TR.CodigoBarra, RE.Libras 
FROM TRANSACCION TR
INNER JOIN REGISTRO RE
ON RE.CodigoBarra = TR.CodigoBarra
WHERE TR.Documento_Inv = '$documentoInv' AND RE.Activo = 0");

$queryBarriles->execute();
$dataBarriles = $queryBarriles->fetchAll();

$totalLibras = 0;
$yBarras = 40;
$yCeldaBarra = 60;

foreach ($dataBarriles as $barriles) {
    $pdf->Code128(10, $yBarras, $barriles['CodigoBarra'], 60, 20);
    $pdf->SetY($yCeldaBarra);

    $pdf->Cell(60, 10, $barriles['CodigoBarra'], 1, 0, 'L', false);

    $pdf->SetXY(70, $yBarras);
    $pdf->Cell(130, 30, $barriles['Libras'] . ' Libras', 1, 'L', false);
    $yBarras += 30;
    $yCeldaBarra += 30;
}

$pdf->Output('CodigoBarra-Caja.pdf', 'I');
