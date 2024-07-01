<?php
Yii::$app->language = 'es_ES';


use app\models\RegistroModel;
use app\models\TransaccionModel;
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;
use kartik\export\ExportMenu;
use kartik\widgets\DatePicker;
use kartik\widgets\Select2;
use yii\bootstrap4\Modal;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\OsigSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Listado de producción';
$this->params['breadcrumbs'][] = $this->title;

?>
<?php
Modal::begin([
    'options' => [
        'tabindex' => false,
    ],
    'headerOptions' => ['class' => 'bg-primary'],
    'title' => 'Informacion de reporte',
    'id' => 'create-modal',
    'size' => 'modal-xl',
    'class' => 'bg-primary',
    'scrollable' => true,
]);
echo "<div id='createModalContent'></div>";
Modal::end();
?>
<div class="row">
    <div class="col-md-4">
        <div class="card card-danger">
            <div class="card-header">
                <h5><i class="fas fa-file"></i> Finalizar producción (Posible varias veces al dia)</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- left column -->
                    <div class="col-md-8">
                        <label for="">Seleccione la fecha de producción: </label>
                        <?= DatePicker::widget([
                            'name' => 'fecha-filtro-finalizacion',
                            'id' => 'fecha-filtro-finalizacion',
                            'value' => date("Y-m-d"),
                            'options' => [
                                'placeholder' => 'Seleccionar fecha',
                            ],
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' =>
                                'yyyy-m-dd',
                                'todayHighlight' => true,
                                'endDate' => "1d",
                            ],
                        ]); ?>
                    </div>
                    <div class="col-md-4 mt-2">
                        <br>
                        <?= Html::button(
                            '<i class="fas fa-exclamation-triangle "></i> Finalizar',
                            [
                                'value' => Url::to('index.php?r=detalle-registro/previsualizar-finalizar'),
                                'class' => 'btn btn-warning',
                                'data-pjax' => 0,
                                'id' => 'modalButton',
                            ]
                        ) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-warning">
            <div class="card-header">
                <h5><i class="fas fa-file"></i> Finalizar dia contable (SOLO UNA VEZ POR DIA)</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <label for="">Seleccione el dia a finalizar: </label>
                        <?= DatePicker::widget([
                            'name' => 'fecha-finalizacion-diaria',
                            'id' => 'fecha-finalizacion-diaria',
                            'value' => date("Y-m-d"),
                            'options' => [
                                'placeholder' => 'Seleccionar fecha',
                            ],
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' =>
                                'yyyy-m-dd',
                                'todayHighlight' => true,
                                //'endDate' => "0d",
                                //'startDate' => "-7d"
                            ],
                        ]); ?>
                    </div>
                    <div class="col-md-4 mt-2">
                        <br>
                        <?= Html::a('<i class="fas fa-exclamation-triangle "></i> Finalizar', ['finalizar-dia-produccion'], [
                            'class' => 'btn btn-success',
                            'data-pjax' => 0,
                            'id' => 'finalizar-dia-produccion',
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-success">
            <div class="card-header">
                <h5><i class="fas fa-file"></i> Finalizar costo (SOLO UNA VEZ POR DIA)</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <label for="">Seleccione el dia a finalizar: </label>
                        <?= DatePicker::widget([
                            'name' => 'fecha-finalizacion-costo',
                            'id' => 'fecha-finalizacion-costo',
                            'value' => date("Y-m-d"),
                            'options' => [
                                'placeholder' => 'Seleccionar fecha',
                            ],
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' =>
                                'yyyy-m-dd',
                                'todayHighlight' => true,
                            ],
                        ]); ?>
                    </div>
                    <div class="col-md-4 mt-2">
                        <br>
                        <?= Html::a('<i class="fas fa-exclamation-triangle "></i> Finalizar', ['finalizar-dia-produccion'], [
                            'class' => 'btn btn-danger',
                            'data-pjax' => 0,
                            'id' => 'finalizar-costo-produccion',
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <div class="float-right">
                    <div class="summary">Mostrando <b><?= count($dataProvider) ?></b> elementos.</div>
                </div>
                <div class="m-0">
                    <h5><i class="fas fa-briefcase"></i> &nbsp;Registros de producción dia <?= date("F j, Y") ?></h5>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="float-right p-2">
                    <?php
                    echo Html::a('<i class="fas fa-plus"></i> Agregar', ['create-detalle'], [
                        'class' => 'btn btn-success',
                        'data-pjax' => 0,
                    ]);
                    echo Html::a('<i class="fas fa-redo"></i>', ['index'], [
                        'class' => 'btn btn-outline-warning',
                        'data-pjax' => 1,
                        'id' => 'redo-index-prod'
                    ]);
                    ?>
                </div>
                <table class="table table-hover table-bordered">
                    <thead class="thead-light text-center">
                        <th>#</th>
                        <th>Codigo barra</th>
                        <th>Articulo</th>
                        <th>Descripcion</th>
                        <th>Estado</th>
                        <th>Libras</th>
                        <th>Fecha de creacion</th>
                        <th>Usuario creacion</th>
                        <th>Mesa de origen</th>
                        <th>Empresa</th>
                        <th>Acciones</th>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($dataProvider as $index => $registro) { ?>
                            <tr class="text-center">
                                <td><?= ($index + 1) ?></td>
                                <td><?= Html::tag('span', $registro->CodigoBarra, ['class' => 'badge bg-light']); ?></td>
                                <td><?= Html::tag('span', $registro->Articulo, ['class' => 'badge bg-green']); ?></td>
                                <td width="10%"><?= Html::tag('span', $registro->Descripcion . " - " . $registro->Clasificacion, ['class' => 'badge bg-info']);  ?></td>
                                <td><?php
                                    if ($registro->Estado == "PROCESO") {
                                        echo Html::tag('span', "En proceso", ['class' => 'badge bg-warning']);
                                    } else if ($registro->Estado == "FINALIZADO") {
                                        echo Html::tag('span', "Finalizado", ['class' => 'badge bg-primary']);
                                    } else if ($registro->Estado == "ELIMINADO") {
                                        echo Html::tag('span', "Eliminado", ['class' => 'badge bg-danger']);
                                    }
                                    ?></td>
                                <td><?= Html::tag('span', $registro->Libras . "Lbs.", ['class' => 'badge bg-purple']); ?></td>
                                <td><?= $registro->CreateDate ?></td>
                                <td><?= $registro->UsuarioCreacion ?></td>
                                <td><?= $registro->MesaOrigen != '' ? $registro->MesaOrigen : 'No definido'; ?></td>
                                <td><?= $registro->EmpresaDestino ?></td>
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
                                                'ruta' => 'index'
                                            ],
                                            [
                                                'id' => 'delete-registro-prod',

                                            ]
                                        );
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    let borrarProd = document.querySelectorAll("#delete-registro-prod")
    let recargarPagina = document.getElementById('redo-index-prod')

    borrarProd.forEach((elemento) => {
        elemento.addEventListener("click", (e) => {
            e.preventDefault()
            Swal.fire({
                title: 'Estas seguro de eliminar este registro?',
                showCancelButton: true,
                icon: 'error',
                confirmButtonText: 'Continuar',
                cancelButtonText: `Cancelar`,
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        method: "GET",
                        url: `${elemento.href}`,
                        success: function() {
                            recargarPagina.click
                        }
                    })
                }
            })
        })
    })
