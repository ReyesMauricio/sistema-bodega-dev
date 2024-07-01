<?php
use kartik\widgets\ActiveForm;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = 'Editar Caja';
$this->params['breadcrumbs'][] = ['label' => 'Listado', 'url' => ['index-lista-cajas']];
$this->params['breadcrumbs'][] = $this->title;

$totalCosto = 0;
$totalLibras = 0;
    foreach($dataProvider->models as $data)
    {
        $totalLibras += $data->registro->Libras;
        $totalCosto += $data->registro->Costo;
    }

// Añadir CSS personalizado
$this->registerCss("
    /* Ajustar padding de las celdas de la tabla */
    .kv-grid-table th, .kv-grid-table td {
        padding: 1px 8px !important;
    }
    
    /* Ajustar el ancho y estilo del encabezado */
    .kv-grid-table th {
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
    }
    
    /* Ajustar el encabezado del card */
    .card-header {
        padding: 10px 15px !important;
    }
");
?>
<div class="card card-dark bg-white">
    <div class="card-header d-flex justify-content-between">
        <h2 class="card-title"><i class="fas fa-box"></i> &nbsp;CODIGO: <?= $codigoBarra; ?></h2>
        <h2 class="card-title text-center d-flex justify-content-center flex-grow-1">&nbsp;Articulo: <?= $Articulo; ?>&nbsp;- <?= $Descripcion; ?></h2>
        <?php if (!empty($uuid)): ?>
            <span class="ml-auto"><?= $uuid; ?></span>
        <?php else: ?>
            <span class="ml-auto">UUID NO ENCONTRADO</span>
        <?php endif; ?>
    </div>

    <?php $form = ActiveForm::begin(['type' => ActiveForm::TYPE_HORIZONTAL]); ?>
    <?php if (!empty($codigoBarra)): ?>
        <?= Html::hiddenInput('codigoBarra', $codigoBarra, ['id' => 'codigoBarra']); ?>
        <?= Html::hiddenInput('totalLibras', $totalLibras, ['id' => 'libras']); ?>
        <?= Html::hiddenInput('totalCosto', $totalCosto, ['id' => 'costo']); ?>
        <?= Html::hiddenInput('Articulo', $Articulo, ['id' => 'articulo']); ?>
        <?= Html::hiddenInput('uuid', $uuid, ['id' => 'uuid']); ?>
    <?php else: ?>
        <label class="ml-auto">Error</label>
    <?php endif; ?>
    <div class="card-body">
        <div class="form-group">
            <label for="form-control">Ingrese los códigos de barra: BARRILES/PRODUCCIÓN</label>
            <?= $form->field($caja, 'codigo_barra', ['showLabels' => false])->textarea([
                'maxlength' => true,
                'rows' => 3,
                'class' => 'form-control',
                'placeholder' => 'Ingrese los códigos de barra aquí...',
            ]) ?>
        </div>
        <div class="row mt-4">
            <div class="col-md-6">
            <?= Html::submitButton('<i class="fas fa-plus"></i> Agregar', [
                'id' => 'btn-agregar', // Asignamos el id 'btn-agregar' al botón
                'class' => 'btn btn-success btn-block',
                'disabled' => !empty($rec) ? 'disabled' : null,
            ]) ?>
            </div>
            <div class="col-md-6">
            <?= Html::button('<i class="fas fa-check-circle"></i> Finalizar Transacción', ['id' => 'ajaxButton', 'class' => 'btn btn-danger btn-block', 'disabled' => !empty($rec) ? 'disabled' : null]) ?>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="tbl-cajas-index">
                <?php Pjax::begin(['id' => 'cajas-grid', 'timeout' => 5000]); ?>
                <?php
                    $gridColumns = [
                        [
                            'class' => 'kartik\grid\SerialColumn',
                            'contentOptions' => ['class' => 'kartik-sheet-style'],
                            'width' => '30px',
                            'header' => '#',
                            'headerOptions' => ['class' => 'kartik-sheet-style'],
                            'footer' => 'TOTALES'
                        ],
                        [
                            'class' => 'kartik\grid\DataColumn',
                            'attribute' => 'CodigoBarra',
                            'width' => '120px',
                            'hAlign' => 'center',
                            'format' => 'raw',
                            'value' => function ($model, $key, $index, $widget) {
                                return Html::a($model->CodigoBarra);
                            },
                            'headerOptions' => ['style' => 'padding: 4px;'],
                            'contentOptions' => ['style' => 'padding: 4px;'],
                            'filter' => false
                        ],
                        [
                            'class' => 'kartik\grid\DataColumn',
                            'attribute' => 'libras',
                            'width' => '90px',
                            'hAlign' => 'center',
                            'format' => 'raw',
                            'value' => function ($model, $key, $index, $widget) {
                                return $model->registro->Libras .' Lbs';
                            },
                            'headerOptions' => ['style' => 'padding: 4px;'],
                            'contentOptions' => ['style' => 'padding: 4px;'],
                            'footer' => $totalLibras. ' Lbs'
                        ],
                        [
                            'class' => 'kartik\grid\ActionColumn',
                            'header' => 'Acciones',
                            'template' => '{delete}',
                            'buttons' => [
                                'delete' => function ($url, $model, $key) {
                                    return Html::a('<i class="fas fa-trash-alt fa-2xs"></i>', $url, [
                                        'id' => 'eliminar-barril',
                                        'title' => Yii::t('app', 'Eliminar'),
                                        'data-pjax' => '0',
                                        'class' => 'btn btn-sm btn-danger eliminar-btn',
                                    ]);
                                },
                            ],
                            'visible' => empty($rec), 
                            'urlCreator' => function ($action, $model, $key, $index) use ($codigoBarra, $totalLibras,$totalCosto) {
                                $librasTotales = $totalLibras - $model->registro->Libras;
                                $costoTotal = $totalCosto - $model->registro->Costo;
                                if ($action === 'delete') {
                                    $params = [
                                        'barriles/eliminar-barril-caja',
                                        'codigoBarra' => $model->CodigoBarra,
                                        'uuid' => $model->NumeroDocumento,
                                        'libras' => $librasTotales,
                                        'costo' => $costoTotal,
                                        'codigoBarraCaja' => $codigoBarra,
                                    ];
                                    return Url::to($params);
                                }
                            return '#';
                            },
                            'headerOptions' => ['style' => 'width:50px;'], // Ajustar el ancho de la columna
                            'contentOptions' => ['style' => 'text-align: center;'],
                        ],
                    ];

                    echo GridView::widget([
                        'id' => 'kv-cajas',
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'columns' => $gridColumns,
                        'showFooter' => true, // Muestra el pie de la tabla
                        'containerOptions' => ['style' => 'overflow: auto'], // only set when $responsive = false
                        'headerRowOptions' => ['class' => 'kartik-sheet-style', 'style' => 'padding: 4px;'],
                        'filterRowOptions' => ['class' => 'kartik-sheet-style', 'style' => 'padding: 4px;'],
                        'pjax' => true, // pjax is set to always true for this demo
                        //'showPageSummary'=>$pageSummary,
                        'panel' => [
                            'type' => 'dark',
                            'heading' => 'Listado de barriles',
                        ],
                        'persistResize' => false,
                    ]);
                ?>
                <?php Pjax::end(); ?>
            </div>
        </div>
    </div>
</div>

<?php
    // Ruta a la acción de Yii2 que manejará la solicitud AJAX
    $url = Yii::$app->urlManager->createUrl(['barriles/finalizar-transaccion-cajas']);
    $urlIndex = Yii::$app->urlManager->createUrl(['barriles/index-lista-cajas']);
    $urlEliminarCaja = Yii::$app->urlManager->createUrl(['barriles/eliminar-caja']);
?>
<script>
$(document).ready(function() {

    //Apartado que asegura que no se encuentren filas dentro del gridview exceptuando el mensaje por 
    //defecto del gridview
    var rowCount = $('#kv-cajas tbody tr').not(':has(td:contains("No results found"))').length;
    console.log(rowCount);
    if (rowCount === 0) {
        $('#ajaxButton').prop('disabled', true);
    }

    $('#ajaxButton').on('click', function() {
        Swal.fire({
            title: '¿Estás seguro?',
            text: '¿Deseas finalizar la transacción?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, finalizar',
            cancelButtonText: 'No, cancelar'
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

                var gridData = [];
                $('#kv-cajas').find('tr[data-key]').each(function(index, row){
                    var rowData = {};
                    var codigoBarra = $(this).find('td[data-col-seq="1"]').text();
                    rowData['CodigoBarra'] = codigoBarra;
                    gridData.push(rowData);
                });

                $.ajax({
                    url: '<?php echo $url?>',
                    type: 'POST',
                    data: {
                        codigoBarra: $('#codigoBarra').val(),
                        articulo: $('#articulo').val(),
                        libras: $('#libras').val(),
                        costo: $('#costo').val(),
                        consecutivo: $('#consecutivo').val(),
                        uuid: $('#uuid').val(),
                        gridData: JSON.stringify(gridData),
                        _csrf: yii.getCsrfToken() // Incluir el token CSRF
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Éxito',
                                text: response.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                // Optional: Redirigir o actualizar la página después de cerrar el SweetAlert
                                window.location.href = '<?php echo $urlIndex?>';
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: response.message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            title: 'Error',
                            text: 'Ocurrió un error: ' + error,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
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

        if (rowCount === 1) {
            // Mostrar SweetAlert para confirmar la eliminación
            Swal.fire({
                title: '¿Eliminar Transaccion?',
                text: '¿Seguro que quieres eliminar la caja y el barril?',
                icon: 'warning',
                showCancelButton: true,
                showDenyButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                denyButtonColor: '#ff9f33',
                denyButtonText: 'Eliminar Barril',
                confirmButtonText: 'Sí, eliminar todo',
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
                        url: '<?php echo $urlEliminarCaja?>',
                        data: {
                            codigoBarra: $('#codigoBarra').val(),
                            uuid: $('#uuid').val(),
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
                                //$.pjax.reload({container: '#cajas-grid'}); // Ajustar el selector según tu Pjax
                                window.location.href = '<?php echo $urlIndex?>';
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
                } else if (result.isDenied) {
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
                                location.reload();//Recarga la pagina completa
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
                else{
                    Swal.fire({
                        title: 'Cancelado',
                        text: 'Se ha cancelado la eliminacion.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }else{
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
        }
    });
});

</script>
