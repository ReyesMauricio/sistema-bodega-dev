<?php
require_once '../../fpdf/code128.php';
$dbBodega = new PDO("sqlsrv:server=(local);database=BODEGA",'sa', '$0ftland..');
$articulo = $_GET["articulo"];
$contenedor = $_GET["contenedor"];
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
$query = $dbBodega->prepare("SELECT REGISTRO.CodigoBarra,
                                REGISTRO.Articulo,
                                REGISTRO.Descripcion,
                                REGISTRO.FechaCreacion,
                                REGISTRO.Libras,
                                UBICACION.Ubicacion
                            FROM REGISTRO INNER JOIN
                            UBICACION ON REGISTRO.IdUbicacion=UBICACION.IdUbicacion
                            WHERE articulo='$articulo' AND documento_inv='$contenedor' AND fechacreacion='$fecha'");
$query->execute();
$data = $query->fetchAll();
$codigoBarra = '';
$contador = 0;
// Contador para controlar las etiquetas por página
// Contador para controlar las etiquetas por página
$etiquetasPorPagina = 0;

// Configuración de fuente y tamaño para el código de barras
// Configuración de fuente y tamaño para el código de barras
$pdf->SetFont('NovaMono', '', 20);

// Contador para etiquetas
$contadorEtiquetas = 0;

foreach ($codigos as $codigoBarra) {
    // Verificar si es necesario agregar una nueva página
    if ($contadorEtiquetas % 3 === 0 && $contadorEtiquetas > 0) {
        $pdf->AddPage();
    }

    // Calcular posición de la etiqueta actual en la página
    $posX = 10 + (60 + 10) * ($contadorEtiquetas % 3); // Ajusta según el espacio deseado entre etiquetas
    $posY = 10 + (80 + 10) * floor($contadorEtiquetas / 3); // Ajusta según el espacio deseado entre filas de etiquetas

    // Generar código de barras
    $pdf->Code128($posX, $posY, $codigoBarra, 60, 20);

    // Información adicional
    $pdf->SetXY($posX + 70, $posY);
    $pdf->Cell(100, 10, $codigoBarra, 1, 1, 'L');

    $pdf->SetXY($posX + 70, $posY + 10);
    $pdf->MultiCell(100, 10, "Fecha: " . $data[0]['FechaCreacion'], 1, 'L');

    $pdf->SetXY($posX + 70, $posY + 20);
    $pdf->MultiCell(100, 10, $data[0]['Libras'] . " Lbs.", 1, 'L');

    $pdf->SetXY($posX + 70, $posY + 30);
    $pdf->MultiCell(100, 10, utf8_decode($data[0]['Descripcion']), 1, 'L');

    // Incrementar contador de etiquetas
    $contadorEtiquetas++;
}

$pdf->Output('CodigoBarra-Barril.pdf', 'I');
