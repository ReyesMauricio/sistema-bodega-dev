<?php

use yii\helpers\Html;
use yii\helpers\Url;

Yii::$app->formatter->locale = 'en-US';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card card-dark">
            <div class="card-header d-flex justify-content-between">
                <h3 id="asignacion-report" class="card-title"><i class="fas fa-briefcase"></i> &nbsp; Promedios de libras - <?= $fecha  ?> (<?= date("F j, Y", strtotime($fecha)) ?>)</h3>
                </h3>
            </div>
            <div class="card-body p-0">
                <?php if (!isset($transaccion)) { ?>
                    <table class="table table-sm table-striped table-hover table-bordered">
                        <tbody class="text-center">
                            <?php
                            $sumLibras = 0;
                            foreach ($articulos as $registro) {
                            ?>
                                <tr>
                                    <td><b>Articulo: </b></td>
                                    <td width='25%'><?= $registro->Articulo ?></td>
                                    <td><b>Promedio: </b></td>
                                    <td><?= number_format($registro->Libras, 2) ?> Lbs.</td>
                                    <td><b>Mas altas: </b></td>
                                    <td><?= $registro->CodigoBarra ?> Lbs.</td>
                                    <td><b>Mas bajas: </b></td>
                                    <td><?= $registro->Costo ?> Lbs.</td>
                                    <td><b>Total: </b></td>
                                    <td><?= $registro->Descripcion ?> Lbs.</td>
                                </tr>
                            <?php
                                $sumLibras += $registro->Descripcion;
                            } ?>
                            <tr>
                                <td colspan="9" class="text-right"><b>Sumatoria de libras por fardos </b> </td>
                                <td colspan="2"><b><?= number_format($sumLibras, 2) ?> Lbs.</b></td>
                            </tr>
                            <tr>
                                <td colspan="9" class="text-right"><b>Total fardos producidos</b></td>
                                <td> <?= $conteo ?></td>
                            </tr>
                        </tbody>
                    </table>
                <?php } else {
                    echo $transaccion;
                } ?>
            </div>
            <div class="card-footer justify-content-between">
                <?= Html::button('<i class="fa fa-image"></i> Generar reporte en imagen', [
                    'class' => 'btn btn-outline-info', 'id' => 'imprimir',
                ]) ?>
                <?= Html::a(
                    '<i class="fas fa-exclamation-triangle "></i> Finalizar produccion',
                    [
                        'finalizar-produccion',
                        'fecha' => $fecha
                    ],
                    [
                        'id' => 'finalizar-produccion',
                        'class' => 'btn btn-outline-danger',
                    ]
                );
                ?>
            </div>
        </div>
    </div>
</div>
<script>
    var finalizarProduccion = document.getElementById("finalizar-produccion")

    finalizarProduccion.addEventListener("click", (e) => {
        e.preventDefault()
        Swal.fire({
            title: 'Estas a punto de finalizar produccion, deseas continuar?',
            showCancelButton: true,
            icon: 'error',
            confirmButtonText: 'Continuar',
            cancelButtonText: `Cancelar`,
        }).then((result) => {
            if (result.isConfirmed) {
                location.href =  finalizarProduccion.href
            }else if(result.isDismissed){
                location.href = recargarPagina.href
            }
        })
    })
</script>
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