<?php
session_start();
$dbBodega = new PDO("sqlsrv:server=(local);database=BODEGA", 'sa', '$0ftland..');
require_once('../../TCPDF/examples/tcpdf_include.php');
require('../../phpqrcode/qrlib.php');
require_once '../../fpdf/code128.php';

$codigo = $_GET['codigoBarra'];
$esquema = $_SESSION['esquema'];
$image =  '';

class MyTCPDF extends TCPDF
{
    public function getBufferContents()
    {
        return $this->getBuffer();
    }
}

$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdfBuffer = new MyTCPDF();
$pdf->AddPage();
$pdf->SetFont('Helvetica', '', 6);
$ruta = '../../web/logos-empresas/';

$query = $dbBodega->prepare(
    "SELECT CONCAT(DETALLEREGISTRO.ArticuloDetalle,'\t',REGISTRO.CodigoBarra,'\t1\n') AS QR, 
    CONCAT(DETALLEREGISTRO.ArticuloDetalle,'\t1\n') AS BARCODE,
	REGISTRO.CodigoBarra, DETALLEREGISTRO.ArticuloDetalle, ex.descripcion,  
	DETALLEREGISTRO.Cantidad, DETALLEREGISTRO.IdDetalleRegistro, DETALLEREGISTRO.ContadorImpresiones, REGISTRO.EmpresaDestino
    FROM REGISTRO 
    INNER JOIN DETALLEREGISTRO 
    ON REGISTRO.IdRegistro = DETALLEREGISTRO.IdRegistro
    INNER JOIN [SOFTLAND]." . $esquema . ".[articulo] ex 
    ON detalleregistro.ArticuloDetalle = ex.articulo
    WHERE REGISTRO.CodigoBarra ='$codigo'"
);
$query->execute();
$data = $query->fetchAll(\PDO::FETCH_ASSOC);

$consulta = json_encode($data);
$arr = json_decode($consulta, true);
$suma = 0;
foreach ($data as $dataCantidad) {
    $cantidad = $dataCantidad['Cantidad'];
    $suma += $cantidad;
    $updateContador = $dbBodega->prepare("UPDATE DETALLEREGISTRO 
    SET ContadorImpresiones = " . ($dataCantidad["ContadorImpresiones"] + 1) . " WHERE IdDetalleRegistro =" . $dataCantidad["IdDetalleRegistro"]);
    $updateContador->execute();
}

$col_width = 31; // Ancho de cada columna
$row_height = 52; // Altura de cada fila
$x = 15; // Posición inicial x
$y = 15; // Posición inicial y
$next_y = $y; // Variable para almacenar la posición y de la siguiente celda
$current_col = 0; // Columna actual
$total_paginas = 0;
$cantidadPagina = 0;
$total_vinetas = 0;
$row_height_personalizado = 28;
$row_width_personalizado = 30;
//echo($json_encode);
foreach ($arr as $val) {
    $cantidad = $val['Cantidad'];
    $descripcion = $val['descripcion'];
    $codigoBarra = $val['CodigoBarra'];
    $codigoArticulo = $val['ArticuloDetalle'];
    $destino = $val['EmpresaDestino'];
    $data = range(1, $cantidad);

    $cantidadPaginasEsperadas = ceil($suma / 30);
    foreach ($data as $key => $value) {
        $dir = '../../web/logos-empresas/temp/';
        $qrData = '';
        if (!file_exists($dir))
            mkdir($dir);
        if ($destino == 'cany') {
            $filename = $dir . $codigoBarra . '_' . $codigoArticulo . 'test.png';
            $qrData = $val['QR'];
        } else {
            $filename = $dir  . $codigoArticulo . 'test.png';
            $qrData = $val['BARCODE'];
            
        }
        
        QRcode::png($qrData, $filename, 'H', 8, 1);
        $col = $current_col; // Definir la columna actual
        $row = floor($key / 7); // Calcular la fila actual
        if ($destino == "cany") {
            $imagen =  $ruta . 'logo-cany.jpeg';
        } else if ($destino == "boutique") {
            $imagen =  $ruta . 'logo-boutique.jpg';
        } else if ($destino == "carisma") {
            $imagen =  $ruta . 'logo-carisma.jpeg';
        } else if ($destino == "nyc") {
            $imagen =  $ruta . 'logo-nyc.png';
        } else if ($destino == "nys") {
            $imagen =  $ruta . 'logo-nys.jpeg';
        }

        //
        //$cantidadPagina++;
        // Calcular la posición x e y de la celda
        $cell_x = $x + ($col * $col_width);
        $cell_y = $next_y;
        $html = '<style>
						.descripcion{
							margin-top: 45px;
						}
					</style>';
        $html .= '<div class="container"><br><br><br><br>';
        $html .=  '<div class="descripcion">' . $descripcion . '</div>';
        $html .= '</div>';
        $html .= '<img src="' . $filename . '" height="30" alt="Código QR">';
        if ($destino == 'cany') {
            $html .=  '<br>' . $codigoBarra . '';
        }
        $html .=  '<br>' . $codigoArticulo . '<br>';
        $html .= '<div class="img-fluid" >
                        <img  src="' . $imagen . '" height="30" width="90" alt="Código QR"><br>
                    </div>';



        $total_vinetas++;
        //$cantidadPagina=ceil($total_vinetas/30);
        // Agregar el valor a la celda
        $pdf->SetXY($cell_x, $cell_y);
        //$pdf->WriteHTMLCell($col_width, $row_height, $content, 1, 'C');
        $pdf->writeHTMLCell($row_width_personalizado, $row_height_personalizado, '', '', $html, 1, 1, 0, true, 'C', true);
        // Actualizar la posición y de la siguiente celda
        $next_y = $cell_y + $row_height;
        // Verifcar si se llegó al final de la página
        if ($next_y > $pdf->getPageHeight() - 50) {
            // Posicionar la siguiente columna a la derecha
            $current_col++;
            $next_y = $y;
            // Verificar si se llegó al final de la última columna
            if ($current_col >= 6) {
                //$cantidadPaginasEsperadas = ceil(($total_vinetas) / 30);
                // Verificar si se ha alcanzado la última página
                if ($total_paginas + 1 != $cantidadPaginasEsperadas) {
                    // Agregar una nueva página
                    $total_paginas = ceil(($total_vinetas) / 30);
                    $pdf->AddPage();
                    $current_col = 0;
                }
            }
        }
    }
}
$pdf->Output('etiquetasDetalle.pdf', 'I');
