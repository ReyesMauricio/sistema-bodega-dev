<?php

use yii\helpers\Html;
use yii\helpers\Url;

Yii::$app->formatter->locale = 'en-US';
$this->title = 'Detalle';
$this->params['breadcrumbs'][] = ['label' => 'Listado', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-briefcase"></i> &nbsp;<?= $model->CodigoBarra ?></h3>
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
            </div>
            <div class="card-footer">
                <?php echo Html::a(
                    '<span class="fas fa-print"> Imprimir</span>',
                    Url::to(
                        substr(Yii::$app->request->baseUrl, 0, -3) . 'views/detalle-registro/pdf-registro.php?codigoBarra=' . $model->CodigoBarra,
                        true,
                    ),
                    ['target' => '_blank', 'class' => 'btn btn-outline-success', 'data-pjax' => 0]
                ) ?>
                <?= Html::a('<i class="fas fa-plus"></i> Crear nuevo registro', ['create-detalle'], [
                    'class' => 'btn btn-primary',
                    'data-pjax' => 0,
                ]) ?>
                <?= Html::a('<i class="fas fa-pen"></i> Actualizar registro', ['update', 'codigoBarra' => $model->CodigoBarra], [
                    'class' => 'btn btn-warning',
                    'data-pjax' => 0,
                ]) ?>

                <?=
                Html::a('<i class="fa fa-arrow-left"></i> Regresar', ['index'], ['class' => 'btn btn-danger', 'data-toggle' => 'tooltip', 'title' => 'Cancelar']);
                ?>
            </div>
        </div>
    </div>
</div>
<script>
    var scroll = $(window).scrollTop();
    var scrollto = scroll + 500;
    $("html, body").animate({
        scrollTop: scrollto
    });
</script>
<?= $this->render('_gridDetalleRegistro', [
    'model' => $model,
    'searchModel' => $searchModel,
    'dataProvider' => $dataProvider,
    'imprimir' => $imprimir,
]) ?>