<?php

use yii\helpers\Html;
use yii\helpers\Url;

Yii::$app->formatter->locale = 'es_ES';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header d-flex justify-content-between">
                <h3 id="asignacion-report" class="card-title"><i class="fas fa-briefcase"></i> &nbsp;Asignacion de codigos - <?= date("F j, Y", strtotime($fechaInicio)) . " - " . date("F j, Y", strtotime($fechaFin)) ?></h3>
                </h3>
            </div>
            <div class="card-body p-0">
                <?php if (!isset($transaccion)) { ?>
                    <table class="table table-sm table-striped table-hover table-bordered">
                        <thead>
                            <th><b>Codigo de barra</b></th>
                            <th><b>Articulo:</b></th>
                            <th><b>Descripcion:</b></th>
                            <th><b>Clasificacion: </b></th>
                            <th><b>Bodega: </b></th>
                            <th><b>Activo: </b></th>
                            <th><b>Empaque</b></th>
                            <th><b>Libras: </b></th>
                            <th><b>Fecha de asignacion: </b></th>
                        </thead>
                        <tbody>
                            <?php
                            $sumLibras = 0;
                            foreach ($asignacion as $registro) {
                            ?>
                                <tr>
                                    <td><?= $registro["CodigoBarra"] ?></td>
                                    <td><?= $registro["Articulo"] ?></td>
                                    <td><?= $registro["Descripcion"] ?></td>
                                    <td><?= $registro["Clasificacion"] ?></td>
                                    <td><?= $registro["BodegaActual"] ?></td>
                                    <td><?= $registro["Activo"] ?></td>
                                    <td><?= $registro["TipoEmpaque"] ?></td>
                                    <td><?= $registro["Libras"] ?></td>
                                    <td><?= $registro["Fecha"] ?></td>
                                </tr>
                            <?php
                                $sumLibras += $registro["Libras"];
                            } ?>
                            <tr>
                                <td colspan="7" class="text-right"><b>Totales </b> </td>
                                <td colspan="2"><b><?= number_format($sumLibras, 2) ?> Lbs.</b></td>
                            </tr>

                        </tbody>
                    </table>
                <?php } else {
                    echo $transaccion;
                } ?>
            </div>
            <div class="card-footer">
                <?= Html::button('<i class="fa fa-image"></i> Generar reporte en imagen', [
                    'class' => 'btn btn-outline-danger', 'id' => 'imprimir',
                ]) ?>
            </div>
        </div>
    </div>
</div>
<script>
    function capture() {
        const captureElement = document.querySelector('#createModalContent') // Select the element you want to capture. Select the <body> element to capture full page.
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