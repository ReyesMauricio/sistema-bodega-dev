<?php

use kartik\number\NumberControl;
use kartik\widgets\ActiveForm;
use kartik\widgets\DatePicker;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Crear registro';
$this->params['breadcrumbs'][] = ['label' => 'Listado', 'url' => ['index-contenedor']];
$this->params['breadcrumbs'][] = $this->title;

?>
<style>
    .small-cell {
        font-size: 14px; /*Tamaño de fuente más pequeño*/
        padding: 2px 5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .small-button {
        font-size: 14px; /* Mismo tamaño de fuente que small-cell */
        padding: 2px 5px; /* Mismo relleno que small-cell */
        width: 100%; /* Ancho del input */
        box-sizing: border-box; /* Incluye el padding en el ancho */
        
    }

    .input-small {
        font-size: 14px; /* Mismo tamaño de fuente que small-cell */
        padding: 2px 5px; /* Mismo relleno que small-cell */
        border-radius: 4px; /* Bordes redondeados */
        width: 50px; 
    }
</style>
<h1>Crear valor de contenedor</h1>
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-truck"></i> &nbsp;Valor de contenedor</h3>
    </div>
    <?php $form = ActiveForm::begin(['type' => ActiveForm::TYPE_HORIZONTAL]); ?>
    <div class="card-body">
        <form role="form">
            <div class="row">
                <div class="col-sm-12">
                    <?php if (isset($nombreEmpresa)): ?>
                        <label for="" class="control-label">EMPRESA</label>
                        <input type="text" class="form-control" id="nombreEmpresa" name="nombreEmpresa" disabled value="<?php echo $nombreEmpresa ?>">
                    <?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <?= Html::activeLabel($valorContenedor, 'fecha', ['class' => 'control-label']) ?>
                    <?= $form->field($valorContenedor, 'fecha', ['showLabels' => false])->widget(DatePicker::class, [
                        'options' => [
                            'placeholder' => 'Seleccionar fecha',
                            'disabled' => $detalle == true ? true : false
                        ],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-m-dd', 
                            'todayHighlight' => true
                        ],
                    ]); ?>
                </div>
                <div class="col-md-3">
                    <?= Html::activeLabel($valorContenedor, 'contenedor', ['class' => 'control-label']) ?>
                    <?= $form->field($valorContenedor, 'contenedor', ['showLabels' => false])->textInput(
                        [
                            'maxlength' => true,
                            'disabled' => $detalle == true ? true : false
                        ]
                    ) ?>
                </div>
                <div class="col-md-3">
                    <?= Html::activeLabel($valorContenedor, 'gasto', ['class' => 'control-label']) ?>
                    <?= $form->field($valorContenedor, 'gasto',  ['showLabels' => false])->widget(NumberControl::class, [
                        'name' => 'gasto',
                        'disabled' => $detalle == true ? true : false
                    ]);
                    ?>
                </div>
                <div class="col-md-2 mt-2">
                    <br>
                    <?= Html::submitButton('Verificar', ['class' => 'btn btn-success']) ?>
                    <?= Html::a('Reiniciar', Url::to(Yii::$app->request->baseUrl . '/index.php?r=registro/valor-contenedor'), ['class' => 'btn btn-danger']) ?>
                </div>
            </div>
        </form>
        <?php
        ActiveForm::end();
        ?>
    </div>
</div>

