<?php

use app\models\RegistroModel;
use kartik\widgets\ActiveForm;
use kartik\widgets\DatePicker;
use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = 'Pagina de inicio';
$this->params['breadcrumbs'] = [['label' => $this->title]];
$fechaInicio = isset($_POST['fechaInicio']) ? $_POST['fechaInicio'] : date("Y-m-d");
$fechaFin = isset($_POST['fechaFin']) ? $_POST['fechaFin'] : date("Y-m-d");

?>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-12">
            <a href="index.php?r=detalle-registro/create-detalle">
                <?php $smallBox = \hail812\adminlte\widgets\SmallBox::begin([
                    'title' => 'Producción',
                    'text' => 'Crear registro',
                    'icon' => 'fas fa-file',
                    'options' => ['class' => 'small-box p-2'],
                    'theme' => 'success',
                    'linkText' => 'Ir a',
                    'linkUrl' => 'index.php?r=detalle-registro/create-detalle'
                ]) ?>
                <?php \hail812\adminlte\widgets\SmallBox::end() ?>
            </a>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6 col-12">
            <a href="index.php?r=registro/create-contenedor">
                <?php $smallBox = \hail812\adminlte\widgets\SmallBox::begin([
                    'title' => 'Contenedor',
                    'text' => 'Crear registro',
                    'icon' => 'fas fa-truck',
                    'options' => ['class' => 'small-box p-2'],
                    'theme' => 'danger',
                    'linkText' => 'Ir a',
                    'linkUrl' => 'index.php?r=registro/create-contenedor'
                ]) ?>
                <?php \hail812\adminlte\widgets\SmallBox::end() ?>
            </a>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6 col-12">
            <a href="index.php?r=barriles/index-barriles&condicionImprimir=">
                <?php $smallBox = \hail812\adminlte\widgets\SmallBox::begin([
                    'title' => 'Barriles',
                    'text' => 'Crear registro',
                    'icon' => 'fas fa-database',
                    'options' => ['class' => 'small-box p-2'],
                    'theme' => 'purple',
                    'linkText' => 'Ir a',
                    'linkUrl' => 'index.php?r=barriles/index-barriles&condicionImprimir='
                ]) ?>
                <?php \hail812\adminlte\widgets\SmallBox::end() ?>
            </a>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6 col-12">
            <a href="index.php?r=mesas/create-mesa">
                <?php $smallBox = \hail812\adminlte\widgets\SmallBox::begin([
                    'title' => 'Mesas de produccion',
                    'text' => 'Crear registro',
                    'options' => ['class' => 'small-box p-2'],
                    'icon' => 'fas fa-inbox',
                    'theme' => 'pink',
                    'linkText' => 'Ir a',
                    'linkUrl' => 'index.php?r=mesas/create-mesa'
                ]) ?>
                <?php \hail812\adminlte\widgets\SmallBox::end() ?>
            </a>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6 col-12">
            <a href="index.php?r=traslados/create-traslado">
                <?php $smallBox = \hail812\adminlte\widgets\SmallBox::begin([
                    'title' => 'Traslado',
                    'text' => 'Crear registro',
                    'options' => ['class' => 'small-box p-2'],
                    'icon' => 'fas fa-arrow-left',
                    'theme' => 'yellow',
                    'linkText' => 'Ir a',
                    'linkUrl' => 'index.php?r=traslados/create-traslado'
                ]) ?>
                <?php \hail812\adminlte\widgets\SmallBox::end() ?>
            </a>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6 col-12">
            <a href="index.php?r=traslados/create-despacho">
                <?php $smallBox = \hail812\adminlte\widgets\SmallBox::begin([
                    'title' => 'Despacho',
                    'text' => 'Crear registro',
                    'options' => ['class' => 'small-box p-2'],
                    'icon' => 'fas fa-arrow-right',
                    'theme' => 'primary',
                    'linkText' => 'Ir a',
                    'linkUrl' => 'index.php?r=traslados/create-despacho'
                ]) ?>
                <?php \hail812\adminlte\widgets\SmallBox::end() ?>
            </a>
        </div>
    </div>
