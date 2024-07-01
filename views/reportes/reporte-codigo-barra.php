<?php

use app\models\MovimientoModel;
use yii\helpers\Html;
use yii\helpers\Url;

Yii::$app->formatter->locale = 'en-US';
?>

<div class="row">
    <div class="col-md-12">
        <?php if (isset($error)) {
            echo $error;
        } else { ?>
            <div class="card card-primary">
                <div class="card-header d-flex justify-content-between">
                    <h3 id="barcode-report" class="card-title"><i class="fas fa-briefcase"></i>&nbsp;Codigo de barra: <b style="color: #ffd500"> <?= $model->CodigoBarra ?></b></h3>
                    <h3 class="card-title"><i class="fas fa-clock"></i> &nbsp; Fecha de produccion: <b style="color: #ffd500"><?= $model->FechaCreacion ?></b></h3>
                    <h3 class="card-title"><i class="fas fa-question"></i> &nbsp; Tipo de registro: <b style="color: #ffd500"><?= $model->tipoRegistro->TipoRegistro ?> </b></h3>
                    <?php
                    if (count($model->detalleMovimiento) > 0) {
                        $movimiento = MovimientoModel::find()->where(['IdMovimiento' => $model->detalleMovimiento[0]->IdMovimiento])->one();
                    ?>
                        <h3 class="card-title"><i class="fas fa-truck"></i>Tipo de movimiento: <b style="color: #ffd500"><?= $movimiento->TipoMovimiento == 'D' ? 'Despacho' : 'Traslado' ?></b></h3>
                    <?php } ?>
                </div>
                <div class="card-body">


                    <table class="table table-sm table-striped table-hover table-bordered">
                        <tr>
                            <td width="16%"><b>Articulo:</b></td>
                            <td width="34%"><?= $model->Articulo ?></td>
                            <td width="16%"><b>Descripcion:</b></td>
                            <td width="34%"> <?= $model->Descripcion ?></td>
                        </tr>
                        <tr>
                            <td><b>Clasificacion: </b></td>
                            <td><?= $model->Clasificacion ?></td>
                            <td><b>Mesa de origen: </b></td>
                            <td><?= $model->MesaOrigen ?></td>
                        </tr>
                        <tr>
                            <td><b>Bodega de creacion: </b></td>
                            <td>
                                <?php
                                if (count($model->detalleMovimiento) > 0) {
                                    $movimiento = MovimientoModel::find()->where(['IdMovimiento' => $model->detalleMovimiento[0]->IdMovimiento])->one();
                                    echo Html::tag('span', $movimiento->origen, ['class' => 'badge bg-danger']);
                                } else {
                                    $model->BodegaCreacion = $model->BodegaCreacion == 'SM00' ? 'SM00 - Bodega principal' : $model->BodegaCreacion;
                                    echo Html::tag('span', $model->BodegaCreacion, ['class' => 'badge bg-success']);
                                }
                                ?>
                            </td>
                            <td><b>Bodega actual: </b></td>
                            <td>
                                <?php
                                if (count($model->detalleMovimiento) > 0) {
                                    $movimiento = MovimientoModel::find()->where(['IdMovimiento' => $model->detalleMovimiento[0]->IdMovimiento])->one();
                                    echo Html::tag('span', $movimiento->destino, ['class' => 'badge bg-success']);
                                } else {
                                    $model->BodegaActual = $model->BodegaActual == 'SM00' ? 'SM00 - Bodega principal' : $model->BodegaActual;
                                    echo Html::tag('span', $model->BodegaActual, ['class' => 'badge bg-success']);
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td><b>Libras: </b></td>
                            <td><?= $model->Libras ?></td>
                            <td><b>Unidades: </b></td>
                            <td><?= $model->Unidades ?> Unidades</td>
                        </tr>
                        <tr>
                            <td><b>Empacado por: </b></td>
                            <td>
                                <ul>
                                    <?php
                                    $empacadores = explode(",", $model->EmpacadoPor);
                                    foreach ($empacadores as $val) {
                                        echo  "<li>" . $val . "</li>";
                                    }
                                    ?>
                                </ul>
                            </td>
                            <td><b>Producido por: </b></td>
                            <td>
                                <ul>
                                    <?php
                                    $productores = explode(",", $model->ProducidoPor);
                                    foreach ($productores as $val) {
                                        echo  "<li>" . $val . "</li>";
                                    }
                                    ?>
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <td><b>Creado por: </b></td>
                            <td><?= $model->UsuarioCreacion ?></td>
                            <td><b>Estado: </b></td>
                            <td>
                                <?php
                                if ($model->Estado == "PROCESO") {
                                    echo Html::tag('span', "En proceso", ['class' => 'badge bg-warning']);
                                } else if ($model->Estado == "FINALIZADO") {
                                    echo Html::tag('span', "Finalizado", ['class' => 'badge bg-primary']);
                                } else if ($model->Estado == "ELIMINADO") {
                                    echo Html::tag('span', "Eliminado", ['class' => 'badge bg-danger']);
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td><b>Fecha creacion:</b></td>
                            <td><?= $model->CreateDate ?></td>
                            <td><b>Fecha de movimiento:</b></td>
                            <td>
                                <?php
                                if (count($model->detalleMovimiento) > 0) {
                                    $movimiento = MovimientoModel::find()->where(['IdMovimiento' => $model->detalleMovimiento[0]->IdMovimiento])->one();
                                    echo Html::tag('span', $movimiento->Fecha, ['class' => 'badge bg-purple']);
                                } else {
                                    echo '';
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td><b>Observaciones: </b></td>
                            <td><?= $model->Observaciones ?></td>
                            <td><b>Empresa destino: </b></td>
                            <td class="text-center">
                                <?php
                                $ruta = Yii::$app->request->baseUrl . '/logos-empresas/';

                                $imagenCany =  $ruta . 'logo-cany.jpeg'; //cany
                                $imagenBoutique =  $ruta . 'logo-boutique.jpg'; //boutique
                                $imagenCarisma =  $ruta . 'logo-carisma.jpeg'; //carisma
                                $imagenNyc =  $ruta . 'logo-nyc.png'; //nyc
                                $imagenNys =  $ruta . 'logo-nys.jpeg'; //nyc
                                ?>
                                <div class="carisma">
                                    <img src="
                                <?php
                                if ($model->EmpresaDestino == 'cany')
                                    echo $imagenCany;
                                else if ($model->EmpresaDestino == 'boutique')
                                    echo $imagenBoutique;
                                else if ($model->EmpresaDestino == 'carisma')
                                    echo $imagenCarisma;
                                else if ($model->EmpresaDestino == 'nyc')
                                    echo $imagenNyc;
                                else if ($model->EmpresaDestino == 'nys')
                                    echo $imagenNys;
                                ?>" class="img-fluid" alt="Código QR">
                                    <h5><b><?= $model->EmpresaDestino ?></b></h5>
                                </div>

                            </td>
                        </tr>
                    </table>
                    <?php if (count($detalle) > 0) { ?>
                        <table class="table table-sm table-striped table-hover  table-bordered">
                            <thead class="thead-dark">
                                <th width="50%">Articulo</th>
                                <th width="16%">Cantidad</th>
                                <th width="16%">Precio unitario</th>
                                <th>Total</th>
                            </thead>
                            <tbody>
                                <?php
                                $totalFinal = 0;
                                foreach ($detalle as $d) { ?>
                                    <tr>
                                        <td><?= $d->ArticuloDetalle, 2 ?></td>
                                        <td><?= $d->Cantidad ?></td>
                                        <td>$<?= number_format($d->PrecioUnitario, 2) ?></td>
                                        <td>$<?= number_format(($d->Cantidad * $d->PrecioUnitario), 2) ?></td>
                                    </tr>
                                <?php $totalFinal += ($d->Cantidad * $d->PrecioUnitario);
                                } ?>
                                <tr>
                                    <td colspan="3" class="text-right">Total:</td>
                                    <td><b>$<?= $totalFinal ?></b></td>
                                </tr>
                            </tbody>
                        </table>
                    <?php } ?>
                </div>
                <div class="card-footer">
                    <?php echo Html::a(
                        '<span class="fas fa-print"> Imprimir codigo de barra</span>',
                        Url::to(
                            substr(Yii::$app->request->baseUrl, 0, -3) . 'views/detalle-registro/pdf-registro.php?codigoBarra=' . $model->CodigoBarra,
                            true,
                        ),
                        ['target' => '_blank', 'class' => 'btn btn-outline-success mt-2', 'data-pjax' => 0]
                    ) ?>
                    <?= Html::a(
                        '<span class="fas fa-print"> Imprimir desglose</span>',
                        Url::to(
                            substr(Yii::$app->request->baseUrl, 0, -3) . 'views/detalle-registro/pdf-detalle-registro.php?codigoBarra=' . $model->CodigoBarra,
                            true,
                        ),
                        ['target' => '_blank', 'class' => 'btn btn-outline-primary mt-2', 'data-pjax' => 0]
                    ) ?>
                    <?= Html::a(
                        '<i class="fas fa-eye"></i> Ver registro',
                        ['detalle-registro/view', 'codigoBarra' => $model->CodigoBarra, 'condicionImprimir' => ''],
                        [
                            'class' => 'btn btn-outline-info mt-2',
                            'data-pjax' => 0,
                        ]
                    ) ?>

                    <?= Html::a(
                        '<i class="fas fa-pen"></i> Actualizar registro',
                        [
                            'detalle-registro/update',
                            'codigoBarra' => $model->CodigoBarra
                        ],
                        [
                            'class' => 'btn btn-outline-warning mt-2',
                            'data-pjax' => 0,
                        ]
                    ) ?>
                    <?= Html::a(
                        '<i class="fas fa-trash-alt"></i> Eliminar registro',
                        [
                            'detalle-registro/delete-registro',
                            'codigoBarra' => $model->CodigoBarra,
                            'ruta' => 'busqueda'
                        ],
                        [
                            'class' => 'btn btn-outline-dark mt-2',
                            'id' => 'delete-registro-prod',
                        ]
                    ); ?>
                <?php } ?>
                <?= Html::button('<i class="fa fa-image"></i> Generar reporte en imagen', [
                    'class' => 'btn btn-outline-danger mt-2', 'id' => 'imprimir',
                ]) ?>


                </div>

            </div>

    </div>
</div>
<script>
    var borrarProd = document.getElementById("delete-registro-prod")
    var recargarPagina = document.getElementById('redo-index-prod')
    borrarProd.addEventListener("click", (e) => {
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
                    url: `${borrarProd.href}`,
                    success: function() {
                        recargarPagina.click
                    }
                })
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
                const barcode = document.getElementById("barcode-report")
                const a = document.createElement('a')
                a.setAttribute('download', `${barcode.innerText.split(':')[1]}`)
                a.setAttribute('href', image)
                a.click()
                canvas.remove()
            })
    }

    var btn = document.querySelector('#imprimir')
    btn.addEventListener('click', capture)
</script>