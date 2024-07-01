<?php

use yii\helpers\Html;
use yii\helpers\Url;

Yii::$app->formatter->locale = 'en-US';
$background = '';
$numeroAleatorio = rand(1, 5);
$background = ($numeroAleatorio % 1 == 0) ? 'primary' : $background;
$background = ($numeroAleatorio % 2 == 0) ? 'danger' : $background;
$background = ($numeroAleatorio % 3 == 0) ? 'success' : $background;
$background = ($numeroAleatorio % 4 == 0) ? 'warning' : $background;
$background = ($numeroAleatorio % 5 == 0) ? 'dark' : $background;
?>

<div class="row">
    <div class="col-md-12 p-3">
        <div class="card card-<?=$background?>">
            <div class="card-header d-flex justify-content-between">
                <h3 id="barcode-report" class="card-title"><i class="fas fa-briefcase"></i> &nbsp;Codigo de barra: <?= $model->CodigoBarra ?></h3>
                <h3 class="card-title"><i class="fas fa-clock"></i> &nbsp; Fecha de produccion: <?= $model->FechaCreacion ?></h3>
                <h3 class="card-title"><i class="fas fa-question"></i> &nbsp; Tipo de registro: <?= $model->tipoRegistro->TipoRegistro ?>
                </h3>
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
                        <td><?= $model->BodegaCreacion ?></td>
                        <td><b>Bodega actual: </b></td>
                        <td><?= $model->BodegaActual ?></td>
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
                        <td><b>Activo: </b></td>
                        <td><span class="badge bg-<?= $model->Estado == "PROCESO" ? "green" : "blue"; ?>"><?= $model->Estado == "PROCESO" ? "En proceso" : "FINALIZADO"; ?></span></td>
                    </tr>
                    <tr>
                        <td><b>Fecha creacion:</b></td>
                        <td><?= $model->CreateDate ?></td>
                        <td><b>Fecha modificacion:</b></td>
                        <td><?= $model->FechaModificacion ?></td>
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
                                    <td><?= $d->ArticuloDetalle ?></td>
                                    <td><?= $d->PrecioUnitario ?></td>
                                    <td><?= $d->Cantidad ?></td>
                                    <td><?= ($d->Cantidad * $d->PrecioUnitario) ?></td>
                                </tr>
                            <?php $totalFinal += ($d->Cantidad * $d->PrecioUnitario);
                            } ?>
                            <tr>
                                <td colspan="4" class="text-right"><b>Total: $<?= $totalFinal ?></b></td>
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
                <?php if ($model->Estado == 'PROCESO' && $model->IdTipoRegistro == 1) { ?>
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
                        ],
                        [
                            'class' => 'btn btn-outline-dark mt-2',
                            'id' => 'delete-registro-prod',
                        ]
                    ); ?>
                <?php } ?>
            </div>
        </div>
    </div>
</div>