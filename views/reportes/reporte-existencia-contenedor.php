<?php

use yii\helpers\Html;
use yii\helpers\Url;

Yii::$app->formatter->locale = 'en-US';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header d-flex justify-content-between">
                <h3 id="existencia-report" class="card-title"><i class="fas fa-briefcase"></i> &nbsp;Existencia actual de contenedores - <?= date("Y-m-d") ?></h3>
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped table-hover table-bordered">
                    <thead>
                        <th width="16%"><b>Articulo:</b></th>
                        <th width="16%"><b>Descripcion:</b></th>
                        <th><b>Clasificacion: </b></th>
                        <th><b>Bodega: </b></th>
                        <th><b>Libras: </b></th>
                        <th><b>Cantidad de fardos: </b></th>
                    </thead>
                    <tbody>
                        <?php
                        $sumLibras = 0;
                        $sumFardos = 0;
                        foreach ($existencia as $registro) {
                        ?>
                            <tr>
                                <td><?= $registro["Articulo"] ?></td>
                                <td><?= $registro["Descripcion"] ?></td>
                                <td><?= $registro["Clasificacion"] ?></td>
                                <td><?= $registro["BodegaActual"] ?></td>
                                <td><?= $registro["Libras"] ?></td>
                                <td><?= $registro["Cantidad"] ?></td>
                            </tr>
                        <?php
                            $sumLibras += $registro["Libras"];
                            $sumFardos += $registro["Cantidad"];
                        } ?>
                        <tr>
                            <td colspan="4" class="text-right"><b>Totales </b> </td>
                            <td> <b><?= number_format($sumLibras, 2)?> Lbs.</b></td>
                            <td> <b><?= $sumFardos ?></b></td>
                        </tr>
                    </tbody>
                </table>
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
                const barcode = document.getElementById("existencia-report")
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