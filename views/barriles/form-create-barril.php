<?php

use kartik\number\NumberControl;
use kartik\widgets\ActiveForm;
use kartik\widgets\DatePicker;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Crear registro';
$this->params['breadcrumbs'][] = ['label' => 'Listado', 'url' => ['index-barriles', 'condicionImprimir' => '']];
$this->params['breadcrumbs'][] = $this->title;


?>
<div class="card card-dark bg-light rounded shadow-sm">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-database"></i> &nbsp;Asignar codigos a mesas</h3>
    </div>
    <?php $form = ActiveForm::begin(['type' => ActiveForm::TYPE_HORIZONTAL]); ?>
    <div class="card-body">
        <form role="form">
            <div class="row">
                <div class="col-md-6">
                    <?= Html::activeLabel($barril, 'bodega', ['class' => 'control-label']) ?>
                    <?= $form->field($barril, 'bodega', ['showLabels' => false])->textInput([
                        'value' => 'SM00 - Bodega Principal',
                        'readonly' => true
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= Html::activeLabel($barril, 'mesa_asignacion', ['class' => 'control-label']) ?>
                    <?= $form->field($barril, 'mesa_asignacion', ['showLabels' => false])->widget(Select2::class, [
                        'data' => ArrayHelper::map([
                            ['numero' => 1 . " - Mesa de clasificacion"],
                            ['numero' => 2 . " - Mesa de clasificacion"],
                            ['numero' => 3 . " - Mesa de clasificacion"],
                            ['numero' => 4 . " - Mesa de clasificacion"],
                            ['numero' => 5 . " - Mesa de clasificacion"]
                        ], "numero", "numero"),                'language' => 'es',
                        'options' => ['placeholder' => '- Seleccionar mesa -'],
                        'pluginOptions' => ['allowClear' => true,],
                        'disabled' => $detalle,
                    ]); ?>
                </div>
                <div class="col-md-12">
                    <?= Html::activeLabel($barril, 'codigo_barra', ['class' => 'control-label']) ?>
                    <?= $form->field($barril, 'codigo_barra', ['showLabels' => false])->textarea(
                        [
                            'maxlength' => true,
                            'disabled' => $detalle,
                            'rows' => 10
                        ]
                    ) ?>
                </div>
                <div class="col-md-12">
                    <?= Html::submitButton('<i class="fas fa-arrow-circle-right"></i> Verificar', ['class' => 'btn btn-primary btn-block', 'disabled' => $detalle]) ?>
                </div>
            </div>
        </form>
        <?php
        ActiveForm::end();
        ?>
    </div>
</div>
<?php
if ($detalle) { ?>
    <table class="table table-bordered table-hover text-center" id="tabla-codigos-barra">
        <thead class="thead-dark">
            <th>Codigo de barra</th>
            <th>Articulo</th>
            <th>Clasificacion</th>
            <th>Libras</th>
        </thead>
        <tbody>
            <?php
            $totalLibras = 0;
            foreach ($registros as $registro) {
                $registro = explode(", ", $registro);
            ?>
                <tr>
                    <td><?= $registro[0] ?></td>
                    <td><?= $registro[1]  . ' - ' . $registro[2] ?></td>
                    <td><?= $registro[3] ?></td>
                    <td id="libras" data-libras="<?= $registro[4] ?>"><?= $registro[4] . 'lb.' ?></td>

                </tr>
            <?php
                $totalLibras += $registro[4];
            } ?>
            <tr class="bg-dark">
                <td colspan="3" class="text-right">Total</td>
                <td><?= $totalLibras ?></td>
            </tr>
        </tbody>
    </table>
    <div class="row">
        <div class="col-md-12 d-flex justify-content-center my-2">
            <?php
            $form = ActiveForm::begin([
                'action' => Url::to([
                    '/barriles/create-detalle',
                    'registros' => $registrosJson,
                    'totalLibrasFardos' => $totalLibras,
                    'totalCostoFardos' => $totalCosto,
                    'numeroMesa' => $numeroMesa,
                ]),
                'type' => ActiveForm::TYPE_HORIZONTAL,
                'method' => 'POST',
            ]);
            ?>
            <button class=" btn btn-primary" type="submit">Asignar</button>
            <?php ActiveForm::end(); ?>

        </div>
    </div>
<?php } ?>