<?php
require_once '../../fpdf/code128.php';
$dbBodega = new PDO("sqlsrv:server=(local);database=BODEGA", 'sa', '$0ftland..');
$codigoBarra = $_GET["codigoBarra"];

class PDF extends FPDF
{
    function Header()
    {
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(80);
        $this->Ln(5);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
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

$query = $dbBodega->prepare("SELECT REGISTRO.CodigoBarra,
                                REGISTRO.Articulo,
                                REGISTRO.CreateDate,
                                REGISTRO.Descripcion,
                                REGISTRO.IdRegistro,
                                REGISTRO.EmpresaDestino,
                                REGISTRO.Observaciones,
                                REGISTRO.ContadorImpresiones,
                                REGISTRO.FechaCreacion FROM REGISTRO WHERE CodigoBarra = '$codigoBarra'");
$query->execute();
$data = $query->fetchAll();
$codigoBarra = '';
$contador = 0;
foreach ($data as $valores) {
    $updateImpresion = $dbBodega->prepare("UPDATE REGISTRO 
    SET ContadorImpresiones = " . ($valores["ContadorImpresiones"] + 1) . " WHERE IdRegistro =" . $valores["IdRegistro"]);
    $updateImpresion->execute();
    $codigoBarra = $valores["CodigoBarra"];

    //PRIMERA ETIQUETA
    $pdf->Ln(20);
    $pdf->SetFont('NovaMono', '', 20);
    $pdf->Cell(60, 10,  $codigoBarra, 1, 0, 'C');
    $pdf->SetY(40);
    $pdf->Cell(60, 30, $pdf->Code128(15, 43, $codigoBarra, 45, 25), 1, 0, 'C');
    $pdf->SetFont('Times', '', 13);
    $pdf->SetXY(70, 30);
    $pdf->cell(130, 10,  $valores['Articulo'] . " - \"" . utf8_decode($valores['Descripcion']) . "\"", 1, 'L', false);
    $pdf->SetXY(70, 40);
    $pdf->cell(130, 20,  "\"" .  $valores["Observaciones"]   . "\"", 1, 'L', false);
    $pdf->SetXY(70, 60);
    $pdf->SetFont('NovaMono', '', 15);
    $pdf->cell(130, 10,  "\"" .  $valores["CreateDate"]   . "\"       " . strtoupper($valores['EmpresaDestino']), 1, 'L', false);

    // Imprimir el código Code128 dentro del Multicell
    $pdf->SetXY(10, 70); // Ajustar la posición vertical para imprimir el código dentro del Multicell
    $pdf->SetFont('NovaMono', '', 10);

    $pdf->SetFont('NovaMono', '', 20);
    $pdf->Cell(60, 10, "Cantidad ", 1, 0, 'C');
    $pdf->Cell(65, 10, "Precio", 1, 0, 'C');
    $pdf->Cell(65, 10, "Total", 1, 0, 'C');
    $pdf->SetXY(10, 80);
    $pdf->Cell(60, 10, " ", 1, 0, 'C');
    $pdf->Cell(65, 10, "", 1, 0, 'C');
    $pdf->Cell(65, 10, "", 1, 0, 'C');
    $pdf->SetXY(10, 90);
    $pdf->Cell(60, 10, " ", 1, 0, 'C');
    $pdf->Cell(65, 10, "", 1, 0, 'C');
    $pdf->Cell(65, 10, "", 1, 0, 'C');
    $pdf->SetXY(10, 100);
    $pdf->Cell(60, 10, " ", 1, 0, 'C');
    $pdf->Cell(65, 10, "", 1, 0, 'C');
    $pdf->Cell(65, 10, "", 1, 0, 'C');
    $pdf->SetXY(10, 110);
    $pdf->Cell(60, 10, " ", 1, 0, 'C');
    $pdf->Cell(65, 10, "", 1, 0, 'C');
    $pdf->Cell(65, 10, "", 1, 0, 'C');
    $pdf->SetXY(10, 120);
    $pdf->Cell(60, 10, " ", 1, 0, 'C');
    $pdf->Cell(65, 10, "", 1, 0, 'C');
    $pdf->Cell(65, 10, "", 1, 0, 'C');
    $pdf->SetXY(10, 130);
    $pdf->Cell(60, 10, " ", 1, 0, 'C');
    $pdf->Cell(65, 10, "", 1, 0, 'C');
    $pdf->Cell(65, 10, "", 1, 0, 'C');

    
    $pdf->SetFont('Times', 'B', 10);
    $pdf->Ln(20);

    //SEGUNDA ETIQUETA

    $pdf->SetXY(10, 160);
    $pdf->SetFont('NovaMono', '', 20);
    $pdf->Cell(60, 10, $codigoBarra, 1, 0, 'C');
    $pdf->SetY(170);
    $pdf->Cell(60, 30, $pdf->Code128(15, 173, $codigoBarra, 45, 25), 1, 0, 'C');
    $pdf->SetXY(70, 160);
    $pdf->SetFont('Times', '', 13);
    $pdf->cell(130, 10,  $valores['Articulo'] . " - \"" . utf8_decode($valores['Descripcion']) . "\"", 1, 'L', false);
    $pdf->SetXY(70, 170);
    $pdf->cell(130, 20,  "\"" .  $valores["Observaciones"]   . "\"", 1, 'L', false);
    $pdf->SetXY(70, 190);
    $pdf->SetFont('NovaMono', '', 15);
    $pdf->cell(130, 10,  "\"" .  $valores["CreateDate"]   . "\"       " . strtoupper($valores['EmpresaDestino']), 1, 'L', false);

    $pdf->SetXY(10, 200);
    $pdf->SetFont('NovaMono', '', 10);

    $pdf->SetFont('NovaMono', '', 20);
    $pdf->Cell(60, 10, "Cantidad ", 1, 0, 'C');
    $pdf->Cell(65, 10, "Precio", 1, 0, 'C');
    $pdf->Cell(65, 10, "Total", 1, 0, 'C');
    $pdf->SetXY(10, 210);
    $pdf->Cell(60, 10, " ", 1, 0, 'C');
    $pdf->Cell(65, 10, "", 1, 0, 'C');
    $pdf->Cell(65, 10, "", 1, 0, 'C');
    $pdf->SetXY(10, 220);
    $pdf->Cell(60, 10, " ", 1, 0, 'C');
    $pdf->Cell(65, 10, "", 1, 0, 'C');
    $pdf->Cell(65, 10, "", 1, 0, 'C');
    $pdf->SetXY(10, 230);
    $pdf->Cell(60, 10, " ", 1, 0, 'C');
    $pdf->Cell(65, 10, "", 1, 0, 'C');
    $pdf->Cell(65, 10, "", 1, 0, 'C');
    $pdf->SetXY(10, 240);
    $pdf->Cell(60, 10, " ", 1, 0, 'C');
    $pdf->Cell(65, 10, "", 1, 0, 'C');
    $pdf->Cell(65, 10, "", 1, 0, 'C');
    $pdf->SetXY(10, 250);
    $pdf->Cell(60, 10, " ", 1, 0, 'C');
    $pdf->Cell(65, 10, "", 1, 0, 'C');
    $pdf->Cell(65, 10, "", 1, 0, 'C');
    $pdf->SetXY(10, 260);
    $pdf->Cell(60, 10, " ", 1, 0, 'C');
    $pdf->Cell(65, 10, "", 1, 0, 'C');
    $pdf->Cell(65, 10, "", 1, 0, 'C');
    $pdf->Ln(80);
    $pdf->SetFont('Times', 'B', 10);
    $pdf->Ln(20);
}
$pdf->Output('codigo-barra-fardo.pdf', 'I');
