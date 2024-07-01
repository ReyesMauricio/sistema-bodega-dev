<?php

use yii\helpers\Html;
use yii\helpers\Url;

Yii::$app->formatter->locale = 'en-US';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 id="produccion-mesa" class="card-title"><i class="fas fa-briefcase"></i> &nbsp;Produccion desde <?= $fechaInicio ?> hasta <?= $fechaFin ?></h3>
            </div>
            <div class="card-body">
                <table class="table table-sm table-striped table-hover table-bordered">
                    <tr>
                        <td ><b>Mesa:</b></td>
                        <td id="mesa-report"><?= $mesa?></td>
                    </tr>
                    <tr>
                        <td><b>Libras producidas</b></td>
                        <td><?= number_format($libras, 2) ?> Lbs.</td>
                    </tr>
                    <tr>
                        <td><b>Cantidad de paquetes:</b></td>
                        <td><?= count($produccion)?></td>
                    </tr>
                </table>
            </div>
            <div class="card-footer">
                <?= Html::button('<i class="fa fa-plus"></i> Generar reporte', [
                    'class' => 'btn btn-warning', 'id' => 'imprimir',
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
                const barcode = document.getElementById("mesa-report")
                const mesa = document.getElementById('produccion-mesa')
                const a = document.createElement('a')
                a.setAttribute('download', `${mesa.innerText} - Mesa: ${barcode.innerText}`)
                a.setAttribute('href', image)
                a.click()
                canvas.remove()
            })
    }

    var btn = document.querySelector('#imprimir')
    btn.addEventListener('click', capture)
</script>