</div>
<div class="row p-2 " style="outline:solid 1px blue;outline-offset:-0.5rem;">
    <div class="col-md-11 mt-2">
        <?php ActiveForm::begin() ?>

        <?php
        $layout3 = <<< HTML
        <span class="input-group-text">Desde</span>
        {input1}
        {separator}
        <span class="input-group-text">Hasta</span>
        {input2}
         
            <span class="input-group-text kv-date-remove">
                <i class="fas fa-times kv-dp-icon"></i>
            </span>
         
        HTML;
        echo DatePicker::widget([
            'name' => 'fechaInicio',
            'type' => DatePicker::TYPE_RANGE,
            'value' => $fechaInicio,
            'name2' => 'fechaFin',
            'value2' => $fechaFin,
            'separator' => '<i class="fas fa-arrows-alt-h"></i>',
            'layout' => $layout3,
            'options' => [
                'placeholder' => 'Seleccionar fecha de inicio',
            ],
            'options2' => [
                'placeholder' => 'Seleccionar fecha de fin',
            ],
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'yyyy-m-dd',
                'todayHighlight' => true
            ],
        ]); ?>
    </div>
    <div class="col-md-1 p-0 mt-2">
        <button type="submit" class="btn btn-primary">Filtrar</button>
        <?=
        Html::a('<i class="fas fa-redo"></i>', ['index'], [
            'class' => 'btn btn-success',
            'data-pjax' => 0,
        ])
        ?>
    </div>
    <?php ActiveForm::end() ?>

    <div class="col-md-6 mt-3">
        <figure class="highcharts-figure">
            <div id="container-fardos" class="border border-dark"></div>
        </figure>
    </div>
    <div class="col-md-6 mt-3">
        <figure class="highcharts-figure">
            <div id="container-libras" class="border border-dark"></div>
        </figure>
    </div>
</div>

<?php if ($fechaInicio != date("Y-m-d")) { ?>
    <script>
        var scroll = $(window).scrollTop();
        var scrollto = scroll + 500;
        $("html, body").animate({
            scrollTop: scrollto
        });
    </script>
<?php } ?>

<script>
    Highcharts.chart('container-fardos', {
        chart: {
            type: 'column'
        },
        title: {
            text: 'Cantidad de fardos producidos el dia <?= date("F j, Y", strtotime(date("Y-m-d"))) ?>',
            align: 'center'
        },
        yAxis: {
            title: {
                text: 'Cantidad de fardos por clasificacion'
            },

        },

        xAxis: {
            categories: [
                '<?= $fechaInicio ?> - <?= $fechaFin ?>'
            ]
        },

        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'top',
            x: -40,
            y: 80,
            floating: true,
            borderWidth: 1,
            backgroundColor: Highcharts.defaultOptions.legend.backgroundColor || '#FFFFFF',
            shadow: true
        },
        plotOptions: {
            bar: {
                dataLabels: {
                    enabled: true
                }
            }
        },
        series: [{
                name: 'ROPA',
                data: [
                    <?=

                    $data = '';
                    $data = RegistroModel::find()->where("(FechaCreacion BETWEEN '$fechaInicio' AND '$fechaFin' AND Clasificacion = 'ROPA')")->all();

                    echo count($data);

                    ?>
                ]
            },
            {
                name: 'ZAPATOS',
                data: [
                    <?=
                    $data = '';
                    $data = RegistroModel::find()->where("(FechaCreacion BETWEEN '$fechaInicio' AND '$fechaFin' AND Clasificacion = 'ZAPATOS')")->all();
                    echo count($data);

                    ?>
                ]
            },
            {
                name: 'CINCHOS',
                data: [
                    <?=
                    $data = '';
                    $data = RegistroModel::find()->where("(FechaCreacion BETWEEN '$fechaInicio' AND '$fechaFin' AND Clasificacion = 'CINCHOS')")->all();
                    echo count($data);

                    ?>
                ]
            },
            {
                name: 'CARTERAS',
                data: [
                    <?=
                    $data = '';
                    $data = RegistroModel::find()->where("(FechaCreacion BETWEEN '$fechaInicio' AND '$fechaFin' AND Clasificacion = 'CARTERAS')")->all();
                    echo count($data);

                    ?>
                ]
            },
            {
                name: 'JUGUETES',
                data: [
                    <?=
                    $data = '';
                    $data = RegistroModel::find()->where("(FechaCreacion BETWEEN '$fechaInicio' AND '$fechaFin' AND Clasificacion = 'JUGUETES')")->all();
                    echo count($data);

                    ?>
                ]
            },
            {
                name: 'OTROS',
                data: [
                    <?=
                    $data = '';
                    $data = RegistroModel::find()->where("(FechaCreacion BETWEEN '$fechaInicio' AND '$fechaFin' AND Clasificacion = 'OTROS')")->all();
                    echo count($data);

                    ?>
                ]
            },
        ],

        responsive: {
            rules: [{
                condition: {
                    maxWidth: 500
                },
                chartOptions: {
                    legend: {
                        layout: 'horizontal',
                        align: 'center',
                        verticalAlign: 'bottom'
                    }
                }
            }]
        }

    });
