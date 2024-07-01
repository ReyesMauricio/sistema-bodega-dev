<?php

use yii\helpers\Html;
use yii\helpers\Url;

Yii::$app->formatter->locale = 'en-US';
?>

<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 id="asignacion-report" class="card-title"><i class="fas fa-briefcase"></i> &nbsp;Produccion por persona, <?= date("F j, Y", strtotime($fechaInicio)) . " - " . date("F j, Y", strtotime($fechaFin)) ?></h3>
                <h3 id="fecha-reporte-produccion" hidden><?= $fechaInicio . " - " . $fechaFin ?></h3>
            </div>
            <div class="card-body">
                <table class="table table-sm table-striped table-hover table-bordered">
                    <thead>
                        <th>Nombre</th>
                        <th>Unidades</th>
                        <th>Libras</th>
                        <th>Fardos creados</th>
                    </thead>
                    <tbody>
                        <?php
                        $sum = 0;
                        foreach ($produccion as $persona) { ?>
                            <tr>
                                <td id="persona-report"><?= $persona['nombre'] ?></td>
                                <td><?= $persona['unidades'] ?> unidades</td>
                                <td><?= $persona['libras'] ?> Lbs.</td>
                                <th><?= $persona['fardos'] ?></th>
                            </tr>
                        <?php
                            $sum += $persona['fardos'];
                        } ?>
                        <tr> <td><?= $sum ?></td></tr>
                    </tbody>

                </table>
            </div>
            <div class="card-footer">
                <?= Html::button('<i class="fa fa-image"></i> Generar reporte en image', [
                    'class' => 'btn btn-warning', 'id' => 'imprimir',
                ]) ?>
                <?= Html::a(
                    '<i class="fa fa-file-excel"></i> Descargar reporte en excel',
                    [
                        'reportes/crear-reporte-produccion-persona-excel',
                        'fechaInicio' => $fechaInicio,
                        'fechaFin' => $fechaFin
                    ],
                    [
                        'id' => 'boton-generar-reporte',
                        'class' => 'btn btn-success'
                    ]
                ) ?>
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
                let fechaProdPersona = document.getElementById('fecha-reporte-produccion')
                const a = document.createElement('a')
                a.setAttribute('download', `Produccion por persona: ${fechaProdPersona.innerText}`)
                a.setAttribute('href', image)
                a.click()
                canvas.remove()
            })
    }

    var btn = document.querySelector('#imprimir')
    btn.addEventListener('click', capture)
</script>