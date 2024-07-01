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
                        <thead class="text-center">
                            <th><b>#</b></th>
                            <th><b>Codigo de barra</b></th>
                            <th><b>Articulo</b></th>
                            <th><b>Unidades</b></th>
                            <th><b>Libras</b></th>
                            <th><b>Estado</b></th>
                            <th><b>Usuario creacion</b></th>
                            <th><b>Acciones</b></th>
                        </thead>
                        <tbody class="text-center">
                            <?php
                            $sumLibras = 0;
                            foreach ($codigos as $index => $registro) {
                            ?>
                                <tr>
                                    <td><b><?= ($index + 1) ?> </b></td>
                                    <td><?= $registro->CodigoBarra ?></td>
                                    <td><?= $registro->Articulo . " - " . $registro->Descripcion ?></td>
                                    <td><?= $registro->Unidades ?></td>
                                    <td><?= $registro->Libras ?></td>
                                    <td><?php
                                        if ($registro->Estado == "PROCESO")
                                            echo "<span class='badge bg-green'>En proceso</span>";
                                        else if ($registro->Estado == "ELIMINADO")
                                            echo "<span class='badge bg-red'>Eliminado</span>";
                                        else if ($registro->Estado == "FINALIZADO")
                                            echo "<span class='badge bg-blue'>Finalizado</span>";
                                        ?>
                                    </td>
                                    <td><?= $registro->UsuarioCreacion ?></td>
                                    <td>
                                        <?php
                                        echo Html::a(
                                            '<span class="fas fa-eye"></span>',
                                            [
                                                'view',
                                                'codigoBarra' => $registro->CodigoBarra,
                                                'condicionImprimir' => ''
                                            ],
                                        );
                                        if ($registro->Estado == 'PROCESO') {
                                            echo Html::a(
                                                '<span class="fas fa-pencil-alt"></span>',
                                                [
                                                    'update',
                                                    'codigoBarra' => $registro->CodigoBarra,
                                                ],
                                            );
                                            echo Html::a(
                                                '<span class="fas fa-trash-alt"></span>',
                                                [
                                                    'delete-registro',
                                                    'codigoBarra' => $registro->CodigoBarra,
                                                ],
                                                [
                                                    'id' => 'delete-registro-prod',

                                                ]
                                            );
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php
                                $sumLibras += $registro->Libras;
                            } ?>
                            <tr>
                                <td colspan="6" class="text-right"><b>Sumatoria de libras por fardos </b> </td>
                                <td colspan="2"><b><?= number_format($sumLibras, 2) ?> Lbs.</b></td>
                            </tr>
                            <tr>
                                <td colspan="6" class="text-right"><b>Total fardos producidos</b></td>
                                <td colspan="2"> <?= count($codigos) ?></td>
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
    let finalizarProduccion = document.getElementById("finalizar-produccion")

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
                location.href = finalizarProduccion.href
            } else if (result.isDismissed) {
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