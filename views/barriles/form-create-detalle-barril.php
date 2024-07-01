<?php

use kartik\number\NumberControl;
use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;

$this->title = 'Asignar barril';
$this->params['breadcrumbs'][] = ['label' => 'Listado', 'url' => ['index-verificar-transaccion']];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="card card-dark bg-light rounded shadow-sm">
    <div class="card-header d-flex justify-content-between">
        <h3 class="card-title"><i class="fas fa-database"></i> &nbsp;Crear barril</h3>
        <?php if (!empty($uuid)): ?>
            <span class="ml-auto"><?= $uuid; ?></span>
        <?php else: ?>
            <span class="ml-auto">UUID NO ENCONTRADO</span>
        <?php endif; ?>
    </div>
    <?php $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'id' => 'form-create-detalle-barril'
    ]); ?>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <?= Html::activeLabel($barrilDetalle, 'articulo', ['class' => 'control-label']) ?>
                <?= $form->field($barrilDetalle, 'articulo', ['showLabels' => false])->widget(Select2::class, [
                    'data' => ArrayHelper::map($articulos, 'ARTICULODESCRIPIONCLASIFICACION', 'ARTICULODESCRIPCION'),
                    'language' => 'es',
                    'options' => ['placeholder' => '- Seleccionar artículo -'],
                    'pluginOptions' => ['allowClear' => true],
                ])->label(false); ?>
            </div>
            <div class="col-md-6">
                <?= Html::activeLabel($barrilDetalle, 'libras', ['class' => 'control-label']) ?>
                <?= $form->field($barrilDetalle, 'libras', ['showLabels' => false])->widget(
                    NumberControl::class,
                    [
                        'maskedInputOptions' => ['rightAlign' => false]
                    ]
                )->label(false); ?>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-6">
                <label for="totalLibrasBarriles">Libras BARRILES</label>
                <input type="text" id="totalLibrasBarriles" name="totalLibrasBarriles" class="form-control" value="<?= $totalLibrasBarriles ?>" readonly>
            </div>
            <div class="col-md-6">
                <label for="totalLibras">Total de libras de FARDO/PACA</label>
                <input type="text" id="totalLibras" name="totalLibras" class="form-control" value="<?= $totalLibras ?>" readonly>
            </div>
            <div class="col-md-12">
                <?= Html::hiddenInput('totalCosto', $totalCosto, ['id' => 'totalCosto']); ?>
                <?= Html::hiddenInput('uuid', $uuid, ['id' => 'uuid']); ?>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <div class="row">
            <?php if(!empty($documentoInv)){ ?>
                <div class="col-12 alert alert-danger text-center" role="alert">
                    TRANSACCIÓN TERMINADA.
                </div>
            <?php }?>
            <div class="col-md-6 mt-2">
                <?php if(empty($documentoInv)){ ?>
                <?= Html::submitButton('<i class="fas fa-plus-circle"></i> Agregar barril', ['class' => 'btn btn-primary btn-block', 'id' => 'create-detalle-barril']) ?>
                <?php }?>
            </div>
            <div class="col-md-6 mt-2">
                <?php if(empty($documentoInv)){ ?>
                    <?= Html::submitButton('<i class="fas fa-check-circle"></i> Finalizar Transacción', ['class' => 'btn btn-danger btn-block', 'id' => 'finalizar-transaccion']) ?>
                <?php }?>
                </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

    <div class="row mt-4 justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header bg-dark text-white">
                    <h5>Listado de Barriles</h5>
                </div>
                <div class="card-body p-0">
                    <?php Pjax::begin(['id' => 'cajas-grid', 'timeout' => 5000]); ?>

                    <?= GridView::widget([
                        'id' => 'kv-cajas',
                        'dataProvider' => $dataProvider,
                        //'filterModel' => $searchModel,
                        'layout' => "{items}\n{summary}\n<div class='text-center'>{pager}</div>", // Layout personalizado con pager centrado
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],

                            [
                                'attribute' => 'CodigoBarra',
                                'label' => 'Código de Barras',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    return $model->CodigoBarra;
                                },
                                'filter' => false
                            ],
                            [
                                'attribute' => 'Articulo',
                            ],
                            
                            [
                                'attribute' => 'Descripcion',
                            ],
                            [
                                'attribute' => 'libras',
                                'label' => 'Libras',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    return $model->Libras . ' Lbs';
                                },
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => 'Acciones',
                                'visible' => empty($documentoInv),
                                'template' => '{delete}',
                                'buttons' => [
                                    'delete' => function ($url, $model) {
                                        return Html::a('<i class="fas fa-trash-alt"></i>', $url, [
                                            'id' => 'eliminar-barril',
                                            'title' => Yii::t('app', 'Eliminar'),
                                            'data-pjax' => '0',
                                            'class' => 'btn btn-sm btn-danger eliminar-btn'
                                        ]);
                                    },
                                ],
                                'urlCreator' => function ($action, $model) {
                                    if ($action === 'delete') {
                                        return Url::to(['eliminar-barril-fardo', 'codigoBarra' => $model->CodigoBarra, 'libras' => $model->Libras]);
                                    }
                                },
                                'contentOptions' => ['class' => 'text-center'], // Centra el contenido de las celdas
                            ],
                        ],
                        'showFooter' => false,
                        'headerRowOptions' => ['class' => 'kartik-sheet-style', 'style' => 'padding: 4px;'],
                        'filterRowOptions' => ['class' => 'kartik-sheet-style', 'style' => 'padding: 4px;'],
                        'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'], // Estilo de tabla Bootstrap
                        'pager' => [
                            'options' => ['class' => 'pagination justify-content-center'], // Estilo de paginación centrada
                            'linkContainerOptions' => ['class' => 'page-item'], // Contenedor de enlaces de página
                            'linkOptions' => ['class' => 'page-link'], // Estilo de enlaces de página
                            'disabledListItemSubTagOptions' => ['tag' => 'a', 'class' => 'page-link disabled'], // Estilo de enlaces de página deshabilitados
                            'disabledPageCssClass' => 'disabled', // Clase CSS para páginas deshabilitadas
                        ],
                    ]); ?>

                    <?php Pjax::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