</script>
<script>
    let botonFinalizar = document.getElementById('modalButton')
    let rutaFinalizar = botonFinalizar.value
    let fechaProduccion = document.getElementById('fecha-filtro-finalizacion');
    botonFinalizar.value += `&fecha=${fechaProduccion.value}`

    $('#fecha-filtro-finalizacion').on('change', function(e) {
        botonFinalizar.value = ''
        botonFinalizar.value = `${rutaFinalizar}&fecha=${fechaProduccion.value}`
    })

    let botonFinalizarDia = document.getElementById('finalizar-dia-produccion')
    let rutaFinalizarDia = botonFinalizarDia.href
    let fechaProduccionFinal = document.getElementById('fecha-finalizacion-diaria');
    botonFinalizarDia.href += `&fecha=${fechaProduccionFinal.value}`

    $('#fecha-finalizacion-diaria').on('change', function(e) {
        botonFinalizarDia.href = ''
        botonFinalizarDia.href = `${rutaFinalizarDia}&fecha=${fechaProduccionFinal.value}`
    })

    botonFinalizarDia.addEventListener('click', (e) => {
        e.preventDefault()
        Swal.fire({
            title: 'Estas a punto de finalizar produccion, deseas continuar?',
            showCancelButton: true,
            icon: 'error',
            confirmButtonText: 'Continuar',
            cancelButtonText: `Cancelar`,
        }).then((result) => {
            if (result.isConfirmed) {
                location.href = botonFinalizarDia.href
            }
        })
    })

    let botonFinalizarCosto = document.getElementById('finalizar-costo-produccion')
    let rutaFinalizarCosto = botonFinalizarCosto.href
    let fechaFinalCosto = document.getElementById('fecha-finalizacion-costo');
    botonFinalizarCosto.href += `&fecha=${fechaFinalCosto.value}`

    $('#fecha-finalizacion-costo').on('change', function(e) {
        botonFinalizarCosto.href = ''
        botonFinalizarCosto.href = `${rutaFinalizarCosto}&fecha=${fechaFinalCosto.value}`
    })

    botonFinalizarCosto.addEventListener('click', (e) => {
        e.preventDefault()
        Swal.fire({
            title: 'Estas a punto de finalizar el costo, deseas continuar?',
            showCancelButton: true,
            icon: 'error',
            confirmButtonText: 'Continuar',
            cancelButtonText: `Cancelar`,
        }).then((result) => {
            if (result.isConfirmed) {
                location.href = botonFinalizarCosto.href
            }
        })
    })
</script>