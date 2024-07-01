<?php

use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Listado de articulos por empresa';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-6">
        <?php $form = ActiveForm::begin(['id' => 'detalle-form', 'type' => ActiveForm::TYPE_HORIZONTAL]); ?>
        <label for="empresa">Seleccione la empresa a comparar:</label>
        <?= Select2::widget([
            'name' => 'empresa',
            'id' => 'empresa',
            'data' => ArrayHelper::map([
                ['numero' => 'CANNYSHOP', 'titulo' => "Cannyshop"],
                ['numero' => 'CCARISMA', 'titulo' => "Carisma"],
                ['numero' => 'CEVER', 'titulo' => "Ever"],
                ['numero' => 'CNERY', 'titulo' => "Nery"],
                ['numero' => 'CNYCENTER', 'titulo' => "New York Center"]
            ], "numero", "titulo"),
            'language' => 'es',
            'options' => ['placeholder' => '- Seleccionar empresa -'],
            'pluginOptions' => ['allowClear' => true,],
        ]);
        ?>
    </div>
    <div class="col-md-6 mt-2">
        <br>
        <?= Html::submitButton('Verificar', ['id' => 'btn-detalle-submit', 'class' => 'btn btn-success']) ?>
        <?= Html::a('<i class="fas fa-redo">&nbsp;&nbsp;Reiniciar</i>', ['obtener-articulos-companies'], [
            'class' => 'btn btn-outline-success',
            'data-pjax' => 0,
        ]) ?>
        <?php if (isset($empresa)) { ?>
            <?= Html::a(
                '<i class="fa fa-copy"></i> Copiar articulos',
                Url::to("index.php?r=detalle-registro/copiar-articulos&empresa=$empresa"),
                [
                    'class' => 'btn btn-warning',
                    'id' => 'copiar-articulos',
                ],

            ) ?>
        <?php } ?>
    </div>
    <?php ActiveForm::end(); ?>
    <div class="col-md-6 mt-2">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <div class="float-right">
                    <div class="summary">Mostrando <b><?= count($coninv) ?></b> elementos.</div>
                </div>
                <div class="m-0">
                    <h5><i class="fas fa-info"></i> &nbsp; Articulos registrados en CONINV</h5>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover table-bordered">
                    <thead class="thead-light text-center">
                        <th>#</th>
                        <th>Articulo</th>
                        <th>Descripcion</th>
                        <th>Clasificaciones</th>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($coninv as $index => $registro) { ?>
                            <tr class="text-center">
                                <td><?= ($index + 1) ?></td>
                                <td><?= Html::tag('span', $registro["ARTICULO"], ['class' => 'badge bg-green']); ?></td>
                                <td><?= Html::tag('span', $registro["DESCRIPCION"] . " - " . $registro["CLASIFICACION_2"], ['class' => 'badge bg-info']);  ?></td>
                                <td><?= Html::tag('span', $registro["CLASIFICACION_2"], ['class' => 'badge bg-danger']);  ?></td>
                            </tr>
                        <?php }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6 mt-2">
        <?php if (isset($empresa)) { ?>

            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <div class="float-right">
                        <div class="summary">Mostrando <b><?= count($empresaInfo) ?></b> elementos.</div>
                    </div>
                    <div class="m-0">
                        <h5><i class="fas fa-info"></i> &nbsp; Articulos no registrados en <?= $empresa ?></h5>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover table-bordered">
                        <thead class="thead-light text-center">
                            <th>#</th>
                            <th>Articulo</th>
                            <th>Descripcion</th>
                            <th>Clasificaciones</th>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($empresaInfo as $index => $registro) { ?>
                                <tr class="text-center">
                                    <td><?= ($index + 1) ?></td>
                                    <td><?= Html::tag('span', $registro["ARTICULO"], ['class' => 'badge bg-green']); ?></td>
                                    <td><?= Html::tag('span', $registro["DESCRIPCION"] . " - " . $registro["CLASIFICACION_2"], ['class' => 'badge bg-info']);  ?></td>
                                    <td><?= Html::tag('span', $registro["CLASIFICACION_2"], ['class' => 'badge bg-danger']);  ?></td>
                                </tr>
                            <?php }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php } ?>
    </div>
</div>