<?php
if ($detalle == true & count($data) > 0) { ?>

<?= count($data);?>
    <table id="myTable" class="table table-bordered table-hover">
        <thead class="thead-dark">
            <th class="small-cell">Articulo</th>
            <th class="small-cell">Cantidad</th>
            <th class="small-cell">Libras</th>
            <th class="small-cell">P. U.</th>
            <th class="small-cell">Subtotal</th>
            <th class="small-cell">Porcentaje</th>
            <th class="small-cell">Distribucion</th>
            <th class="small-cell">Subtotal final</th>
            <th class="small-cell">P. U. final</th>
            <th class="small-cell">Acciones</th>
        </thead>
        <tbody class="bg-light">
            <?php $sum = 0; foreach ($data as $detalle) { ?>
                <tr>
                    <?= '<td class="small-cell" id="articulo" data-articulo="' . $detalle['Articulo'] . '" data-lb=' . $detalle['Libras'] . ' data-descripcion="' . $detalle['Descripcion'] . '" data-bodega="' . $detalle["BodegaCreacion"] . '" data-cantidad="' . $detalle['Cantidad'] . '">' . $detalle['Articulo'] . " " . $detalle['Descripcion'] . '</td>' ?>
                    <?= '<td class="small-cell">' . $detalle['Cantidad'] . '</td>' ?>
                    <?= '<td class="small-cell">' . $detalle['Libras'] . '</td>' ?>
                    <td class=""><input class="input-small" type="text" name="" id="precio-unitario"></td>
                    <td class="small-cell" id="subtotal">0</td>
                    <td class="small-cell"  id="porcentaje">0</td>
                    <td class="small-cell" id="distribucion-gasto">0</td>
                    <td class="small-cell" id="subtotal-final">0</td>
                    <td class="small-cell" id="pu-final">0</td>
                    <td class=""><button class="btn btn-primary small-button" id="btnCalcular" type="button" data-articulo="<?= $detalle['Articulo'] ?>" data-descripcion="<?= $detalle['Descripcion'] ?>" data-cantidad="<?= $detalle['Cantidad'] ?>">Calcular</button></td>
                </tr>
            <?php
        $sum += $detalle['Libras'];
        } echo $sum;?>
            <tr>
                <td colspan="5" class="text-right small-cell" id="suma-subtotal">0</td>
                <td id="suma-porcentaje" class="small-cell">0</td>
                <td id="suma-gasto" class="small-cell">0</td>
                <td colspan="3" class="text-left small-cell" id="suma-gasto-final">0</td>
            </tr>
            <tr>
                <td colspan="10" class="text-right">
                    <?php $form = ActiveForm::begin([
                        'type' => ActiveForm::TYPE_HORIZONTAL,
                        'id' => 'set-valor-contenedor',
                        'action' => Url::to(['/registro/set-valor-contenedor']),
                        'method' => 'POST',
                    ]);
                    ?>
                    <input type="text" name="valoresPost" id="valoresPost" hidden>
                    <input type="text" name="fecha-contenedor" id="fecha-contenedor" hidden>
                    <button type="submit" class="btn btn-danger small-button"><i class="fas fa-exclamation"></i>&nbsp;Finalizar costeo de contenedor</button>
                    <input type="text" class="form-control" id="nombreEmpresa" name="nombreEmpresa" hidden value="<?php echo $nombreEmpresa ?>">
                    <input type="text" class="form-control" id="contenedor2" name="contenedor2" hidden value="">
                    <?php
                    ActiveForm::end();
                    ?>
                </td>
            </tr>
        </tbody>
    </table>
<?php } ?>

