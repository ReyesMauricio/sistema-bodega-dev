<?php

use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

Yii::$app->formatter->locale = 'en-US';
$empresa = $empresa == 'CA' ? 'CARISMA' : $empresa;
$empresa = $empresa == 'E0' ? 'EVER' : $empresa;
$empresa = $empresa == 'N0' ? 'NERY' : $empresa;
$empresa = $empresa == 'T0' ? 'CANNYSHOP' : $empresa;

?>

<div class="row">
    <div class="col-md-12 mt-2">
        <div class="card card-primary">
            <div class="card-header d-flex justify-content-between">
                <h3 id="asignacion-report" class="card-title"><i class="fas fa-briefcase"></i> &nbsp;Resumen de traslado <?= date("F j, Y", strtotime($fechaInicio)) . " - " . date("F j, Y", strtotime($fechaFin)) ?></h3>
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped table-hover table-bordered">
                    <thead>
                        <th><b>Articulo:</b></th>
                        <th><b>Descripcion</b></th>
                        <th><b>Cantidad: </b></th>
                        <th><b>Total </b></th>
                    </thead>
                    <tbody>
                        <?php
                        $total = 0;
                        if (count($despachos) == 0) {
                            echo "<tr><td colspan='8'><h1>No existen registros para mostrar en $empresa</h1></td></tr>";
                        } else {

                            foreach ($despachos as $despacho) { ?>
                                <tr>
                                    <td><?= $despacho["ArticuloDetalle"] ?></td>
                                    <td><?= $despacho["DESCRIPCION"] ?></td>
                                    <td><?= $despacho["Cantidad"] ?></td>
                                    <td><?= $despacho["PrecioUnitario"]  ?></td>
                                </tr>
                            <?php
                                $total += $despacho["PrecioUnitario"];
                            } ?>
                            <tr>
                                <td colspan="3" class="text-right"><b>Total final</b></td>
                                <td><?= $total ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <?= Html::button('<i class="fa fa-image"></i> Generar reporte en imagen', [
                    'class' => 'btn btn-outline-danger', 'id' => 'imprimir',
                ]) ?>
                <?php
                if ($registros != '') {
                    echo Html::a(
                        '<i class="fa fa-download"></i> Descargar reporte',
                        Url::toRoute(["crear-reporte-despacho-excel", 'registros' => $registros, 'fechaInicio' => $fechaInicio, 'fechaFin' => $fechaFin, 'empresa' => $empresa]),
                        [
                            'id' => 'boton-generar-reporte',
                            'class' => 'btn btn-warning',
                            'target' => "_blank",
                            'data-pjax' => false
                        ]
                    );
                }
                ?>
            </div>
        </div>
    </div>
</div>
<script>
    function capture() {
        let captureElement = document.querySelector('#createModalContent') // Select the element you want to capture. Select the <body> element to capture full page.
        html2canvas(captureElement)
            .then(canvas => {
                canvas.style.display = 'none'
                document.body.appendChild(canvas)
                return canvas
            })
            .then(canvas => {
                const image = canvas.toDataURL('image/png')
                const barcode = document.getElementById("asignacion-report")
                const a = document.createElement('a')
                a.setAttribute('download', `${barcode.innerText}`)
                a.setAttribute('href', image)
                a.click()
                canvas.remove()
            })
    }

    var btn = document.querySelector('#imprimir')
    btn.addEventListener('click', capture)
</script>