</script>
<script>
    Highcharts.chart('container-libras', {
        chart: {
            type: 'column'
        },
        title: {
            text: 'Cantidad de libras producidos el dia <?= date("F j, Y", strtotime(date("Y-m-d"))) ?>',
            align: 'center'
        },
        yAxis: {
            title: {
                text: 'Cantidad de libras por clasificacion'
            },

        },

        xAxis: {
            categories: [
                '<?= $fechaInicio ?> - <?= $fechaFin ?>'
            ]
        },

        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'top',
            x: -40,
            y: 80,
            floating: true,
            borderWidth: 1,
            backgroundColor: Highcharts.defaultOptions.legend.backgroundColor || '#FFFFFF',
            shadow: true
        },
        plotOptions: {
            bar: {
                dataLabels: {
                    enabled: true
                }
            }
        },
        series: [{
                name: 'ROPA',
                data: [
                    <?=

                    $data = '';
                    $data = RegistroModel::find()->where("(FechaCreacion BETWEEN '$fechaInicio' AND '$fechaFin' AND Clasificacion = 'ROPA')")->all();
                    $count = 0;

                    foreach ($data as $registro) {
                        $count += $registro->Libras;
                    }
                    echo $count;

                    ?>
                ]
            },
            {
                name: 'ZAPATOS',
                data: [
                    <?=
                    $data = '';
                    $data = RegistroModel::find()->where("(FechaCreacion BETWEEN '$fechaInicio' AND '$fechaFin' AND Clasificacion = 'ZAPATOS')")->all();
                    $count = 0;

                    foreach ($data as $registro) {
                        $count += $registro->Libras;
                    }
                    echo $count;

                    ?>
                ]
            },
            {
                name: 'CINCHOS',
                data: [
                    <?=
                    $data = '';
                    $data = RegistroModel::find()->where("(FechaCreacion BETWEEN '$fechaInicio' AND '$fechaFin' AND Clasificacion = 'CINCHOS')")->all();
                    $count = 0;

                    foreach ($data as $registro) {
                        $count += $registro->Libras;
                    }
                    echo $count;

                    ?>
                ]
            },
            {
                name: 'CARTERAS',
                data: [
                    <?=
                    $data = '';
                    $data = RegistroModel::find()->where("(FechaCreacion BETWEEN '$fechaInicio' AND '$fechaFin' AND Clasificacion = 'CARTERAS')")->all();
                    $count = 0;

                    foreach ($data as $registro) {
                        $count += $registro->Libras;
                    }
                    echo $count;

                    ?>
                ]
            },
            {
                name: 'JUGUETES',
                data: [
                    <?=
                    $data = '';
                    $data = RegistroModel::find()->where("(FechaCreacion BETWEEN '$fechaInicio' AND '$fechaFin' AND Clasificacion = 'JUGUETES')")->all();
                    $count = 0;

                    foreach ($data as $registro) {
                        $count += $registro->Libras;
                    }
                    echo $count;

                    ?>
                ]
            },
            {
                name: 'OTROS',
                data: [
                    <?=
                    $data = '';
                    $data = RegistroModel::find()->where("(FechaCreacion BETWEEN '$fechaInicio' AND '$fechaFin' AND Clasificacion = 'OTROS')")->all();
                    $count = 0;

                    foreach ($data as $registro) {
                        $count += $registro->Libras;
                    }
                    echo $count;

                    ?>
                ]
            },
        ],

        responsive: {
            rules: [{
                condition: {
                    maxWidth: 500
                },
                chartOptions: {
                    legend: {
                        layout: 'horizontal',
                        align: 'center',
                        verticalAlign: 'bottom'
                    }
                }
            }]
        }

    });
</script>