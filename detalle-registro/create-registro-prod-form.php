<?php

use app\models\TipoEmpaqueModel;
use app\models\UsuarioModel;
use kartik\number\NumberControl;
use kartik\widgets\ActiveForm;
use kartik\widgets\DatePicker;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$this->title = 'Crear registro';
$this->params['breadcrumbs'][] = ['label' => 'Listado', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;


?>

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-briefcase"></i> &nbsp;Crear/Actualizar registro</h3>
    </div>
    <?php $form = ActiveForm::begin(['id' => 'detalle-form', 'type' => ActiveForm::TYPE_HORIZONTAL]); ?>
    <div class="card-body">
        <form role="form">
            <div class="row">
                <div class="col-md-4">
                    <?= Html::activeLabel($registro, 'Libras', ['class' => 'control-label']) ?>
                    <?= $form->field($registro, 'Libras',  ['showLabels' => false])->widget(NumberControl::class, [
                        'name' => 'Libras',
                    ]);
                    ?>
                </div>
                <div class="col-md-4">
                    <?= Html::activeLabel($registro, 'Unidades', ['class' => 'control-label']) ?>
                    <?= $form->field($registro, 'Unidades', ['showLabels' => false])->widget(NumberControl::class, [
                        'name' => 'Unidades',
                    ]); ?>
                </div>
                <div class="col-md-4">
                    <?= Html::activeLabel($registro, 'MesaOrigen', ['class' => 'control-label']) ?>
                    <?= $form->field($registro, 'MesaOrigen',  ['showLabels' => false])->widget(Select2::class, [
                        'name' => 'MesaOrigen',
                        'id' => 'MesaOrigen',
                        'data' => ArrayHelper::map([

                            ['numero' => 6, 'titulo' => "6 - Mesa de producción"],
                            ['numero' => 7, 'titulo' => "7 - Mesa de producción"],
                            ['numero' => 8, 'titulo' => "8 - Mesa de producción"],
                            ['numero' => 9, 'titulo' => "9 - Mesa de producción"],
                            ['numero' => 10, 'titulo' => "10 - Mesa de producción"],
                            ['numero' => 11, 'titulo' => "11 - Mesa de producción"],
                            ['numero' => 12, 'titulo' => "12 - Mesa de producción"],
                            ['numero' => 13, 'titulo' => "13 - Mesa de producción"],
                            ['numero' => 14, 'titulo' => "14 - Mesa de producción"],
                            ['numero' => 15, 'titulo' => "15 - Mesa de producción"],
                            ['numero' => 16, 'titulo' => "16 - Mesa de producción"],
                            ['numero' => 17, 'titulo' => "17 - Mesa de producción"],
                            ['numero' => 18, 'titulo' => "18 - Mesa de producción"],
                            ['numero' => 19, 'titulo' => "19 - Mesa de producción"],
                            ['numero' => 20, 'titulo' => "20 - Mesa de producción"],

                        ], "numero", "titulo"),
                        'language' => 'es',
                        'options' => ['placeholder' => '- Seleccionar mesa -'],
                        'pluginOptions' => ['allowClear' => true,],
                    ]);
                    ?>
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
                <div class="col-md-6">
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
                <div class="col-md-6">
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
                    <?= Html::activeLabel($registro, 'Observaciones', ['class' => 'control-label']) ?>
                    <?= $form->field($registro, 'Observaciones', ['showLabels' => false])->textarea(
                        [
                            'maxlength' => true,
                        ]
                    ) ?>
                </div>

                <!--INICIA SEGUNDA PARTE DEL FORMULARIO-->
                <?php if (isset($articulos)) { ?>
                    <div class="col-md-12">
                        <?= Html::activeLabel($registro, 'Clasificacion', ['class' => 'control-label']) ?>
                        <?= $form->field($registro, 'Clasificacion', ['showLabels' => false])->textInput(['maxlength' => true, 'readonly' => true]) ?>
                    </div>
                <?php } else { ?>
                    <div class="col-md-12">
                        <?= Html::activeLabel($registro, 'Clasificacion', ['class' => 'control-label']) ?>
                        <?= $form->field($registro, 'Clasificacion', ['showLabels' => false])->radioButtonGroup(
                            ArrayHelper::map($clasificacion, 'CLASIFICACION_2', 'CLASIFICACION_2'),
                            []
                        ); ?>

                    </div>
                <?php } ?>
                <?php if (isset($articulos)) { ?>
                    <div class="col-md-6">
                        <?= Html::activeLabel($registro, 'Articulo', ['class' => 'control-label']) ?>
                        <?= $form->field($registro, 'Articulo', ['showLabels' => false])->widget(Select2::class, [
                            'data' => ArrayHelper::map($articulos, 'ARTICULO_DESCRIPCION', 'ARTICULO_DESCRIPCION'),
                            'language' => 'es',
                            'id' => 'Articulo',
                            'options' => [
                                'placeholder' => '-- Seleccionar articulo --',
                                'focus' => true,
                            ],
                            'pluginOptions' => ['allowClear' => true],
                        ]); ?>
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
                    <script>
                        var scroll = $(window).scrollTop();
                        var scrollto = scroll + 500;
                        $("html, body").animate({
                            scrollTop: scrollto
                        });
                    </script>
                <?php } ?>
                <!--FIN DE SEGUNDA PARTE FORMULARIO-->
                <?php if (isset($articulos)) { ?>

                    <div class="col-md-12 mt-2">
                        <br>
                        <?= Html::submitButton('Crear registro', ['id' => 'btn-detalle-submit', 'class' => 'btn btn-success']) ?>
                    </div>
                <?php } else { ?>
                    <div class="col-md-12 mt-2">
                        <br>
                        <?= Html::submitButton('Continuar', ['id' => 'btn-detalle-submit', 'class' => 'btn btn-success']) ?>
                    </div>
                <?php } ?>
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