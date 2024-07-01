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
        <div class="card card-<?= $background ?>">
            <div class="card-header d-flex justify-content-between">
                <h3>Codigos de barra</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <table class="table table-hover table-bordered">
                        <thead class="thead-light text-center">
                            <th><h4>#</h4></th>
                            <th><h4>Codigo barra</h4></th>
                            <th><h4>Acciones</h4></th>
                        </thead>
                        <tbody>
                            <?php foreach ($model as $index => $registro) { ?>
                                <tr class="text-center">
                                    <td><?= ($index + 1) ?></td>
                                    <td>
                                        <h4><i class="fas fa-briefcase"></i> &nbsp;Codigo de barra: <span class="badge bg-<?=$background?>"><?= $registro->CodigoBarra ?></span></h4>
                                    </td>
                                    <td>
                                        <?= Html::a(
                                            '<i class="fas fa-eye"></i> Ver registro',
                                            ['detalle-registro/view', 'codigoBarra' => $registro->CodigoBarra, 'condicionImprimir' => ''],
                                            [
                                                'class' => "btn btn-outline-$background mt-2",
                                                'data-pjax' => 0,
                                                'target' => '_blank'
                                            ]
                                        ) ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>