<?php

use yii\helpers\Html;
use yii\helpers\Url;

Yii::$app->formatter->locale = 'en-US';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card card-dark">
            <div class="card-header d-flex justify-content-between">
                <h3 id="asignacion-report" class="card-title"><i class="fas fa-briefcase"></i> &nbsp; Movimientos en fecha: <?= date("F j, Y", strtotime($fechaInicio)) . " - " . date("F j, Y", strtotime($fechaFin)) ?></h3>
                </h3>
            </div>
            <div class="card-body p-0">
                <?php if (!isset($transaccion)) { ?>
                    <table class="table table-sm table-striped table-hover table-bordered">
                        <thead class="thead-light text-center">
                            <th>#</th>
                            <th>Tipo de movimiento</th>
                            <th>Estado</th>
                            <th>Bodega de origen</th>
                            <th>Bodega destino</th>
                            <th>Total fardos</th>
                            <th>Documento inventario</th>
                            <th>Fecha de movimiento</th>
                            <th>Acciones</th>
                        </thead>
                        <tbody>
                            <?php
                            $sum = 0;
                            foreach ($movimientos as $index => $registro) { ?>
                                <tr class="text-center">
                                    <td><?= ($index + 1) ?></td>
                                    <td><?php
                                        if ($registro->TipoMovimiento == "D") {
                                            echo Html::tag('span', "Despacho", ['class' => 'badge bg-primary']);
                                        } else if ($registro->TipoMovimiento == "T") {
                                            echo Html::tag('span', "Traslado", ['class' => 'badge bg-primary']);
                                        }
                                        ?>
                                    </td>
                                    <td><?php
                                        if ($registro->Estado == "PROCESO") {
                                            echo Html::tag('span', "En proceso", ['class' => 'badge bg-warning']);
                                        } else if ($registro->Estado == "FINALIZADO") {
                                            echo Html::tag('span', "Finalizado", ['class' => 'badge bg-primary']);
                                        }
                                        ?>
                                    </td>
                                    <td><?= Html::tag('span', $registro->origen, ['class' => 'badge bg-danger']); ?></td>
                                    <td><?= Html::tag('span', $registro->destino, ['class' => 'badge bg-success']); ?></td>
                                    <td><b><?= count($registro->detalleMovimiento) ?></b></td>
                                    <td><?= $registro->Documento_inv == NULL ? 'No definido' : $registro->Documento_inv ?></td>
                                    <td><?= $registro->Fecha ?></td>
                                    <td>
                                        <?php
                                        echo Html::a(
                                            '<span class="fas fa-eye"></span>',
                                            [
                                                'view-traslados',
                                                'IdMovimiento' => $registro->IdMovimiento,
                                            ],
                                        );
                                        ?>
                                    </td>
                                </tr>
                            <?php
                                $sum += count($registro->detalleMovimiento);
                            } ?>
                            <tr>
                                <td colspan="5" class="text-right"><b> Total </b></td>
                                <td class="text-right"><?= $sum ?></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="card-footer justify-content-between">
                        <?= Html::button('<i class="fa fa-image"></i> Generar reporte en imagen', [
                            'class' => 'btn btn-outline-info', 'id' => 'imprimir',
                        ]) ?>
                        <?= Html::a(
                            '<i class="fa fa-file-excel"></i> Generar reporte en excel',
                            [
                                'generar-reporte-excel-movimientos',
                                'fechaInicio' => $fechaInicio,
                                'fechaFin' => $fechaFin,
                                'tipo' => $tipo
                            ],
                            [
                                'class' => 'btn btn-outline-success'
                            ]
                        ) ?>
                    </div>
                <?php } else {
                    echo $transaccion;
                } ?>
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