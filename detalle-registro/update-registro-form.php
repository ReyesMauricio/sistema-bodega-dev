<?php

use app\models\TipoEmpaqueModel;
use app\models\UsuarioModel;
use kartik\number\NumberControl;
use kartik\widgets\ActiveForm;
use kartik\widgets\DatePicker;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

Yii::$app->formatter->locale = 'en-US';
$this->title = 'Actualizar registro';
$this->params['breadcrumbs'][] = ['label' => 'Listado', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Detalle', 'url' => ['view', 'codigoBarra' => $registro->CodigoBarra, 'condicionImprimir' => '']];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-briefcase"></i> &nbsp;Actualizar registro</h3>
    </div>
    <?php $form = ActiveForm::begin(['id' => 'detalle-form', 'type' => ActiveForm::TYPE_HORIZONTAL]); ?>
    <div class="card-body">
        <form role="form">
            <div class="row">
                <div class="col-md-4">
                    <?= Html::activeLabel($registro, 'Unidades', ['class' => 'control-label']) ?>
                    <?= $form->field($registro, 'Unidades', ['showLabels' => false])->widget(NumberControl::class, [
                        'name' => 'Unidades',
                    ]); ?>
                </div>
                <div class="col-md-4">
                    <?= Html::activeLabel($registro, 'IdTipoEmpaque', ['class' => 'control-label']) ?>
                    <?= $form->field($registro, 'IdTipoEmpaque', ['showLabels' => false])->widget(Select2::class, [
                        'data' => ArrayHelper::map(TipoEmpaqueModel::find()->where("TipoEmpaque NOT IN ('PACA', 'BARRIL')")->all(), 'IdTipoEmpaque', 'TipoEmpaque'),
                        'language' => 'es',
                        'options' => [
                            'placeholder' => '- Seleccionar empacador -',
                        ],
                        'pluginOptions' => ['allowClear' => true],
                    ]); ?>
                </div>
                <div class="col-md-4">
                    <?= Html::activeLabel($registro, 'FechaCreacion', ['class' => 'control-label']) ?>
                    <?= $form->field($registro, 'FechaCreacion', ['showLabels' => false])->widget(DatePicker::class, [
                        'options' => [
                            'placeholder' => 'Seleccionar fecha',
                            'value' => $registro->FechaCreacion != '' ? $registro->FechaCreacion : date("Y-m-d"),
                        ],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' =>
                            'yyyy-m-dd',
                            'todayHighlight' => true,
                            'endDate' => "+1d",
                        ],
                    ]); ?>
                </div>
                <div class="col-md-12">
                    <?= Html::activeLabel($registro, 'EmpacadoPor', ['class' => 'control-label']) ?>
                    <?= $form->field($registro, 'EmpacadoPor', ['showLabels' => false])->widget(Select2::class, [
                        'data' => ArrayHelper::map(UsuarioModel::find()->where('Empaca = 1')->all(), 'Nombre', 'Nombre'),
                        'language' => 'es',
                        'options' => [
                            'placeholder' => '- Seleccionar empacador -',
                            'multiple' => true,
                        ],
                        'pluginOptions' => ['allowClear' => true],
                    ]); ?>
                </div>
                <div class="col-md-12">
                    <?= Html::activeLabel($registro, 'ProducidoPor', ['class' => 'control-label']) ?>
                    <?= $form->field($registro, 'ProducidoPor', ['showLabels' => false])->widget(Select2::class, [
                        'data' => ArrayHelper::map(UsuarioModel::find()->where('Produce = 1')->all(), 'Nombre', 'Nombre'),
                        'language' => 'es',
                        'options' => [
                            'placeholder' => '- Seleccionar empacador -',
                            'multiple' => true,
                        ],
                        'pluginOptions' => ['allowClear' => true],
                    ]); ?>
                </div>
                <div class="col-md-12">
                    <?= Html::activeLabel($registro, 'Observaciones', ['class' => 'control-label']) ?>
                    <?= $form->field($registro, 'Observaciones', ['showLabels' => false])->textarea(
                        [
                            'maxlength' => true,
                        ]
                    ) ?>
                </div>
                <div class="col-md-6">
                    <?= Html::activeLabel($registro, 'Clasificacion', ['class' => 'control-label']) ?>
                    <?= $form->field($registro, 'Clasificacion', ['showLabels' => false])->radioButtonGroup(
                        ArrayHelper::map($clasificacion, 'CLASIFICACION_2', 'CLASIFICACION_2'),
                        []
                    ); ?>

                </div>
                <div class="col-md-6">
                    <label for="">Articulo actual</label>
                    <br>
                    <input class="form-control" disabled value="<?= $registro->Articulo ?>">
                </div>
                <div class="col-6">
                    <?= Html::activeLabel($registro, 'EmpresaDestino', ['class' => 'control-label']) ?>
                    <?= $form->field($registro, 'EmpresaDestino', ['showLabels' => false])->widget(Select2::class, [
                        'data' => ArrayHelper::map(
                            [
                                ['empresa' => 'cany', 'nombre' => 'CannyShop'],
                                ['empresa' => 'carisma', 'nombre' => 'Carisma'],
                                ['empresa' => 'boutique', 'nombre' => 'La boutique'],
                                ['empresa' => 'nys', 'nombre' => 'New York Store (SM1)'],
                                ['empresa' => 'nyc', 'nombre' => 'New York Center (ST1)'],
                            ],
                            'empresa',
                            'nombre'
                        ),
                        'language' => 'es',
                        'id' => 'EmpresaDestino',
                        'options' => [
                            'placeholder' => '-- Seleccionar EmpresaDestino --',
                            'focus' => true,
                        ],
                        'pluginOptions' => ['allowClear' => true],
                    ]); ?>
                </div>
                <div class="col-md-6">
                    <?= Html::activeLabel($registro, 'Articulo', ['class' => 'control-label']) ?>
                    <?= Select2::widget([
                        'data' => ArrayHelper::map($articulos, 'ARTICULO_DESCRIPCION', 'ARTICULO_DESCRIPCION'),
                        'language' => 'es',
                        'id' => 'Articulo',
                        'name' => 'Articulo',
                        'options' => [
                            'placeholder' => '-- Seleccionar articulo --',
                            'focus' => true,
                        ],
                        'pluginOptions' => ['allowClear' => true],
                    ]); ?>
                </div>
                <div class="col-md-12 mt-2">
                    <br>
                    <?= Html::submitButton('Actualizar registro', ['id' => 'btn-detalle-submit', 'class' => 'btn btn-warning']) ?>
                </div>
            </div>
        </form>
        <?php
        ActiveForm::end();
        ?>
    </div>
</div>

<script>
    $('#registromodel-libras-disp').focus();
</script>