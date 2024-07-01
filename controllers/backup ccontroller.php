<?php

namespace app\controllers;

use app\models\DetalleRegistroModel;
use app\models\RegistroModel;
use app\models\TipoEmpaqueModel;
use app\models\TrabajoMesaModel;
use app\models\TrabajoMesaRestanteModel;
use app\modelsSearch\DetalleRegistroModelSearch;
use app\modelsSearch\RegistroModelSearch;
use Exception;
use yii\web\Controller;
use Yii;
use yii\base\DynamicModel;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Url;

use function PHPSTORM_META\type;

/**
 * DetalleRegistroController implements the CRUD actions for DetalleRegistroModel model.
 */
class DetalleRegistroController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Crear una nueva conexion hacia la base de datos SOFTLAND
     * @return Yii\db\Connection
     */
    public function SoftlandConn()
    {
        return Yii::$app->db2;
    }

    /**
     * Muestra una lista de todos los registros de produccion
     * @return string|array|view Retorna una vista donde se mostraran todos los registros encontrados
     */
    public function actionIndex()
    {
        if (!isset($_SESSION['user'])) {
            return $this->redirect(Url::to(Yii::$app->request->baseUrl . '/index.php?r=site/login'));
        }

        $searchModel = new RegistroModelSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, 'IdTipoRegistro = 1');
        $dataProvider->sort->defaultOrder = ['CreateDate' => SORT_DESC];
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Retorna una vista que muestra el resumen de lo que contiene un fardo de produccion, y su detalle interno
     * @param string $codigoBarra el codigo de barra del fardo de produccion
     * @param string $condicionImprimir esta condicion permite saber cuando imprimir por primera vez un fardo de produccion
     */
    public function actionView($codigoBarra, $condicionImprimir)
    {
        $model = $this->findRegistroModel($codigoBarra);
        $searchModel = new DetalleRegistroModelSearch();
        $searchModel->IdRegistro = $model->IdRegistro;
        $dataProvider = $searchModel->search($this->request->queryParams);
        return $this->render('view', [
            'model' => $model,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'imprimir' => $condicionImprimir
        ]);
    }

    /**
     * Muestra una vista donde se puede reimprimir tanto etiquetas de fardo como etiquetas de desglose
     */
    public function actionImprimir()
    {
        if (!isset($_SESSION['user'])) {
            return $this->redirect(Url::to(Yii::$app->request->baseUrl . '/index.php?r=site/login'));
        }
        $searchModel = new RegistroModelSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, ['IdTipoRegistro' => 1]);
        $dataProvider->sort->defaultOrder = ['CreateDate' => SORT_DESC];

        return $this->render('imprimir-index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreateDetalle()
    {
        if (!isset($_SESSION['user'])) {
            return $this->redirect(Url::to(Yii::$app->request->baseUrl . '/index.php?r=site/login'));
        }
        $registro = new RegistroModel;
        $clasificacion = $this->getClasificacion();

        if ($registro->load($this->request->post()) && isset($registro->Articulo)) {
            $articulos = $this->getArticulosByEsquema($registro->Clasificacion);

            $articulo = $registro->Articulo;
            $registro->Descripcion = explode(" -", $articulo)[1];
            $registro->Articulo = explode(" -", $articulo)[0];

            $empacadores = $registro->EmpacadoPor;
            $productores = $registro->ProducidoPor;
            $registro->EmpacadoPor = $this->fromArrayToString($empacadores);
            $registro->ProducidoPor = $this->fromArrayToString($productores);

            //Obtener los codigos de la mesa
            $codigosMesaProduccion = TrabajoMesaModel::find()->where(['Fecha' => $registro->FechaCreacion, 'NumeroMesa' => $registro->MesaOrigen])->all();

            //Obtener el restante
            $registroRestante = $this->obtenerRestanteLibrasCostoMesa($registro->MesaOrigen);

            //Obtener la bodega 
            $registro->BodegaActual = RegistroModel::find()->where(['CodigoBarra' => $codigosMesaProduccion[0]->CodigoBarra])->one()->BodegaActual;

            //Verificar si ya se realizo finalizacion de produccion
            $existeProduccion = $this->obtenerProduccionFinalizadaDiaria($registro->FechaCreacion, $registro->MesaOrigen);

            //Obtener la suma de la produccion sin finalizar actual
            $sumaLibrasProduccionDia = RegistroModel::find()
                ->where("MesaOrigen = " . $registro->MesaOrigen . " AND FechaCreacion = '$registro->FechaCreacion' AND Estado = 'PROCESO'")->sum('Libras');

            $sumaLibrasCodigosMesa = $this->obtenerSumaLibrasCodigosMesa($codigosMesaProduccion);
            $sumaCostosCodigosMesa = $this->obtenerSumaCostosCodigosMesa($codigosMesaProduccion);

            if ($registroRestante && count($existeProduccion) > 0) {
                $sumaProduccionDia = RegistroModel::find()
                    ->select('SUM(Libras) as Libras, MesaOrigen')
                    ->where(['FechaCreacion' => $registro->FechaCreacion, 'MesaOrigen' => $registro->MesaOrigen, 'Estado' => 'FINALIZADO'])
                    ->groupBy(['MesaOrigen'])
                    ->one();

                echo "Restante de produccion finalizada: " . $registroRestante->Libras;
                echo "<br>Produccion finalizada: " .  $sumaProduccionDia->Libras;
                echo "<br>Asignado: " . $sumaLibrasCodigosMesa;

                $asignadoInicial = $registroRestante->Libras + $sumaProduccionDia->Libras;
                $restanteAnterior = $asignadoInicial - ($sumaLibrasCodigosMesa + 1000);
                
                if (($asignadoInicial - $restanteAnterior) != ($sumaLibrasCodigosMesa + 1000)) {
                    echo "AAAA";
                }
                
                die;
                $sumaLibrasProduccionDia = RegistroModel::find()
                    ->where("MesaOrigen = " . $registro->MesaOrigen . " AND FechaCreacion = '$registro->FechaCreacion' AND Estado = 'PROCESO'")->sum('Libras');

                $precioUnitario = $registroRestante->Libras / $registroRestante->Costo;
                $costoFardoProduccion = $registro->Libras * $precioUnitario;

                if (($sumaLibrasProduccionDia + $registro->Libras) > $registroRestante->Libras) {
                    $registro->EmpacadoPor = $empacadores;
                    $registro->ProducidoPor = $productores;
                    $registro->Articulo = $articulo;
                    Yii::$app->session->setFlash('danger', "Mesa #" . $registro->MesaOrigen . " no posee libras disponibles para produccion");
                    return $this->render('create-registro-prod-form', [
                        'registro' => $registro,
                        'clasificacion' => $clasificacion,
                        'articulos' => $articulos
                    ]);
                }

                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $sesionDataDay = $this->getSessionDataRegisterDay(date("Y-m-d "));
                    $codigoBarra = $this->generateBarCode($registro->FechaCreacion);
                    $this->crearRegistroProduccionREGISTRO($codigoBarra, $registro->Articulo, $registro->Descripcion, $registro->Clasificacion, $registro->Libras, $registro->Unidades, $registro->IdTipoEmpaque, $registro->EmpacadoPor, $registro->ProducidoPor, $registro->BodegaActual, $registro->Observaciones, $costoFardoProduccion, $registro->FechaCreacion, $sesionDataDay, $registro->EmpresaDestino, $registro->MesaOrigen);
                    $transaction->commit();
                } catch (Exception $e) {
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('warning', "Error: " . $e->getMessage());
                    return $this->redirect(['index']);
                }

                Yii::$app->session->setFlash('success', "Registro creado existosamente.");
                $imprimirFardo = "<script>window.open('http://localhost/yii2-prod/views/detalle-registro/pdf-registro.php?codigoBarra=" . $codigoBarra . "', '_blank')</script>";
                return $this->redirect(['view', 'codigoBarra' => $codigoBarra, 'condicionImprimir' => $imprimirFardo]);
            }

            if (!$codigosMesaProduccion) {
                $registro->EmpacadoPor = $empacadores;
                $registro->ProducidoPor = $productores;
                $registro->Articulo = $articulo;
                Yii::$app->session->setFlash('warning', "Mesa de origen no posee codigos asignados o restante");
                return $this->render('create-registro-prod-form', [
                    'registro' => $registro,
                    'clasificacion' => $clasificacion,
                    'articulos' => $articulos
                ]);
            }

            if ($registroRestante) {
                $sumaLibrasCodigosMesa += $registroRestante->Libras;
                $sumaCostosCodigosMesa += $registroRestante->Costo;
            }

            if (($sumaLibrasProduccionDia + $registro->Libras) > $sumaLibrasCodigosMesa) {
                $registro->EmpacadoPor = $empacadores;
                $registro->ProducidoPor = $productores;
                $registro->Articulo = $articulo;
                Yii::$app->session->setFlash('danger', "Mesa #" . $registro->MesaOrigen . " no posee libras disponibles para produccion");
                return $this->render('create-registro-prod-form', [
                    'registro' => $registro,
                    'clasificacion' => $clasificacion,
                    'articulos' => $articulos
                ]);
            }

            $precioUnitario = $sumaCostosCodigosMesa / $sumaLibrasCodigosMesa;
            $costoFardoProduccion = $registro->Libras * $precioUnitario;

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $sesionDataDay = $this->getSessionDataRegisterDay(date("Y-m-d "));
                $codigoBarra = $this->generateBarCode($registro->FechaCreacion);
                $this->crearRegistroProduccionREGISTRO($codigoBarra, $registro->Articulo, $registro->Descripcion, $registro->Clasificacion, $registro->Libras, $registro->Unidades, $registro->IdTipoEmpaque, $registro->EmpacadoPor, $registro->ProducidoPor, $registro->BodegaActual, $registro->Observaciones, $costoFardoProduccion, $registro->FechaCreacion, $sesionDataDay, $registro->EmpresaDestino, $registro->MesaOrigen);
                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('warning', "Error: " . $e->getMessage());
                return $this->redirect(['index']);
            }

            Yii::$app->session->setFlash('success', "Registro creado existosamente.");
            $imprimirFardo = "<script>window.open('http://localhost/yii2-prod/views/detalle-registro/pdf-registro.php?codigoBarra=" . $codigoBarra . "', '_blank')</script>";
            return $this->redirect(['view', 'codigoBarra' => $codigoBarra, 'condicionImprimir' => $imprimirFardo]);
        } else if ($registro->load($this->request->post())) {
            $articulos = $this->getArticulosByEsquema($registro->Clasificacion);
            return $this->render('create-registro-prod-form', [
                'registro' => $registro,
                'clasificacion' => $clasificacion,
                'articulos' => $articulos
            ]);
        } else {

            return $this->render('create-registro-prod-form', [
                'registro' => $registro,
                'clasificacion' => $clasificacion
            ]);
        }
    }

    /**
     * Updates an existing DetalleRegistroModel model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $IdDetalleRegistro Id Detalle Registro
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($IdRegistro)
    {
        $bodegas = $this->getBodegasByEsquema();
        $empacadores = $this->getEmpacadores();
        $empaque = $this->getTipoEmpaque();
        $productores = $this->getProductores();
        $clasificacion = $this->getClasificacion();
        $registro = $this->generateModelRegistro();
        $data = $this->findRegistroModel($IdRegistro);

        $registro->unidades = $data->Unidades;
        $registro->peso = $data->Libras;
        $registro->bodega = $data->BodegaCreacion;
        $registro->fecha_produccion = $data->FechaCreacion;
        $registro->empacado_por = $data->EmpacadoPor;
        $registro->producido_por = $data->ProducidoPor;
        $registro->tipo_empaque = $data->IdTipoEmpaque;
        $registro->observaciones = $data->Observaciones;
        $registro->clasificacion = $data->Clasificacion;
        if ($registro->load($this->request->post()) && isset($_POST['DynamicModel']['articulo'])) {
            $codigoBarra = $this->generateBarCode($registro->fecha_produccion);
            $descripcion = explode(" -", $_POST['DynamicModel']['articulo']);
            $empacado = '';
            foreach ($registro->empacado_por as $index => $val) {
                if ($index == 0) {
                    $empacado .= $val;
                } else {
                    $empacado .= ', ' . $val;
                }
            }

            $producido = '';
            foreach ($registro->producido_por as $index => $val) {
                if ($index == 0) {
                    $producido .= $val;
                } else {
                    $producido .= ', ' . $val;
                }
            }
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $sesionDataDay = $this->getSessionDataRegisterDay($registro->fecha_produccion) + 1;
                $sqlQueryREGISTRO = "UPDATE REGISTRO SET 
                CodigoBarra = '$codigoBarra',
                Articulo = '" . $descripcion[0] . "', 
                Descripcion = '" . $descripcion[1] . "',
                Clasificacion = '$registro->clasificacion'
                Libras = $registro->peso,
                Unidades = $registro->unidades, , 
                IdTipoEmpaque = $registro->tipo_empaque,
                IdUbicacion = 1, 
                EmpacadoPor = '$empacado', 
                ProducidoPor = '$producido',
                BodegaCreacion = '$registro->bodega', 
                Observaciones = '" . $_POST['DynamicModel']['observaciones'] . "',
                UsuarioCreacion = '" . $_SESSION['user'] . "', 
                Estado = 'PROCESO',   
                FechaCreacion = '$registro->fecha_produccion',
                Sesion = $sesionDataDay, 
                IdTipoRegistro =  1, 
                EmpresaDestino = '" . $_POST['destino'] . "'
                WHERE IdRegistro = $IdRegistro";

                $rowREGISTRO = Yii::$app->db->createCommand($sqlQueryREGISTRO)->execute();
                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollBack();
                return $this->redirect(['index']);
            }
            Yii::$app->session->setFlash('success', "Registro actualizado existosamente.");
            $imprimirFardo = "<script>window.open('http://localhost/yii2-prod/views/detalle-registro/pdf-registro.php?codigoBarra=" . $codigoBarra . "', '_blank')</script>";
            return $this->redirect(['view', 'codigoBarra' => $codigoBarra, 'condicionImprimir' => $imprimirFardo]);
        } else if ($registro->load($this->request->post())) {

            $articulos = $this->getArticulosByEsquema($registro->clasificacion);
            $registro->observaciones = $_POST['DynamicModel']['observaciones'];
            return $this->render('create-registro-prod-form', [
                'bodegas' => $bodegas,
                'registro' => $registro,
                'empacadores' => $empacadores,
                'empaque' => $empaque,
                'productores' => $productores,
                'clasificacion' => $clasificacion,
                'articulos' => $articulos
            ]);
        } else {
            return $this->render('create-registro-prod-form', [
                'bodegas' => $bodegas,
                'registro' => $registro,
                'empacadores' => $empacadores,
                'empaque' => $empaque,
                'productores' => $productores,
                'clasificacion' => $clasificacion
            ]);
        }
    }

    /**
     * Creates a new DetalleRegistroModel model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreateModalDetalle($IdRegistro, $clasificacion)
    {
        $model = new DetalleRegistroModel();
        $model->IdRegistro = $IdRegistro;
        $detalleArticulo = $this->getArticuloDetalle($clasificacion);
        if ($model->load($this->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {

                if ($model->save()) {
                    $transaction->commit();
                    return 1;
                } else {
                    throw new Exception(implode("<br />", \yii\helpers\ArrayHelper::getColumn($model->getErrors(), 0, false)));
                }
            } catch (Exception $e) {
                $transaction->rollBack();
                return $this->redirect(['view', 'IdRegistro' => $IdRegistro]);
            }
            Yii::$app->session->setFlash('success', "Registro creado exitosamente.");
            return $this->redirect([
                'view', 'codigoBarra' => $model->CodigoBarra,
                'condicionImprimir' => ''
            ]);
        } else {

            return $this->renderAjax('_modalDetalleRegistro', [
                'model' => $model,
                'articulos' => $detalleArticulo
            ]);
        }
    }

    public function actionEditModalDetalle($IdRegistro, $IdDetalleRegistro)
    {
        $model = $this->findDetalleModel($IdDetalleRegistro);
        $clasificacion = Yii::$app->db->createCommand("SELECT Clasificacion FROM REGISTRO WHERE IdRegistro = $IdRegistro")->queryOne();
        $detalleArticulo = $this->getArticuloDetalle($clasificacion["Clasificacion"]);
        if ($model->load($this->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();
            try {

                if ($model->save()) {
                    $transaction->commit();
                } else {
                    throw new Exception(implode("<br />", \yii\helpers\ArrayHelper::getColumn($model->getErrors(), 0, false)));
                }
            } catch (Exception $e) {
                $transaction->rollBack();
                return $this->redirect(['view', 'IdRegistro' => $IdRegistro]);
            }
            Yii::$app->session->setFlash('info', "Registro actualizado exitosamente.");
            return $this->redirect(['view', 'IdRegistro' => $IdRegistro]);
        } else {

            return $this->render('_modalDetalleRegistro', [
                'model' => $model,
                'articulos' => $detalleArticulo
            ]);
        }
    }

    public function actionFinalizarProduccion($fecha)
    {
        if (!isset($_SESSION['user'])) {
            return $this->redirect(Url::to(Yii::$app->request->baseUrl . '/index.php?r=site/login'));
        }

        $produccion = RegistroModel::find()
            ->select("SUM(Libras) as Libras, MesaOrigen, FechaCreacion, BodegaActual")
            ->where("Estado = 'PROCESO' AND IdTipoRegistro = 1 AND Costo IS NOT NULL AND FechaCreacion = '$fecha'")
            ->groupBy(['Libras', 'MesaOrigen', 'FechaCreacion', 'BodegaActual'])
            ->all();

        if (count($produccion) == 0) {
            Yii::$app->session->setFlash('info', "No existen registros de producccion para finalizar.");
            $this->redirect(['index']);
        }

        foreach ($produccion as $mesa) {

            $codigosMesa = TrabajoMesaModel::find()->where(['NumeroMesa' => $mesa->MesaOrigen, 'Fecha' => $fecha])->all();

            $sumaLibrasCodigosMesa = $this->obtenerSumaLibrasCodigosMesa($codigosMesa);
            $sumaCostosCodigosMesa = $this->obtenerSumaCostosCodigosMesa($codigosMesa);

            $restanteAnterior = $this->obtenerRestanteLibrasCostoMesa($mesa->MesaOrigen);
            $existeProduccion = RegistroModel::find()
                ->where(['FechaCreacion' => $fecha, 'MesaOrigen' => $mesa->MesaOrigen, 'Estado' => 'FINALIZADO'])
                ->all();

            if ($restanteAnterior && count($existeProduccion) > 0) {
                $precioUnitario = $restanteAnterior->Libras / $restanteAnterior->Costo;

                $restante = $restanteAnterior->Libras - $mesa->Libras;
                $costoRestante = $restante * $precioUnitario;

                //Restante positivo -- nuevo restante
                Yii::$app->db->createCommand(
                    "INSERT INTO [BODEGA].[dbo].[TRABAJOMESA_RESTANTE] 
                (NumeroMesa, Fecha, Libras, Bodega, CreateDate, Costo) 
                VALUES 
                ('$mesa->MesaOrigen', '$fecha', $restante, '$mesa->BodegaActual', getdate(), $costoRestante)"
                )->execute();

                //Restante viejo
                Yii::$app->db->createCommand(
                    "INSERT INTO [BODEGA].[dbo].[TRABAJOMESA_RESTANTE] 
                (NumeroMesa, Fecha, Libras, Bodega, CreateDate, Costo) 
                VALUES 
                ('$mesa->MesaOrigen', '$fecha', -$restanteAnterior->Libras, '$mesa->BodegaActual', getdate(), -$restanteAnterior->Costo)"
                )->execute();
                continue;
            }

            if (!$restanteAnterior) {
                $restante = $sumaLibrasCodigosMesa - $mesa->Libras;
                $precioUnitario = $sumaCostosCodigosMesa / $sumaLibrasCodigosMesa;
                $costoRestante = $restante * $precioUnitario;

                //Restante positivo -- nuevo restante
                Yii::$app->db->createCommand(
                    "INSERT INTO [BODEGA].[dbo].[TRABAJOMESA_RESTANTE] 
                    (NumeroMesa, Fecha, Libras, Bodega, CreateDate, Costo) 
                    VALUES 
                    ('$mesa->MesaOrigen', '$fecha', $restante, '$mesa->BodegaActual', getdate(), $costoRestante)"
                )->execute();
            }

            $precioUnitario = ($sumaCostosCodigosMesa + $restanteAnterior->Libras) / $sumaLibrasCodigosMesa;

            $restante = $sumaLibrasCodigosMesa + $restanteAnterior->Libras - $mesa->Libras;
            $costoRestante = $restante * $precioUnitario;

            //Restante positivo -- nuevo restante
            Yii::$app->db->createCommand(
                "INSERT INTO [BODEGA].[dbo].[TRABAJOMESA_RESTANTE] 
                (NumeroMesa, Fecha, Libras, Bodega, CreateDate, Costo) 
                VALUES 
                ('$mesa->MesaOrigen', '$fecha', $restante, '$mesa->BodegaActual', getdate(), $costoRestante)"
            )->execute();

            //Restante viejo
            Yii::$app->db->createCommand(
                "INSERT INTO [BODEGA].[dbo].[TRABAJOMESA_RESTANTE] 
                (NumeroMesa, Fecha, Libras, Bodega, CreateDate, Costo) 
                VALUES 
                ('$mesa->MesaOrigen', '$fecha', -$restanteAnterior->Libras, '$mesa->BodegaActual', getdate(), -$restanteAnterior->Costo)"
            )->execute();
        }

        $codigosProduccion = $produccion = RegistroModel::find()
            ->where("Estado = 'PROCESO' AND IdTipoRegistro = 1 AND Costo IS NOT NULL AND FechaCreacion = '$fecha'")
            ->all();
        $consecutivo = $this->obtenerConsecutivo('PRODUCCION');

        $this->crearDocumentoInv($consecutivo);
        foreach ($codigosProduccion as $registro) {
            $this->actualizarRegistroProduccionREGISTRO($registro->CodigoBarra);
            $this->crearRegistroFinalizadoTRANSACCION($registro->CodigoBarra, $registro->BodegaActual, $consecutivo);
            $this->crearLineaDocumentoInvEntrada($consecutivo, $registro->Articulo, $registro->BodegaActual, $registro->Costo);
        }
        $this->actualizarConsecutivo();
        Yii::$app->session->setFlash('success', "Produccion finalizada exitosamente.");

        $this->redirect(['index']);
    }

    /**
     * Cambia el estado de un registro de produccion a eliminado
     * @param string $codigoBarra el codigo de barra del registro
     * @return view Retorna al index de los registros de produccion
     */
    public function actionDeleteRegistro($codigoBarra)
    {
        $model = $this->findRegistroModel($codigoBarra);
        $model->Estado = 'ELIMINADO';
        $model->save();
        return $this->redirect(['index']);
    }

    /**
     * Elimina un registro en [BODEGA].[dbo].[DETALLEREGISTRO] a partir de un codigo de barra y y registro de produccion
     * @return view Retorna al view del registro de produccion
     */
    public function actionDeleteDetalle($IdDetalleRegistro, $codigoBarra)
    {
        Yii::$app->db->createCommand("DELETE FROM DETALLEREGISTRO WHERE IdDetalleRegistro = $IdDetalleRegistro")->execute();

        return $this->redirect(['view', 'codigoBarra' => $codigoBarra, 'condicionImprimir' => '']);
    }

    /**
     * Encuentra un registro a partir de un codigo de barra
     * @param string $codigoBarra El codigo de barra del registro a buscar
     * @return RegistroModel El modelo encontrado
     * @throws NotFoundHttpException Si no se encuentra ningun registro
     */
    protected function findRegistroModel($codigoBarra)
    {
        if (($model = RegistroModel::findOne(['CodigoBarra' => $codigoBarra])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Encuentra un registro a partir de un id
     * @param string $IdDetalleRegistro El id del registro a buscar
     * @return DetalleRegistroModel El modelo encontrado
     * @throws NotFoundHttpException Si no se encuentra ningun registro
     */
    protected function findDetalleModel($IdDetalleRegistro)
    {
        if (($model = DetalleRegistroModel::findOne(['IdDetalleRegistro' => $IdDetalleRegistro])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Crea un nuevo registro de produccion en la tabla [BODEGA].[dbo].[REGISTRO]
     * 
     */
    public function crearRegistroProduccionREGISTRO($codigoBarra, $articulo, $descripcion, $clasificacion, $libras, $unidades, $idTipoEmpaque, $empacadoPor, $producidoPor, $bodega, $observaciones, $costo, $fecha, $sesion, $empresaDestino, $mesa)
    {
        Yii::$app->db->createCommand(
            "INSERT INTO REGISTRO 
            (
            CodigoBarra, Articulo, Descripcion, Clasificacion, Libras, Unidades, IdTipoEmpaque,
            IdUbicacion, EmpacadoPor, ProducidoPor, BodegaCreacion, BodegaActual, Observaciones, UsuarioCreacion, 
            Estado, Costo, FechaCreacion, Sesion, IdTipoRegistro, EmpresaDestino, MesaOrigen) 
            VALUES 
            (
                '$codigoBarra', '$articulo', '$descripcion', '$clasificacion', $libras, $unidades, $idTipoEmpaque, 
                1, '$empacadoPor', '$producidoPor', '$bodega', '$bodega', '$observaciones', '" . $_SESSION['user'] . "', 
                'PROCESO', $costo, '$fecha', $sesion, 1, '$empresaDestino', $mesa
            )"
        )->execute();
    }

    /**
     * Obtiene el consecutivo a trabajar para registrar una mesa
     * @return string Un consecutivo el cual registrara todo movimiento de una mesa en inventario
     */
    public function obtenerConsecutivo($consecutivo)
    {
        $getConsecutivo =  $this->SoftlandConn()->createCommand("SELECT SIGUIENTE_CONSEC 
        FROM [SOFTLAND].[CONINV].[CONSECUTIVO_CI] WHERE CONSECUTIVO = '" . $consecutivo . "'")->queryOne();

        return $getConsecutivo['SIGUIENTE_CONSEC'];
    }

    /**
     * Crear un documento de inventario a partir de un consecutivo
     * @param int $consecutivo El consecutivo actual 
     * @throws PDOException Si algun campo/valor proporcionado estan fuera de los campos/valores esperados
     */
    public function crearDocumentoInv($consecutivo)
    {
        $this->SoftlandConn()->createCommand(
            "INSERT INTO [SOFTLAND].[CONINV].[DOCUMENTO_INV]
            (PAQUETE_INVENTARIO, DOCUMENTO_INV, CONSECUTIVO , REFERENCIA, FECHA_HOR_CREACION, FECHA_DOCUMENTO,
            SELECCIONADO, USUARIO, APROBADO) 
            VALUES 
            (
                '" . Yii::$app->session->get('paquete') . "',
                '" . $consecutivo . "',
                'PRODUCCION',
                'PRODUCCION del dia " .  date("Y-m-d") . "',
                '" . date("Y-m-d H:i:s") . "',
                '" . date("Y-m-d H:i:s") . "',
                'N',
                '" . Yii::$app->session->get('user') . "',
                'N'
            )"
        )->execute();
    }

    /**
     * Cuenta las lineas actuales de un documento de inventario, y le suma 1
     * @param int $consecutivo El consecutivo actual 
     * @return int El numero de la siguiente linea de inventario
     * @throws PDOException Si algun campo/valor proporcionado estan fuera de los campos/valores esperados
     */
    public function obtenerNumeroLineaDocInv($consecutivo)
    {
        $ultimaLinea = $this->SoftlandConn()->createCommand("SELECT COUNT(LINEA_DOC_INV) AS linea
        FROM [SOFTLAND].[CONINV].[LINEA_DOC_INV] 
        WHERE DOCUMENTO_INV = '" . $consecutivo . "'")->queryOne();

        return $ultimaLinea['linea'] + 1;
    }

    public function actualizarRegistroProduccionREGISTRO($codigoBarra)
    {
        $model = $this->findRegistroModel($codigoBarra);
        $model->Estado = 'FINALIZADO';
        $model->save();
    }

    /**
     * Tomando un codigo de barra, crea un registro en la tabla [BODEGA].[dbo].[TRANSACCION] para representar una salida del inventario
     * @param string $codigoBarra un codigo de barra de un registro existente
     * @param string $bodega la bodega en la que estaba registrado el fardo trabajado
     * @param string $consecutivo un codigo de consecutivo el cual enlaza el inventario con la transaccion
     */
    public function crearRegistroFinalizadoTRANSACCION($codigoBarra, $bodega, $consecutivo)
    {
        Yii::$app->db->createCommand(
            "INSERT INTO [BODEGA].[dbo].[TRANSACCION] 
            (CodigoBarra, IdTipoTransaccion, Fecha, Bodega, Naturaleza, Estado,
            UsuarioCreacion, FechaCreacion, Documento_Inv) 
            VALUES 
            ('" . $codigoBarra . "', 2, '" . date("Y-m-d") . "', '" . $bodega . "', 'E', 'F',
            '" . Yii::$app->session->get('user') . "', '" . date("Y-m-d H:i:s") . "', '" . $consecutivo . "')"
        )->execute();
    }

    /**
     * Crea una linea de entrada de inventario la cual representa un registro de articulo barril/fardo
     * @param int $consecutivo El consecutivo actual 
     * @throws PDOException Si algun campo/valor proporcionado estan fuera de los campos/valores esperados
     */
    public function crearLineaDocumentoInvEntrada($consecutivo, $articulo, $bodega, $costo)
    {
        Yii::$app->db->createCommand(
            "INSERT INTO [SOFTLAND].[CONINV].[LINEA_DOC_INV] 
            (PAQUETE_INVENTARIO, DOCUMENTO_INV, LINEA_DOC_INV, 
            AJUSTE_CONFIG, ARTICULO, BODEGA, TIPO, SUBTIPO, SUBSUBTIPO, CANTIDAD,
            COSTO_TOTAL_LOCAL, COSTO_TOTAL_DOLAR, PRECIO_TOTAL_LOCAL, PRECIO_TOTAL_DOLAR, COSTO_TOTAL_LOCAL_COMP, COSTO_TOTAL_DOLAR_COMP)
            VALUES 
            (
                '" . Yii::$app->session->get('paquete') . "', '" . $consecutivo . "', " . $this->obtenerNumeroLineaDocInv($consecutivo) . ",
                '~OO~', '" . $articulo . "', '" . $bodega . "', 'O', 'D', 'L', 1,
                $costo, 0, 0, 0, 0, 0
            )"
        )->execute();
    }

    /**
     * Toma el consecutivo actual de PRODUCCION de [SOFTLAND].[CONINV].[CONSECUTIVO_CI] para luego aumentarlo en 1
     * @return string el nuevo consecutivo 
     */
    public function crearSiguienteConsecutivo()
    {
        $getConsecutivoCode =  $this->SoftlandConn()->createCommand("SELECT CONSECUTIVO, SIGUIENTE_CONSEC 
        FROM [SOFTLAND].[CONINV].[CONSECUTIVO_CI] WHERE CONSECUTIVO = 'PRODUCCION'")->queryOne();

        $consecutivoCode = explode("-", $getConsecutivoCode['SIGUIENTE_CONSEC']);
        $ultimoConsecutivoCode  = intval($consecutivoCode[1]);
        $newConsecutivo = $consecutivoCode[0] . '-';

        if ($ultimoConsecutivoCode >= 0 && $ultimoConsecutivoCode < 9) {
            $newConsecutivo = $newConsecutivo . '000000' . ($ultimoConsecutivoCode + 1);
        } else if ($ultimoConsecutivoCode >= 9 && $ultimoConsecutivoCode < 99) {
            $newConsecutivo = $newConsecutivo . '00000' . ($ultimoConsecutivoCode + 1);
        } else if ($ultimoConsecutivoCode >= 99 && $ultimoConsecutivoCode < 999) {
            $newConsecutivo = $newConsecutivo . '0000' . ($ultimoConsecutivoCode + 1);
        } else if ($ultimoConsecutivoCode >= 999 && $ultimoConsecutivoCode < 9999) {
            $newConsecutivo = $newConsecutivo . '000' . ($ultimoConsecutivoCode + 1);
        } else if ($ultimoConsecutivoCode >= 9999 && $ultimoConsecutivoCode < 99999) {
            $newConsecutivo = $newConsecutivo . '00' . ($ultimoConsecutivoCode + 1);
        } else if ($ultimoConsecutivoCode >= 99999 && $ultimoConsecutivoCode < 999999) {
            $newConsecutivo = $newConsecutivo . '0' . ($ultimoConsecutivoCode + 1);
        } else if ($ultimoConsecutivoCode >= 999999 && $ultimoConsecutivoCode < 9999999) {
            $newConsecutivo = $newConsecutivo . ($ultimoConsecutivoCode + 1);
        }

        return $newConsecutivo;
    }

    /**
     * Actualiza el consecutivo PRODUCCION de [SOFTLAND].[CONINV].[CONSECUTIVO_CI]
     */
    public function actualizarConsecutivo()
    {
        $this->SoftlandConn()->createCommand("UPDATE [SOFTLAND].[CONINV].[CONSECUTIVO_CI] 
        SET SIGUIENTE_CONSEC = '" . $this->crearSiguienteConsecutivo() . "' WHERE CONSECUTIVO = 'PRODUCCION'")->execute();
    }

    /**
     * Obtiene todas las bodegas disponibles segun el esquema del usuario logeado
     * @return array un array con las bodegas encontradas
     */
    public function getBodegasByEsquema()
    {
        $bodegas = $this->SoftlandConn()->createCommand(
            "SELECT BODEGA, NOMBRE 
            FROM [SOFTLAND]." . $_SESSION['esquema'] . ".[BODEGA] 
            WHERE bodega LIKE '%00'"
        )->queryAll();

        foreach ($bodegas as $index => $bodega) {
            $bodegas[$index]["NOMBRE"] = $bodegas[$index]["BODEGA"] . ' - ' . $bodegas[$index]["NOMBRE"];
        }
        return $bodegas;
    }

    /**
     * Obtiene las diferentes clasificaciones que poseen los articulos
     * @return array un array que contiene todas las clasificaciones
     */
    public function getClasificacion()
    {
        $articulos = $this->SoftlandConn()->createCommand(
            "SELECT DISTINCT CLASIFICACION_2 FROM 
            [SOFTLAND].[CONINV].[ARTICULO] 
            WHERE activo = 'S'
            AND CLASIFICACION_2 NOT LIKE 'RIPIO'"
        )->queryAll();
        return $articulos;
    }

    protected function getArticulosByEsquema($clasificacion)
    {
        $articulos = $this->SoftlandConn()->createCommand(
            "SELECT ARTICULO, DESCRIPCION, CLASIFICACION_2 AS ARTICULO_DESCRIPCION 
            FROM [SOFTLAND]." . $_SESSION['esquema'] . ".[ARTICULO] 
            WHERE CLASIFICACION_2 = '$clasificacion' AND ACTIVO = 'S' 
            AND ARTICULO LIKE '%P%'"
        )->queryAll();
        foreach ($articulos as $index => $articulo) {
            $articulos[$index]["ARTICULO_DESCRIPCION"] = $articulo['ARTICULO'] . ' - ' . $articulo["DESCRIPCION"];
        }
        return $articulos;
    }

    /**
     * Toma un array y lo concatena cada valor dentro de un string, todo separado por una coma (,)
     * @param array $array un array que contiene varios valores
     * @return string un string con los valores concatenados
     */
    public function fromArrayToString($array)
    {

        $string = '';
        foreach ($array as $index => $val) {
            if ($index == 0) {
                $string .= $val;
            } else {
                $string .= ', ' . $val;
            }
        }

        return $string;
    }

    /**
     * Obtiene la sumatoria de las libras de cada codigo de barra que se trabaja en una mesa de produccion
     * @param object $codigosMesa es un objeto de Yii2 el cual trae los codigos de barra que estan en una mesa de produccion
     * @return int una sumatoria de libras
     */
    public function obtenerSumaLibrasCodigosMesa($codigosMesa)
    {
        $sumaLibrasCodigosMesa = 0;
        foreach ($codigosMesa as $codigoBarra) {
            $obtenerLibrasPorCodigo = RegistroModel::find()->where(['CodigoBarra' => $codigoBarra->CodigoBarra])->sum('Libras');
            $sumaLibrasCodigosMesa += $obtenerLibrasPorCodigo;
        }

        return $sumaLibrasCodigosMesa;
    }

    /**
     * Obtiene la sumatoria de los costos de cada codigo de barra que se trabaja en una mesa de produccion
     * @param object $codigosMesa es un objeto de Yii2 el cual trae los codigos de barra que estan en una mesa de produccion
     * @return int una sumatoria de costos
     */
    public function obtenerSumaCostosCodigosMesa($codigosMesa)
    {
        $sumaCostosCodigosMesa = 0;
        foreach ($codigosMesa as $codigoBarra) {
            $obtenerCostosPorCodigo = RegistroModel::find()->where(['CodigoBarra' => $codigoBarra->CodigoBarra])->sum('Costo');
            $sumaCostosCodigosMesa += $obtenerCostosPorCodigo;
        }

        return $sumaCostosCodigosMesa;
    }

    public function obtenerRestanteLibrasCostoMesa($mesaOrigen)
    {
        $restante = TrabajoMesaRestanteModel::find()
            ->select('NumeroMesa, SUM(Libras) as Libras, SUM(Costo) as Costo')
            ->where(['NumeroMesa' => $mesaOrigen])
            ->groupBy(['NumeroMesa'])
            ->one();

        return $restante;
    }

    public function obtenerProduccionFinalizadaDiaria($fecha, $mesaOrigen)
    {
        $produccionFinalizada = RegistroModel::find()
            ->where(['FechaCreacion' => $fecha, 'MesaOrigen' => $mesaOrigen, 'Estado' => 'FINALIZADO'])
            ->all();

        return $produccionFinalizada;
    }

    public function generateBarCode($fecha)
    {
        $fechaActual = date('m.d.y');
        $fechaComoEntero = strtotime($fechaActual);
        $anio = date("y", $fechaComoEntero);
        $mes = date("m", $fechaComoEntero);
        $dia = date("d", $fechaComoEntero);
        $DesdeLetra = "a";
        $HastaLetra = "z";
        $letraAleatoria = chr(rand(ord($DesdeLetra), ord($HastaLetra)));
        $letraMayuscula = strtoupper($letraAleatoria);

        $devuelve = $this->getSessionDataRegisterDay($fecha) + 1;
        if ($devuelve >= 0 && $devuelve < 10) {
            $numero = "00$devuelve";
        } else if ($devuelve > 9 && $devuelve < 100) {
            $numero = "0$devuelve";
        } else {
            $numero = $devuelve;
        }

        $codigoBarra = ("" . $letraMayuscula . "" . "P" . $dia . "" . $mes . "" . $numero . "" . $anio
        );

        return $codigoBarra;
    }

    public function getSessionDataRegisterDay($fecha)
    {
        $sesionDataDay = Yii::$app->db->createCommand("SELECT ISNULL(MAX(Sesion), 0) AS maximo 
        FROM REGISTRO 
        WHERE FechaCreacion = '" . $fecha . "' AND IdTipoRegistro = 1")->queryOne();

        return $sesionDataDay['maximo'] + 1;
    }

    protected function getArticuloDetalle($clasificacion)
    {
        $query = "SELECT "
            . $_SESSION['esquema'] . ".ARTICULO.ARTICULO, "
            . $_SESSION['esquema'] . ".ARTICULO.DESCRIPCION, "
            . $_SESSION['esquema'] . ".ARTICULO_PRECIO.PRECIO, "
            . $_SESSION['esquema'] . ".ARTICULO.ACTIVO
        FROM " . $_SESSION['esquema'] . ".ARTICULO 
        INNER JOIN " . $_SESSION['esquema'] . ".ARTICULO_PRECIO 
        ON " . $_SESSION['esquema'] . ".ARTICULO.ARTICULO = " . $_SESSION['esquema'] . ".ARTICULO_PRECIO.ARTICULO
        WHERE (" . $_SESSION['esquema'] . ".ARTICULO.ACTIVO = 'S') 
        AND (" . $_SESSION['esquema'] . ".ARTICULO.UNIDAD_ALMACEN = '59') 
        AND (" . $_SESSION['esquema'] . ".ARTICULO.CLASIFICACION_2 = '$clasificacion') 
        AND (" . $_SESSION['esquema'] . ".ARTICULO.USA_LOTES = 'S') 
        AND (" . $_SESSION['esquema'] . ".ARTICULO_PRECIO.NIVEL_PRECIO = 'REGULAR') 
        ORDER BY " . $_SESSION['esquema'] . ".ARTICULO.DESCRIPCION, " . $_SESSION['esquema'] . ".ARTICULO.ARTICULO";

        $articulos = $this->SoftlandConn()->createCommand($query)->queryAll();
        foreach ($articulos as $index => $articulo) {
            $articulos[$index]["ACTIVO"] = $articulo['ARTICULO'] . ' - ' . $articulo["DESCRIPCION"];
        }

        return $articulos;
    }

    /**
     * Funcion para evaluar valores dentro de un array
     * @throws Array Retorna los valores con etiquetas <pre></pre>
     */
    public function printArrays($datos)
    {
        echo "<pre>";
        print_r($datos);
        echo "</pre>";
    }
}
