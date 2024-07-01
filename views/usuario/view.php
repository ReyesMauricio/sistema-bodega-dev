<?php

use yii\helpers\Html;

Yii::$app->formatter->locale = 'es_ES';
$this->title = 'Detalle';
$this->params['breadcrumbs'][] = ['label' => 'Listado', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-briefcase"></i> &nbsp;<?= $model->IdUsuario . ' - ' .  $model->Nombre ?></h3>
            </div>
            <div class="card-body">
                <table class="table table-sm table-striped table-hover table-bordered">
                    <tr>
                        <td width="16%"><b>Usuario:</b></td>
                        <td width="34%"><?= $model->Usuario ?></td>
                        <td width="16%"><b>Nombre completo:</b></td>
                        <td width="34%"> <?= $model->Nombre ?></td>
                    </tr>
                    <tr>
                        <td><b>Digita: </b></td>
                        <td><?= $model->Digita == 1 ? '<span class="badge bg-primary">Digita</span>' : '<span class="badge bg-danger">No digita</span>' ?></td>
                        <td><b>Empaca: </b></td>
                        <td><?= $model->Empaca == 1 ? '<span class="badge bg-primary">Empaca</span>' : '<span class="badge bg-danger">No empaca</span>' ?></td>
                    </tr>
                    <tr>
                        <td><b>Produce: </b></td>
                        <td><?= $model->Produce == 1 ? '<span class="badge bg-primary">Produce</span>' : '<span class="badge bg-danger">No produce</span>' ?></td>
                        <td><b>Activo: </b></td>
                        <td><?= $model->Activo == 1 ? '<span class="badge bg-primary">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>' ?></td>
                    </tr>
                </table>
            </div>
            <div class="card-footer">
                <?php echo Html::a('<i class="fa fa-edit"></i> Editar', ['update', 'IdUsuario' => $model->IdUsuario], ['class' => 'btn btn-primary', 'data-toggle' => 'tooltip', 'title' => 'Edit record']) ?>
                <?php echo Html::a('<i class="fa fa-arrow-left"></i> Regresar', ['index'], ['class' => 'btn btn-danger', 'data-toggle' => 'tooltip', 'title' => 'Cancelar']) ?>
            </div>
        </div>
    </div>
</div>