<script>
    let btnAllCalcular = document.querySelectorAll('#btnCalcular');
    btnAllCalcular.forEach((item) => {
        item.addEventListener('click', (e) => {

            let gasto = document.getElementById('dynamicmodel-gasto').value;
            //Obtener el indice de la fila donde se disparo el evento
            let rowIndex = e.target.closest('tr').rowIndex
            let table = document.getElementById("myTable")
            let lastRow = document.getElementById("myTable").rows.length
            let sumaSubtotal = document.getElementById('suma-subtotal');
            let sumaPorcentaje = document.getElementById('suma-porcentaje');
            let sumaGasto = document.getElementById('suma-gasto');
            let sumaGastoFinal = document.getElementById('suma-gasto-final');

            let precio = table.rows[rowIndex].cells[3].lastChild.value

            sumaSubtotal

            //colocando subtotal
            table.rows[rowIndex].cells[4].innerHTML = parseFloat(precio) * parseFloat(table.rows[rowIndex].cells[2].innerHTML)
            table.rows[rowIndex].cells[0].dataset.subtotalbase = table.rows[rowIndex].cells[4].innerHTML
            let sumSubtotal = 0;
            for (let i = 1; i < lastRow - 2; i++) {
                sumSubtotal += parseFloat(table.rows[i].cells[4].innerHTML)
            }

            sumaSubtotal.innerHTML = sumSubtotal

            for (let i = 1; i < lastRow - 2; i++) {

                table.rows[i].cells[5].innerHTML = parseFloat(table.rows[i].cells[4].innerHTML) / parseFloat(sumaSubtotal.innerHTML)
                table.rows[i].cells[6].innerHTML = Number((parseFloat(table.rows[i].cells[5].innerHTML) * parseFloat(gasto)).toFixed(3))
                table.rows[i].cells[7].innerHTML = Number((parseFloat(table.rows[i].cells[6].innerHTML) + parseFloat(table.rows[i].cells[4].innerHTML)).toFixed(3))
                table.rows[i].cells[8].innerHTML = parseFloat(table.rows[i].cells[7].innerHTML) / parseFloat(table.rows[i].cells[2].innerHTML)
            }

            let sumPorcentaje = 0;
            for (let i = 1; i < lastRow - 2; i++) {
                sumPorcentaje += parseFloat(table.rows[i].cells[5].innerHTML)
            }

            let sumDistribucion = 0;
            for (let i = 1; i < lastRow - 2; i++) {
                sumDistribucion += parseFloat(table.rows[i].cells[6].innerHTML)
            }

            let sumSubtotalFinal = 0;
            for (let i = 1; i < lastRow - 2; i++) {
                sumSubtotalFinal += parseFloat(table.rows[i].cells[7].innerHTML)
            }

            sumaPorcentaje.innerHTML = sumPorcentaje
            sumaGasto.innerHTML = sumDistribucion
            sumaGastoFinal.innerHTML = sumSubtotalFinal

        })
    })
    let finalizarCosteo = document.getElementById('set-valor-contenedor')
    let fechaContenedor = document.getElementById('dynamicmodel-fecha').value
    let contenedor = document.getElementById('dynamicmodel-contenedor').value

    finalizarCosteo.addEventListener('click', (e) => {
        e.preventDefault();

        let articulos = document.querySelectorAll("#articulo")
        let preciosUnitarios = document.querySelectorAll("#pu-final")
        let arrayValoresPos = [];

        for (let i = 0; i < articulos.length; i++) {
            console.log(articulos[i]);
            arrayValoresPos.push(`${articulos[i].dataset.articulo}, ${preciosUnitarios[i].innerHTML}, ${articulos[i].dataset.lb}, ${articulos[i].dataset.descripcion}, ${articulos[i].dataset.bodega}, ${articulos[i].dataset.cantidad}, ${articulos[i].dataset.subtotalbase}`)
        }
        console.log(arrayValoresPos);

        let valoresPost = document.getElementById('valoresPost')
        valoresPost.value = JSON.stringify(arrayValoresPos);

        let nombreEmpresa = document.getElementById('nombreEmpresa').value
        console.log(nombreEmpresa)

        //enviamos el nombre de la bodega al enviar los datos de la valoracion
        let primerElemento = arrayValoresPos[0];
        let partesPrimerElemento = primerElemento.split(","); 
        let nombreBodega = partesPrimerElemento[4].trim(); 
        document.getElementById('contenedor2').value = nombreBodega;
        console.log(nombreBodega);

        let valoresFechaContenedor = document.getElementById('fecha-contenedor')
        valoresFechaContenedor.value = `${fechaContenedor}, ${contenedor}`

        Swal.fire({
            title: '¿Estas seguro de terminar este costeo?',
            text: 'No podras deshacer estos valores mas adelante, ni agregar mas valores al contenedor.',
            showCancelButton: true,
            icon: 'question',
            confirmButtonText: 'Confirmar',
            cancelButtonText: `Cancelar`,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
        }).then((result) => {
            if (result.isConfirmed) {
                finalizarCosteo.submit();
            }
        })
    })
</script>