</div>
<style>
    .card {
        margin-top: 20px; /* Espacio superior */
    }
    .table > thead > tr > th,
    .table > tbody > tr > td {
        vertical-align: middle; /* Alinear verticalmente el contenido */
    }
    .eliminar-btn {
        padding: 5px 10px; /* Espaciado interno del botón */
    }
</style>
<?php
    // Ruta a la acción de Yii2 que manejará la solicitud AJAX
    $url = Yii::$app->urlManager->createUrl(['barriles/finalizar-transaccion-barriles']);
    $urlIndex = Yii::$app->urlManager->createUrl(['barriles/index-verificar-transaccion']);
    $urlEliminarBarrilFardo = Yii::$app->urlManager->createUrl(['barriles/eliminar-barril-fardo']);
?>

<script>
    $(document).ready(function(){
        var rowCount = $('#kv-cajas tbody tr').not(':has(td:contains("No results found"))').length;
        console.log(rowCount);
        if (rowCount === 0) {
            $('#finalizar-transaccion').prop('disabled', true);
        }

        $("#finalizar-transaccion").click(function(e){
            e.preventDefault(); // Prevenir el comportamiento predeterminado del botón
            //Mostramos el SWAL
            Swal.fire({
                title: '¿Estás seguro de terminar este costeo?',
                text: 'No podrás deshacer estos valores más adelante, ni agregar más valores al contenedor.',
                showCancelButton: true,
                icon: 'question',
                confirmButtonText: 'Confirmar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Espere por favor',
                        text: 'Procesando transacción...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    // Realizar la solicitud AJAX
                    $.ajax({
                        url: '<?= $url ?>',
                        type: 'POST',
                        data: {
                            totalLibras: $('#totalLibras').val(),
                            totalCosto: $('#totalCosto').val(),
                            uuid: $('#uuid').val(),
                        },
                        success: function(response){
                            console.log(response);

                            if (response.success) {
                                // Mostrar un SweetAlert de éxito
                                Swal.fire({
                                    title: 'Éxito',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                });

                                // Recargar el GridView después de eliminar el elemento
                                window.location.href = '<?php echo $urlIndex?>';
                            } else {
                                // Mostrar un mensaje de error si la eliminación falló
                                Swal.fire({
                                    title: 'Error',
                                    text: response.message,
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        error: function(xhr, status, error){
                            // Aquí puedes manejar los errores si es necesario
                            console.error(error);
                            Swal.fire('¡Error!', 'Ha ocurrido un error al intentar terminar el costeo', 'error');
                        }
                    });
                }
            });
        });

        $(document).on('click', '.eliminar-btn', function(e) {
            e.preventDefault(); // Prevenir el comportamiento predeterminado del enlace

            var url = $(this).attr('href'); // Obtener la URL de la solicitud AJAX

            var rowCount = $('#kv-cajas tbody tr').length;
            console.log(rowCount)

            // Mostrar SweetAlert para confirmar la eliminación
            Swal.fire({
                title: '¿Estás seguro?',
                text: '¿Seguro que quieres eliminar este elemento?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Espere por favor',
                        text: 'Procesando transacción...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    // Realizar la solicitud AJAX para eliminar el elemento
                    $.ajax({
                        url: url,
                        data: {
                            uuid: $('#uuid').val(),
                            totalLibrasBarriles: $('#totalLibrasBarriles').val(),
                            _csrf: yii.getCsrfToken() // Incluir el token CSRF
                        },
                        type: 'POST',
                        success: function(response) {
                            if (response.success) {
                                // Mostrar un SweetAlert de éxito
                                Swal.fire({
                                    title: 'Éxito',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                });

                                // Recargar el GridView después de eliminar el elemento
                                $.pjax.reload({container: '#cajas-grid'}); // Ajustar el selector según tu Pjax
                                $('#totalLibrasBarriles').val(response.totalLibrasBarriles);
                            } else {
                                // Mostrar un mensaje de error si la eliminación falló
                                Swal.fire({
                                    title: 'Error',
                                    text: 'Hubo un error al eliminar el elemento.',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        error: function() {
                            // Mostrar un mensaje de error si hubo un problema con la solicitud AJAX
                            Swal.fire({
                                title: 'Error',
                                text: 'Hubo un problema al procesar la solicitud.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
            });
            
        });
    });
</script>
