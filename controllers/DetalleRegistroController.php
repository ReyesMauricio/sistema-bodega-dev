<?php
namespace app\controllers;

use app\models\CNYCENTER_ARTICULO_PROVEEDOR;
use app\models\CNYCENTERARTICULO;
use app\models\CONINVARTICULO;
use app\models\DetalleRegistroModel;
use app\models\RegistroModel;
use app\models\TrabajoMesaModel;
use app\models\TrabajoMesaRestanteModel;
use app\modelsSearch\DetalleRegistroModelSearch;
use app\modelsSearch\RegistroModelSearch;
use Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use yii\web\Controller;
use Yii;
use yii\base\DynamicModel;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Url;

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

    public function actionObtenerArticulosCompanies()
    {
        $CONINV = $this->SoftlandConn()->createCommand(
            "SELECT ARTICULO, DESCRIPCION, CLASIFICACION_1, CLASIFICACION_2, CLASIFICACION_3 
            FROM [PRUEBAS].[CONINV].[ARTICULO]
            ORDER BY ARTICULO ASC"
        )->queryAll();

        if ($this->request->post()) {
            $empresaInfo = $this->SoftlandConn()->createCommand(
                "SELECT ARTICULO, DESCRIPCION, CLASIFICACION_1, CLASIFICACION_2, CLASIFICACION_3 
                FROM [PRUEBAS].[CONINV].[ARTICULO]
                WHERE ARTICULO NOT IN (SELECT ARTICULO FROM [PRUEBAS]." . $_POST["empresa"] . ".[ARTICULO])
                ORDER BY ARTICULO ASC"
            )->queryAll();
            return $this->render("obtener-articulos-companies", [
                'coninv' => $CONINV,
                'empresa' => $_POST["empresa"],
                'empresaInfo' => $empresaInfo
            ]);
        } else {
            return $this->render("obtener-articulos-companies", [
                'coninv' => $CONINV,
            ]);
        }
    }

    public function actionCopiarArticulos($empresa)
    {
        $canny = " 
        CASE
        WHEN CLASIFICACION_3 = 'BISUTERIA' 
            OR CLASIFICACION_3 = 'MISCELANEA' 
            OR CLASIFICACION_2 = 'OTROS' 
            OR CLASIFICACION_2 = 'GANCHOS'
            OR CLASIFICACION_2 = 'RIPIO'
        THEN 'MISC'
        WHEN CLASIFICACION_3 = 'MOCHILAS'
        THEN 'MOCH'
        WHEN CLASIFICACION_3 = 'INSUMOS'
        THEN 'INSU'
        WHEN CLASIFICACION_2 = 'CARTERAS'
        THEN 'CART'
        WHEN CLASIFICACION_2 = 'JUGUETES'
        THEN 'JUGU'
        WHEN CLASIFICACION_2 = 'ZAPATOS'
        THEN 'ZAPA'
        WHEN CLASIFICACION_2 = 'CINCHOS'
        THEN 'CINC'
        WHEN CLASIFICACION_2 = 'GORRAS'
        THEN 'GORR'
        WHEN CLASIFICACION_2 = 'ROPA'
        THEN 'ROPA'
        ELSE ARTICULO_CUENTA
        END AS
        ";
        $cnycenter = "SELECT [ARTICULO], [PLANTILLA_SERIE], [DESCRIPCION], 
        CASE 
        WHEN CLASIFICACION_1 = 'ROPAS'
        THEN 'ROPA'
        WHEN CLASIFICACION_1 = 'CARTERAS'
        THEN 'CARTERASYM'
        ELSE CLASIFICACION_1
        END AS
        [CLASIFICACION_1],
        CASE 
        WHEN CLASIFICACION_2 = 'JUGUETES'
        THEN 'JUGUETESYP'
        WHEN CLASIFICACION_2 = 'CARTERAS'
        THEN 'CARTERASYM'
        ELSE CLASIFICACION_2
        END AS
        [CLASIFICACION_2], 
        CASE 
        WHEN CLASIFICACION_2 = 'JUGUETES'
        THEN 'JUGUETESYP'
        WHEN CLASIFICACION_2 = 'CARTERAS'
        THEN 'CARTERASYM'
        WHEN CLASIFICACION_3 = 'PERCHEROS'
        THEN 'OTROS'
        WHEN CLASIFICACION_3 = 'MISCELANEA'
        THEN 'MISCELANEAS'
        WHEN CLASIFICACION_3 = 'GANCHO'
        THEN 'GANCHOS'
        WHEN CLASIFICACION_3 = 'OTRO'
        THEN 'OTROS'
        ELSE CLASIFICACION_3
        END AS
        [CLASIFICACION_3],
        [CLASIFICACION_4], [CLASIFICACION_5], [CLASIFICACION_6], [FACTOR_CONVER_1], [FACTOR_CONVER_2], [FACTOR_CONVER_3], 
        [FACTOR_CONVER_4], [FACTOR_CONVER_5], [FACTOR_CONVER_6], [TIPO], [ORIGEN_CORP], [PESO_NETO], [PESO_BRUTO], [VOLUMEN], [BULTOS], 
        CASE
        WHEN CLASIFICACION_3 = 'BISUTERIA' 
            OR CLASIFICACION_3 = 'MISCELANEA' 
            OR CLASIFICACION_2 = 'OTROS' 
            OR CLASIFICACION_2 = 'GANCHOS'
            OR CLASIFICACION_2 = 'RIPIO'
        THEN 'MISC'
        WHEN CLASIFICACION_3 = 'MOCHILAS'
        THEN 'MOCH'
        WHEN CLASIFICACION_3 = 'INSUMOS'
        THEN 'INSU'
        WHEN CLASIFICACION_2 = 'CARTERAS'
        THEN 'CART'
        WHEN CLASIFICACION_2 = 'JUGUETES'
        THEN 'JUGU'
        WHEN CLASIFICACION_2 = 'ZAPATOS'
        THEN 'ZAPA'
        WHEN CLASIFICACION_2 = 'CINCHOS'
        THEN 'CINC'
        WHEN CLASIFICACION_2 = 'GORRAS'
        THEN 'GORR'
        WHEN CLASIFICACION_2 = 'ROPA'
        THEN 'ROPA'
        ELSE ARTICULO_CUENTA
        END AS [ARTICULO_CUENTA]";
        echo "EN COPIAR ARTICULOS CON LA EMPRESA $empresa";
        $this->SoftlandConn()->createCommand("
        INSERT INTO PRUEBAS.$empresa.ARTICULO ([ARTICULO], [PLANTILLA_SERIE], [DESCRIPCION], [CLASIFICACION_1],
         [CLASIFICACION_2], [CLASIFICACION_3], 
        [CLASIFICACION_4], [CLASIFICACION_5], [CLASIFICACION_6], [FACTOR_CONVER_1], [FACTOR_CONVER_2], [FACTOR_CONVER_3], 
        [FACTOR_CONVER_4], [FACTOR_CONVER_5], [FACTOR_CONVER_6], [TIPO], [ORIGEN_CORP], [PESO_NETO], [PESO_BRUTO], [VOLUMEN], [BULTOS], 
        [ARTICULO_CUENTA], [IMPUESTO], [FACTOR_EMPAQUE], [FACTOR_VENTA], [EXISTENCIA_MINIMA], [EXISTENCIA_MAXIMA], [PUNTO_DE_REORDEN], 
        [COSTO_FISCAL], [COSTO_COMPARATIVO], [COSTO_PROM_LOC], [COSTO_PROM_DOL], [COSTO_STD_LOC], [COSTO_STD_DOL], [COSTO_ULT_LOC], 
        [COSTO_ULT_DOL], [PRECIO_BASE_LOCAL], [PRECIO_BASE_DOLAR], [ULTIMA_SALIDA], [ULTIMO_MOVIMIENTO], [ULTIMO_INGRESO], [ULTIMO_INVENTARIO],  
        [CLASE_ABC], [FRECUENCIA_CONTEO], [CODIGO_BARRAS_VENT], [CODIGO_BARRAS_INVT], [ACTIVO], [USA_LOTES], [OBLIGA_CUARENTENA],  
        [MIN_VIDA_COMPRA], [MIN_VIDA_CONSUMO], [MIN_VIDA_VENTA], [VIDA_UTIL_PROM], [DIAS_CUARENTENA], [PROVEEDOR], [ARTICULO_DEL_PROV],  
        [ORDEN_MINIMA], [PLAZO_REABAST], [LOTE_MULTIPLO], [NOTAS], [UTILIZADO_MANUFACT], [USUARIO_CREACION], [FCH_HORA_CREACION],  
        [USUARIO_ULT_MODIF], [FCH_HORA_ULT_MODIF], [USA_NUMEROS_SERIE], [MODALIDAD_INV_FIS], [TIPO_COD_BARRA_DET], [TIPO_COD_BARRA_ALM],  
        [USA_REGLAS_LOCALES], [UNIDAD_ALMACEN], [UNIDAD_EMPAQUE], [UNIDAD_VENTA], [PERECEDERO], [GTIN], [MANUFACTURADOR], [CODIGO_RETENCION], 
        [RETENCION_VENTA], [RETENCION_COMPRA], [MODELO_RETENCION], [ESTILO], [TALLA], [COLOR], [TIPO_COSTO], [ARTICULO_ENVASE], [ES_ENVASE],  
        [USA_CONTROL_ENVASE], [COSTO_PROM_COMPARATIVO_LOC], [COSTO_PROM_COMPARATIVO_DOLAR], [COSTO_PROM_ULTIMO_LOC], [COSTO_PROM_ULTIMO_DOL],  
        [UTILIZADO_EN_CONTRATOS], [VALIDA_CANT_FASE_PY], [OBLIGA_INCLUIR_FASE_PY], [ES_IMPUESTO], [TIPO_DOC_IVA], [NIT], [CANASTA_BASICA],  
        [ES_OTRO_CARGO], [SERVICIO_MEDICO], [ITEM_HACIENDA], [CODIGO_HACIENDA], [ITEM_HACIENDA_COMPRA], [TIENDA], [TIPO_EXISTENCIA],  
        [CATALOGO_EXISTENCIA], [TIPO_DETRACCION_VENTA], [CODIGO_DETRACCION_VENTA], [TIPO_DETRACCION_COMPRA], [CODIGO_DETRACCION_COMPRA],  
        [CALC_PERCEP], [PORC_PERCEP], [SUGIERE_MIN], [U_CLAVE_UNIDAD], [U_CLAVE_PROD_SERV], [U_CLAVE_PS_PUB], [TIPO_VENTA],  [NoteExistsFlag],  
        [ES_INAFECTO], [PARTIDA_ARANCELARIA])
        SELECT [ARTICULO], [PLANTILLA_SERIE], [DESCRIPCION], 
        CASE 
        WHEN CLASIFICACION_1 = 'ROPAS'
        THEN 'ROPA'
        WHEN CLASIFICACION_1 = 'CARTERAS'
        THEN 'CARTERASYM'
        ELSE CLASIFICACION_1
        END AS
        [CLASIFICACION_1],
        CASE 
        WHEN CLASIFICACION_2 = 'JUGUETES'
        THEN 'JUGUETESYP'
        WHEN CLASIFICACION_2 = 'CARTERAS'
        THEN 'CARTERASYM'
        ELSE CLASIFICACION_2
        END AS
        [CLASIFICACION_2], 
        CASE 
        WHEN CLASIFICACION_2 = 'JUGUETES'
        THEN 'JUGUETESYP'
        WHEN CLASIFICACION_2 = 'CARTERAS'
        THEN 'CARTERASYM'
        WHEN CLASIFICACION_3 = 'PERCHEROS'
        THEN 'OTROS'
        WHEN CLASIFICACION_3 = 'MISCELANEA'
        THEN 'MISCELANEAS'
        WHEN CLASIFICACION_3 = 'GANCHO'
        THEN 'GANCHOS'
        WHEN CLASIFICACION_3 = 'OTRO'
        THEN 'OTROS'
        ELSE CLASIFICACION_3
        END AS
        [CLASIFICACION_3],
        [CLASIFICACION_4], [CLASIFICACION_5], [CLASIFICACION_6], [FACTOR_CONVER_1], [FACTOR_CONVER_2], [FACTOR_CONVER_3], 
        [FACTOR_CONVER_4], [FACTOR_CONVER_5], [FACTOR_CONVER_6], [TIPO], [ORIGEN_CORP], [PESO_NETO], [PESO_BRUTO], [VOLUMEN], [BULTOS], 
        CASE
        WHEN CLASIFICACION_3 = 'BISUTERIA' 
            OR CLASIFICACION_3 = 'MISCELANEA' 
            OR CLASIFICACION_2 = 'OTROS' 
            OR CLASIFICACION_2 = 'GANCHOS'
            OR CLASIFICACION_2 = 'RIPIO'
        THEN 'MISC'
        WHEN CLASIFICACION_3 = 'MOCHILAS'
        THEN 'MOCH'
        WHEN CLASIFICACION_3 = 'INSUMOS'
        THEN 'INSU'
        WHEN CLASIFICACION_2 = 'CARTERAS'
        THEN 'CART'
        WHEN CLASIFICACION_2 = 'JUGUETES'
        THEN 'JUGU'
        WHEN CLASIFICACION_2 = 'ZAPATOS'
        THEN 'ZAPA'
        WHEN CLASIFICACION_2 = 'CINCHOS'
        THEN 'CINC'
        WHEN CLASIFICACION_2 = 'GORRAS'
        THEN 'GORR'
        WHEN CLASIFICACION_2 = 'ROPA'
        THEN 'ROPA'
        ELSE ARTICULO_CUENTA
        END AS [ARTICULO_CUENTA], [IMPUESTO], [FACTOR_EMPAQUE], [FACTOR_VENTA], [EXISTENCIA_MINIMA], [EXISTENCIA_MAXIMA], [PUNTO_DE_REORDEN], 
        [COSTO_FISCAL], [COSTO_COMPARATIVO], [COSTO_PROM_LOC], [COSTO_PROM_DOL], [COSTO_STD_LOC], [COSTO_STD_DOL], [COSTO_ULT_LOC], 
        [COSTO_ULT_DOL], [PRECIO_BASE_LOCAL], [PRECIO_BASE_DOLAR], [ULTIMA_SALIDA], [ULTIMO_MOVIMIENTO], [ULTIMO_INGRESO], [ULTIMO_INVENTARIO],  
        [CLASE_ABC], [FRECUENCIA_CONTEO], [CODIGO_BARRAS_VENT], [CODIGO_BARRAS_INVT], [ACTIVO], [USA_LOTES], [OBLIGA_CUARENTENA],  
        [MIN_VIDA_COMPRA], [MIN_VIDA_CONSUMO], [MIN_VIDA_VENTA], [VIDA_UTIL_PROM], [DIAS_CUARENTENA], [PROVEEDOR], [ARTICULO_DEL_PROV],  
        [ORDEN_MINIMA], [PLAZO_REABAST], [LOTE_MULTIPLO], [NOTAS], [UTILIZADO_MANUFACT], [USUARIO_CREACION], [FCH_HORA_CREACION],  
        [USUARIO_ULT_MODIF], [FCH_HORA_ULT_MODIF], [USA_NUMEROS_SERIE], [MODALIDAD_INV_FIS], [TIPO_COD_BARRA_DET], [TIPO_COD_BARRA_ALM],  
        [USA_REGLAS_LOCALES], [UNIDAD_ALMACEN], [UNIDAD_EMPAQUE], [UNIDAD_VENTA], [PERECEDERO], [GTIN], [MANUFACTURADOR], [CODIGO_RETENCION], 
        [RETENCION_VENTA], [RETENCION_COMPRA], [MODELO_RETENCION], [ESTILO], [TALLA], [COLOR], [TIPO_COSTO], [ARTICULO_ENVASE], [ES_ENVASE],  
        [USA_CONTROL_ENVASE], [COSTO_PROM_COMPARATIVO_LOC], [COSTO_PROM_COMPARATIVO_DOLAR], [COSTO_PROM_ULTIMO_LOC], [COSTO_PROM_ULTIMO_DOL],  
        [UTILIZADO_EN_CONTRATOS], [VALIDA_CANT_FASE_PY], [OBLIGA_INCLUIR_FASE_PY], [ES_IMPUESTO], [TIPO_DOC_IVA], [NIT], [CANASTA_BASICA],  
        [ES_OTRO_CARGO], [SERVICIO_MEDICO], [ITEM_HACIENDA], [CODIGO_HACIENDA], [ITEM_HACIENDA_COMPRA], [TIENDA], [TIPO_EXISTENCIA],  
        [CATALOGO_EXISTENCIA], [TIPO_DETRACCION_VENTA], [CODIGO_DETRACCION_VENTA], [TIPO_DETRACCION_COMPRA], [CODIGO_DETRACCION_COMPRA],  
        [CALC_PERCEP], [PORC_PERCEP], [SUGIERE_MIN], [U_CLAVE_UNIDAD], [U_CLAVE_PROD_SERV], [U_CLAVE_PS_PUB], [TIPO_VENTA],  [NoteExistsFlag],  
        [ES_INAFECTO], [PARTIDA_ARANCELARIA] FROM [PRUEBAS].[CONINV].[ARTICULO]
        WHERE ARTICULO NOT IN (SELECT ARTICULO FROM [PRUEBAS].$empresa.[ARTICULO])
        ORDER BY ARTICULO ASC")->execute();
        echo "Query ejecutado";
    }

    /**
     * Muestra una lista de todos los registros de produccion
     * @return string|array|view Retorna una vista donde se mostraran todos los registros encontrados
     */
    public function actionIndex()
    {
        if (!Yii::$app->session->get('user')) {
            return $this->redirect(Url::to(Yii::$app->request->baseUrl . '/index.php?r=site/login'));
        }
        
        $dataProvider = RegistroModel::find()->where("IdTipoRegistro = 1 AND FechaCreacion ='" . date("Y-m-d") . "'")->orderBy(['CreateDate' => SORT_DESC])->all();
        return $this->render('index', [
            'dataProvider' => $dataProvider
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
        $dataProvider->pagination->pageSize = 100;
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

    public function actionConsultar()
    {
        $model = $this->crearModelReporte();
        return $this->render('consultar-produccion', [
            'model' => $model
        ]);
    }

    public function actionMostrarConsultaProduccion($fecha)
    {
        $codigos = RegistroModel::find()
            ->where(
                "IdTipoRegistro = 1 
                AND Estado NOT LIKE 'ELIMINADO'
            AND FechaCreacion = '$fecha'
            AND UsuarioCreacion NOT LIKE '%AUDITORIA%'"
            )->orderBy(' Articulo, CodigoBarra')
            ->all();

        if (!$codigos) {
            return $this->renderAjax('mostrar-produccion-fecha', [
                'transaccion' => "<h1>No existe asignacion en esta fecha: $fecha</h1>",
                'fecha' => $fecha
            ]);
        }

        return $this->renderAjax('mostrar-produccion-fecha', [
            'codigos' => $codigos,
            'fecha' => $fecha
        ]);
    }

    /**
     * Crea un modelo dinamico que captura la informacion para generar reportes
     * @return DynamicModel un modelo dinamico
     */
    public function crearModelReporte()
    {
        $reporte = new DynamicModel([
            'reporte', 'fecha', 'persona', 'mesa'
        ]);
        $reporte->setAttributeLabels([
            'reporte' => 'Tipo de reporte',
            'fecha' => 'Fecha estimada',
            'persona' => 'Persona',
            'mesa' => 'Mesa',
        ]);
        $reporte->addRule(['reporte', 'fecha', 'persona'], 'required');
        return $reporte;
    }

    public function actionCreateDetalle()
    {
        if (!isset($_SESSION['user'])) {
            return $this->redirect(Url::to(Yii::$app->request->baseUrl . '/index.php?r=site/login'));
        }

        /*$finalizado = $this->validarFinalizarDia(date("Y-m-d"));
        if ($finalizado != 'FINALIZADO') {
            Yii::$app->session->setFlash('warning', "<i class='fas fa-exclamation-triangle'></i> &nbsp;&nbsp;<b>$finalizado!</b>");
            return $this->redirect(['site/index']);
        }*/

        $registro = new RegistroModel;
        $clasificacion = $this->getClasificacion();

        if ($registro->load($this->request->post()) && isset($registro->Articulo)) {
            $articulos = $this->getArticulosByEsquema($registro->Clasificacion);

            $articulo = $registro->Articulo;
            $registro->Articulo = explode(" -", $articulo)[0];

            $descripionString = '';
            foreach (explode(" -", $articulo) as $index => $descripcion) {
                if ($index == 0) {
                    continue;
                }
                $descripionString .= $descripcion;
            }
            $registro->Descripcion = $descripionString;

            $empacadores = $registro->EmpacadoPor;
            $productores = $registro->ProducidoPor;
            $registro->EmpacadoPor = $this->fromArrayToString($empacadores);
            $registro->ProducidoPor = $this->fromArrayToString($productores);
            $registro->BodegaActual = 'SM00';

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $sesionDataDay = $this->getSessionDataRegisterDay(date("Y-m-d "));
                $codigoBarra = $this->generateBarCode($registro->FechaCreacion);
                $this->crearRegistroProduccionREGISTRO($codigoBarra, $registro->Articulo, $registro->Descripcion, $registro->Clasificacion, $registro->Libras, $registro->Unidades, $registro->IdTipoEmpaque, $registro->EmpacadoPor, $registro->ProducidoPor, $registro->BodegaActual, $registro->Observaciones, 0, $registro->FechaCreacion, $sesionDataDay, $registro->EmpresaDestino, $registro->MesaOrigen);
                $this->crearRegistroProduccionTRANSACCION($codigoBarra, $registro->BodegaActual, $registro->FechaCreacion);
                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('warning', "Error: " . $e->getMessage());
                return $this->redirect(['index']);
            }

            Yii::$app->session->setFlash('success', "Registro creado existosamente.");
            return $this->redirect(['view', 'codigoBarra' => $codigoBarra, 'condicionImprimir' => '']);
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
    public function actionUpdate($codigoBarra)
    {
        $registro = $this->findRegistroModel($codigoBarra);
        $registro->EmpacadoPor = explode(", ", $registro->EmpacadoPor);
        $registro->ProducidoPor = explode(", ", $registro->ProducidoPor);
        $articuloAntiguo = $registro->Articulo;
        $descripcionVieja = $registro->Descripcion;

        $clasificacion = $this->getClasificacion();
        $articulos = $this->getArticulosByEsquema('');

        if ($registro->load($this->request->post())) {

            if ($_POST["Articulo"] == '') {
                $registro->Articulo = $articuloAntiguo;
                $registro->Descripcion = $descripcionVieja;
            } else {
                $registro->Articulo = explode(" -", $_POST["Articulo"])[0];
                $descripionString = '';
                foreach (explode(" -", $_POST['Articulo']) as $index => $descripcion) {
                    if ($index == 0) {
                        continue;
                    }
                    $descripionString .= $descripcion;
                }
                $registro->Descripcion = $descripionString;
            }

            $empacadores = $registro->EmpacadoPor;
            $productores = $registro->ProducidoPor;
            $registro->EmpacadoPor = $this->fromArrayToString($empacadores);
            $registro->ProducidoPor = $this->fromArrayToString($productores);
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $registro->save();
                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollBack();
                return $this->redirect(['index']);
            }
            Yii::$app->session->setFlash('warning', "Registro actualizado existosamente.");
            return $this->redirect(['view', 'codigoBarra' => $codigoBarra, 'condicionImprimir' => '']);
        } else {

            return $this->render('update-registro-form', [
                'registro' => $registro,
                'clasificacion' => $clasificacion,
                'articulos' => $articulos
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
            return $this->redirect(['view', 'codigoBarra' => $model->idRegistro->CodigoBarra, 'condicionImprimir' => '']);
        } else {

            return $this->render('_modalDetalleRegistro', [
                'model' => $model,
                'articulos' => $detalleArticulo
            ]);
        }
    }

    public function actionPrevisualizarFinalizar($fecha)
    {
        $articulos = RegistroModel::find()
            ->select(["CONCAT (Articulo, ' - ' ,Descripcion) Articulo, (SUM(Libras) / COUNT(CodigoBarra)) AS Libras, MAX(Libras) AS CodigoBarra, MIN(Libras) as Costo, SUM(Libras) AS Descripcion"])
            ->where(
                "Estado = 'PROCESO' 
                AND IdTipoRegistro = 1 
                AND FechaCreacion = '$fecha'
                "
            )
            ->groupBy('Articulo, Descripcion')
            ->orderBy('Articulo')
            ->all();

        $codigosProduccion = RegistroModel::find()
            ->where(
                "Estado = 'PROCESO' 
                AND IdTipoRegistro = 1 
                AND FechaCreacion = '$fecha'
                "
            )
            ->all();

        if (!$articulos) {
            return $this->renderAjax('previsualizar-finalizar-produccion', [
                'transaccion' => "<h1>No existe produccion pendiente de finalizar en esta fecha: $fecha </h1>",
                'fecha' => $fecha,
                'conteo' => count($codigosProduccion)
            ]);
        }
        return $this->renderAjax('previsualizar-finalizar-produccion', [
            'articulos' => $articulos,
            'fecha' => $fecha,
            'conteo' => count($codigosProduccion),
        ]);
    }

    public function actionFinalizarProduccion($fecha)
    {
        if (!isset($_SESSION['user'])) {
            return $this->redirect(Url::to(Yii::$app->request->baseUrl . '/index.php?r=site/login'));
        }

        $codigosProduccion = RegistroModel::find()
            ->where(
                "Estado = 'PROCESO' 
                AND IdTipoRegistro = 1 
                AND FechaCreacion = '$fecha'
                "
            )
            ->all();

        foreach ($codigosProduccion as $registro) {

            $transaction = Yii::$app->db->beginTransaction();
            try {
                if ($registro->Articulo != 'BA030') {

                    $this->actualizarRegistroProduccionREGISTRO($registro->CodigoBarra);
                    $this->crearRegistroFinalizadoTRANSACCION($registro->CodigoBarra, $registro->BodegaCreacion, $fecha);
                }
                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('warning', "Error: " . $e->getMessage());
                return $this->redirect(['index']);
            }
        }
        Yii::$app->session->setFlash('success', "<b><i class='fa fa-info-circle'></i> Produccion finalizada exitosamente.</b>");
        return $this->redirect(['index']);
    }

    public function actionFinalizarCosto($fecha)
    {
        if (!isset($_SESSION['user'])) {
            return $this->redirect(Url::to(Yii::$app->request->baseUrl . '/index.php?r=site/login'));
        }

        $asignacion = $this->obtenerAsignacionPorFecha($fecha);
        $consecutivoCONINV = $this->obtenerConsecutivo('PRODUCCION', 'CONINV');
        $consecutivoCNYCENTER = $this->obtenerConsecutivo('PRODUCCION', 'CNYCENTER');

        $codigosProduccion = RegistroModel::find()
            ->where(
                "Estado = 'PROCESO' 
            AND IdTipoRegistro = 1 
            AND MesaOrigen IS NOT NULL 
            AND FechaCreacion = '$fecha'
            AND UsuarioCreacion NOT LIKE '%AUDITORIA%'"
            )
            ->all();

        $sumaLibrasProduccion = RegistroModel::find()->where(
            "Estado = 'PROCESO' 
            AND IdTipoRegistro = 1 
            AND MesaOrigen IS NOT NULL 
            AND FechaCreacion = '$fecha'
            AND UsuarioCreacion NOT LIKE '%AUDITORIA%'"
        )->sum('Libras');

        if ($asignacion->Libras < $sumaLibrasProduccion) {
            Yii::$app->session->setFlash('info', "<b><i class='fas fa-exclamation-triangle'></i> La asignacion es menor a lo producido!</b>");
            return $this->redirect(['index']);
        }

        $precioUnitario = $asignacion->Costo / $asignacion->Libras;

        $costo = 0;
        $bodega = '';

        $this->crearDocumentoInv($consecutivoCONINV, 'CONINV', 'PRODUCCION');
        $this->crearDocumentoInv($consecutivoCNYCENTER, 'CNYCENTER', 'PRODUCCION');

        foreach ($codigosProduccion as $registro) {
            if ($registro->Clasificacion != 'RIPIO') {
                $bodega = $registro->BodegaActual;
                $this->actualizarRegistroCostoREGISTRO($registro->CodigoBarra, $consecutivoCONINV, ($registro->Libras * $precioUnitario));
                $this->actualizarRegistroFinalizadoTRANSACCION($registro->CodigoBarra, $registro->BodegaCreacion, $consecutivoCONINV, $fecha);
                $this->crearLineaDocumentoInvEntrada($consecutivoCONINV, $registro->Articulo, $registro->BodegaCreacion, ($registro->Libras * $precioUnitario), 'CONINV');
                $this->crearLineaDocumentoInvEntrada($consecutivoCNYCENTER, $registro->Articulo, $registro->BodegaCreacion, ($registro->Libras * $precioUnitario), 'CNYCENTER');
            }
            $costo += ($registro->Libras * $precioUnitario);
        }
        $this->crearRegistroTRABAJOMESA(-$sumaLibrasProduccion, -$costo, $consecutivoCONINV, $fecha, $bodega);
        $this->actualizarConsecutivo('CONINV', 'PRODUCCION');
        $this->actualizarConsecutivo('CNYCENTER', 'PRODUCCION');


        Yii::$app->session->setFlash('success', "Produccion finalizada exitosamente.");
        return $this->redirect(['index']);
    }

    public function actionFinalizarDiaProduccion($fecha)
    {
        $newFecha = $fecha;
        $finalizado = Yii::$app->db->createCommand(
            "SELECT * FROM BODEGA.dbo.FINALIZACION_DIA WHERE Fecha = '$fecha'"
        )->queryOne();

        if ($finalizado["Estado"] == 'FINALIZADO') {
            Yii::$app->session->setFlash('warning', "<b>DIA $fecha YA SE ENCUENTRA FINALIZADO!</b>");
            return $this->redirect(['site/index']);
        }

        $asignacionTotalInicial = TrabajoMesaModel::find()->select("SUM(Libras) Libras, SUM(Costo) Costo, Fecha")
            ->where("Fecha = '$fecha' AND Libras > 0 AND Costo > 0")
            ->groupBy('Fecha')->one();

        if (!$asignacionTotalInicial) {
            $asignacionTotalInicial = TrabajoMesaModel::find()->distinct()->select("Fecha")
                ->where("Fecha < '$fecha'")
                ->orderBy(["Fecha" => SORT_DESC])->one();
            $fecha = $asignacionTotalInicial["Fecha"];

            $asignacionTotalInicial = TrabajoMesaModel::find()->select("SUM(LIBRAS) Libras, SUM(Costo) Costo, Fecha")
                ->where(['Fecha' => $fecha])
                ->groupBy('Fecha')->one();
        }

        $asignacionTotalPorArticulo = Yii::$app->db->createCommand(
            "SELECT R.Articulo, R.Clasificacion, SUM(R.Libras) as Libras, COUNT(R.Articulo) as Cantidad, R.Descripcion
            FROM TRANSACCION T
            INNER JOIN REGISTRO R
            ON T.CodigoBarra = R.CodigoBarra
            WHERE T.Documento_Inv LIKE '%CON%' AND Fecha = '$fecha'
            GROUP BY R.Articulo, R.Clasificacion, R.Descripcion
            ORDER BY SUM(R.Libras) DESC"
        )->queryAll();

        $produccionTotalDiaria = RegistroModel::find()->select("Articulo, SUM(Libras) AS Libras, SUM(Costo) as Costo, FechaCreacion")
            ->where("MesaOrigen IS NOT NULL AND MesaOrigen > 5 AND FechaCreacion = '$newFecha'")
            ->groupBy('Articulo, FechaCreacion')->all();

        $transaction = $this->SoftlandConn()->beginTransaction();
        try {
            $consecutivo = $this->obtenerConsecutivo('REC-LBS', 'CNYCENTER');
            $this->crearDocumentoInv($consecutivo, 'CNYCENTER', 'REC-LBS');
            $sumaCostoProduccion = 0;
            foreach ($produccionTotalDiaria as $produccion) {
                $this->crearLineaDocumentoInvEntrada($consecutivo, $produccion->Articulo, 'SM00', $produccion->Costo, 'CNYCENTER');
                $sumaCostoProduccion += $produccion->Costo;
            }
            $restante = $asignacionTotalInicial["Costo"] - $sumaCostoProduccion;
            $this->crearLineaDocumentoInvEntrada($consecutivo, $asignacionTotalPorArticulo[0]["Articulo"], 'SM00', $restante, 'CNYCENTER');
            $this->actualizarConsecutivo('CNYCENTER', 'REC-LBS');

            Yii::$app->db->createCommand(
                "UPDATE BODEGA.dbo.FINALIZACION_DIA SET Estado = 'FINALIZADO' WHERE Fecha = '$newFecha'"
            )->execute();

            $transaction->commit();
            Yii::$app->session->setFlash('success', "<i class='fas fa-thumbs-up'></i> &nbsp;&nbsp;Dia finalizado con exito!");
            return $this->redirect(['index']);
        } catch (Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('warning', "Error: " . $e->getMessage());
            return $this->redirect(['site/index']);
        }
    }
    //Accion para agregar un nuevo articulo en el equema CNYCENTER
    public function actionCrearArticulo()
    {
        $articulo = $this->crearModelArticulo();
        $clasificaciones = CNYCENTERARTICULO::find()
            ->distinct()
            ->select('CLASIFICACION_2')
            ->where("CLASIFICACION_2 NOT LIKE 'RIPIO' AND CLASIFICACION_2 IS NOT NULL")
            ->asArray()
            ->all();
        if ($articulo->load($this->request->post())) {
            $existe = CNYCENTERARTICULO::find()->where("DESCRIPCION = '$articulo->descripcion'")->all();

            if (count($existe) > 0) {
                Yii::$app->session->setFlash('warning', "Articulo con descripcion <b>$articulo->descripcion</b> ya existe!");
                return $this->redirect(['index']);
            }
            $ultimoArticulo = CNYCENTERARTICULO::find()
                ->where("ARTICULO LIKE '%$articulo->tipo%' AND ARTICULO NOT LIKE 'P900'")
                ->orderBy(['ARTICULO' => SORT_DESC])
                ->one();
            $articulo->clasificacion = $articulo->clasificacion == 'CARTERAS' ? 'CARTERASYM' : $articulo->clasificacion;
            $articulo->clasificacion = $articulo->clasificacion == 'JUGUETES' ? 'JUGUETESYP' : $articulo->clasificacion;
            $articuloCuenta = $this->articuloCuentaCNYCENTER($articulo->clasificacion);
            $siguienteArticulo = $this->siguienteArticulo($articulo->tipo, $ultimoArticulo->ARTICULO);
            //$articuloCuenta = $this->articuloCuentaCONINV($articulo->clasificacion);
            $transaction = Yii::$app->db2->beginTransaction();
            try {
                $newArticulo = new CNYCENTERARTICULO();
                    $newArticulo->ARTICULO = $siguienteArticulo;
                    $newArticulo->DESCRIPCION = $articulo->descripcion;
                    $newArticulo->CLASIFICACION_1 = $newArticulo->CLASIFICACION_2 = $newArticulo->CLASIFICACION_3 = $articulo->clasificacion;
                    $newArticulo->TIPO = $newArticulo->ORIGEN_CORP = 'T';
                    $newArticulo->PESO_NETO = $newArticulo->PESO_BRUTO = $newArticulo->VOLUMEN = $newArticulo->BULTOS = 0;
                    $newArticulo->ARTICULO_CUENTA = $articuloCuenta;
                    $newArticulo->IMPUESTO = 'IVA';
                    $newArticulo->FACTOR_EMPAQUE = $newArticulo->FACTOR_VENTA = 1;
                    $newArticulo->EXISTENCIA_MINIMA = $newArticulo->EXISTENCIA_MAXIMA = $newArticulo->PUNTO_DE_REORDEN = 0;
                    $newArticulo->COSTO_FISCAL = 'P';
                    $newArticulo->COSTO_COMPARATIVO = 'L';
                    $newArticulo->COSTO_PROM_LOC = $newArticulo->COSTO_PROM_DOL = $newArticulo->COSTO_STD_LOC = $newArticulo->COSTO_STD_DOL = $newArticulo->COSTO_ULT_LOC = $newArticulo->COSTO_ULT_DOL = $newArticulo->PRECIO_BASE_LOCAL = $newArticulo->PRECIO_BASE_DOLAR = 0;
                    $newArticulo->ULTIMA_SALIDA = $newArticulo->ULTIMO_MOVIMIENTO = $newArticulo->ULTIMO_INGRESO = $newArticulo->ULTIMO_INVENTARIO = '1980-01-01 00:00:00.000';
                    $newArticulo->CLASE_ABC = 'A';
                    $newArticulo->FRECUENCIA_CONTEO = 0;
                    $newArticulo->ACTIVO = 'S';
                    $newArticulo->USA_LOTES = 'N';
                    $newArticulo->OBLIGA_CUARENTENA = 'N';
                    $newArticulo->MIN_VIDA_COMPRA = $newArticulo->MIN_VIDA_CONSUMO = $newArticulo->MIN_VIDA_VENTA = $newArticulo->VIDA_UTIL_PROM = $newArticulo->DIAS_CUARENTENA = $newArticulo->ORDEN_MINIMA = $newArticulo->PLAZO_REABAST = $newArticulo->LOTE_MULTIPLO = 0;
                    $newArticulo->UTILIZADO_MANUFACT = $newArticulo->USA_NUMEROS_SERIE = 'N';
                    $newArticulo->UNIDAD_ALMACEN = $newArticulo->UNIDAD_EMPAQUE = $newArticulo->UNIDAD_VENTA = '59';
                    $newArticulo->PERECEDERO = 'N';
                    $newArticulo->TIPO_COSTO = 'A';
                    $newArticulo->ES_ENVASE = $newArticulo->USA_CONTROL_ENVASE = 'N';
                    $newArticulo->COSTO_PROM_COMPARATIVO_LOC = $newArticulo->COSTO_PROM_COMPARATIVO_DOLAR = $newArticulo->COSTO_PROM_ULTIMO_LOC = $newArticulo->COSTO_PROM_ULTIMO_DOL = 0;
                    $newArticulo->UTILIZADO_EN_CONTRATOS = $newArticulo->VALIDA_CANT_FASE_PY = $newArticulo->OBLIGA_INCLUIR_FASE_PY = $newArticulo->ES_IMPUESTO = $newArticulo->CANASTA_BASICA = $newArticulo->ES_OTRO_CARGO = $newArticulo->SERVICIO_MEDICO = 'N';
                    $newArticulo->TIENDA = 'No Definido';
                    $newArticulo->CALC_PERCEP = $newArticulo->SUGIERE_MIN = $newArticulo->ES_INAFECTO = 'N';
                if (!$newArticulo->save()) {
                    throw new Exception(implode("<br />", \yii\helpers\ArrayHelper::getColumn($newArticulo->getErrors(), 0, false)));
                }
                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('warning', "Error: " . $e->getMessage());
                return $this->redirect(['index']);
            }
                try {
                    $articleExist = $this->ifArticleExist($siguienteArticulo);
                    //Verificamos si ya existe un articulo igual dentro de la tabla [CNYCENTER].[ARTICULO_PROVEEDOR]
                    if($articleExist){
                        Yii::$app->session->setFlash('warning', "Error: "."El articulo ya existe dentro de la tabla ARTICULO_PROVEEDOR");
                        return $this->redirect(['index']);
                    }else{
                        $transaction = Yii::$app->db2->beginTransaction();
                        $newArticulo = new CNYCENTER_ARTICULO_PROVEEDOR();
                        $newArticulo->ARTICULO = $siguienteArticulo;
                        $newArticulo->PROVEEDOR = 'EX0002';
                        $newArticulo->CODIGO_CATALOGO = $siguienteArticulo;
                        $newArticulo->LOTE_MINIMO = $newArticulo->LOTE_ESTANDAR = $newArticulo->MULTIPLO_COMPRA = $newArticulo->CANT_ECONOMICA_COM = 1;
                        $newArticulo->PESO_MINIMO_ORDEN = 0;
                        $newArticulo->UNIDAD_MEDIDA_COMP = 'UND';
                        $newArticulo->FACTOR_CONVERSION = $newArticulo->PLAZO_REABASTECIMI = 1;
                        $newArticulo->PORC_AJUSTE_COSTO = 1;
                        $newArticulo->PAIS = 'ESA';
                        $newArticulo->TIPO = 'P';
                        $newArticulo->IMPUESTO = '0';
                        if (!$newArticulo->save()) {
                            throw new Exception(implode("<br />", \yii\helpers\ArrayHelper::getColumn($newArticulo->getErrors(), 0, false)));
                        }
                        $transaction->commit();
                        Yii::$app->session->setFlash('primary', "Articulo creado exitosamente!");
                        return $this->redirect(['index']);
                    }
                    
                } catch (Exception $e) {
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('warning', "Error: " . $e->getMessage());
                    return $this->redirect(['index']);
                }
        } else {
            return $this->render('crear-articulo-produccion', [
                'articulo' => $articulo,
                'clasificaciones' => $clasificaciones
            ]);
        }
    }
    //Verifica si se intenta insertar dos veces el mismo articulo dentro de la tabla [CNYCENTER].[ARTICULO_PROVEEDOR]
    public function ifArticleExist($articulo){
        $existe = Yii::$app->db2->createCommand(
            "SELECT COUNT(*) FROM CNYCENTER.ARTICULO_PROVEEDOR
            WHERE ARTICULO = :articulo"
        )->bindValue(':articulo', $articulo)->queryScalar();
    
        return $existe > 0;
    }
    
    

    // public function actionCrearArticulo()
    // {
    //     $articulo = $this->crearModelArticulo();
    //     $clasificaciones = CONINVARTICULO::find()
    //         ->distinct()
    //         ->select('CLASIFICACION_2')
    //         ->where("CLASIFICACION_2 NOT LIKE 'RIPIO' AND CLASIFICACION_2 IS NOT NULL")
    //         ->asArray()
    //         ->all();
    //     if ($articulo->load($this->request->post())) {
    //         $existe = CONINVARTICULO::find()->where("DESCRIPCION = '$articulo->descripcion'")->all();

    //         if (count($existe) > 0) {
    //             Yii::$app->session->setFlash('warning', "Articulo con descripcion <b>$articulo->descripcion</b> ya existe!");
    //             return $this->redirect(['index']);
    //         }
    //         $ultimoArticulo = CONINVARTICULO::find()
    //             ->where("ARTICULO LIKE '%$articulo->tipo%' AND ARTICULO NOT LIKE 'P900'")
    //             ->orderBy(['ARTICULO' => SORT_DESC])
    //             ->one();

    //         $siguienteArticulo = $this->siguienteArticulo($articulo->tipo, $ultimoArticulo->ARTICULO);
    //         $articuloCuenta = $this->articuloCuentaCONINV($articulo->clasificacion);
    //         $transaction = Yii::$app->db2->beginTransaction();
    //         try {
    //             $newArticulo = new CONINVARTICULO();
    //             $newArticulo->ARTICULO = $siguienteArticulo;
    //             $newArticulo->DESCRIPCION = $articulo->descripcion;
    //             $newArticulo->CLASIFICACION_1 = $newArticulo->CLASIFICACION_2 = $newArticulo->CLASIFICACION_3 = $articulo->clasificacion;
    //             $newArticulo->TIPO = $newArticulo->ORIGEN_CORP = 'T';
    //             $newArticulo->PESO_NETO = $newArticulo->PESO_BRUTO = $newArticulo->VOLUMEN = $newArticulo->BULTOS = 0;
    //             $newArticulo->ARTICULO_CUENTA = $articuloCuenta;
    //             $newArticulo->IMPUESTO = 'IVA';
    //             $newArticulo->FACTOR_EMPAQUE = $newArticulo->FACTOR_VENTA = 1;
    //             $newArticulo->EXISTENCIA_MINIMA = $newArticulo->EXISTENCIA_MAXIMA = $newArticulo->PUNTO_DE_REORDEN = 0;
    //             $newArticulo->COSTO_FISCAL = 'P';
    //             $newArticulo->COSTO_COMPARATIVO = 'L';
    //             $newArticulo->COSTO_PROM_LOC = $newArticulo->COSTO_PROM_DOL = $newArticulo->COSTO_STD_LOC = $newArticulo->COSTO_STD_DOL = $newArticulo->COSTO_ULT_LOC = $newArticulo->COSTO_ULT_DOL = $newArticulo->PRECIO_BASE_LOCAL = $newArticulo->PRECIO_BASE_DOLAR = 0;
    //             $newArticulo->ULTIMA_SALIDA = $newArticulo->ULTIMO_MOVIMIENTO = $newArticulo->ULTIMO_INGRESO = $newArticulo->ULTIMO_INVENTARIO = '1980-01-01 00:00:00.000';
    //             $newArticulo->CLASE_ABC = 'A';
    //             $newArticulo->FRECUENCIA_CONTEO = 0;
    //             $newArticulo->ACTIVO = 'S';
    //             $newArticulo->USA_LOTES = 'N';
    //             $newArticulo->OBLIGA_CUARENTENA = 'N';
    //             $newArticulo->MIN_VIDA_COMPRA = $newArticulo->MIN_VIDA_CONSUMO = $newArticulo->MIN_VIDA_VENTA = $newArticulo->VIDA_UTIL_PROM = $newArticulo->DIAS_CUARENTENA = $newArticulo->ORDEN_MINIMA = $newArticulo->PLAZO_REABAST = $newArticulo->LOTE_MULTIPLO = 0;
    //             $newArticulo->UTILIZADO_MANUFACT = $newArticulo->USA_NUMEROS_SERIE = 'N';
    //             $newArticulo->UNIDAD_ALMACEN = $newArticulo->UNIDAD_EMPAQUE = $newArticulo->UNIDAD_VENTA = '59';
    //             $newArticulo->PERECEDERO = 'N';
    //             $newArticulo->TIPO_COSTO = 'A';
    //             $newArticulo->ES_ENVASE = $newArticulo->USA_CONTROL_ENVASE = 'N';
    //             $newArticulo->COSTO_PROM_COMPARATIVO_LOC = $newArticulo->COSTO_PROM_COMPARATIVO_DOLAR = $newArticulo->COSTO_PROM_ULTIMO_LOC = $newArticulo->COSTO_PROM_ULTIMO_DOL = 0;
    //             $newArticulo->UTILIZADO_EN_CONTRATOS = $newArticulo->VALIDA_CANT_FASE_PY = $newArticulo->OBLIGA_INCLUIR_FASE_PY = $newArticulo->ES_IMPUESTO = $newArticulo->CANASTA_BASICA = $newArticulo->ES_OTRO_CARGO = $newArticulo->SERVICIO_MEDICO = 'N';
    //             $newArticulo->TIENDA = 'No Definido';
    //             $newArticulo->CALC_PERCEP = $newArticulo->SUGIERE_MIN = $newArticulo->ES_INAFECTO = 'N';
    //             if (!$newArticulo->save()) {
    //                 throw new Exception(implode("<br />", \yii\helpers\ArrayHelper::getColumn($newArticulo->getErrors(), 0, false)));
    //             }
    //             $transaction->commit();
    //         } catch (Exception $e) {
    //             $transaction->rollBack();
    //             Yii::$app->session->setFlash('warning', "Error: " . $e->getMessage());
    //             return $this->redirect(['index']);
    //         }

    //         if ($articulo->tipo == 'FARD0') {

    //             $articulo->clasificacion = $articulo->clasificacion == 'CARTERAS' ? 'CARTERASYM' : $articulo->clasificacion;
    //             $articulo->clasificacion = $articulo->clasificacion == 'JUGUETES' ? 'JUGUETESYP' : $articulo->clasificacion;
    //             $articuloCuenta = $this->articuloCuentaCNYCENTER($articulo->clasificacion);

    //             $transaction = Yii::$app->db2->beginTransaction();
    //             try {
    //                 $newArticulo = new CNYCENTERARTICULO();
    //                 $newArticulo->ARTICULO = $siguienteArticulo;
    //                 $newArticulo->DESCRIPCION = $articulo->descripcion;
    //                 $newArticulo->CLASIFICACION_1 = $newArticulo->CLASIFICACION_2 = $newArticulo->CLASIFICACION_3 = $articulo->clasificacion;
    //                 $newArticulo->TIPO = $newArticulo->ORIGEN_CORP = 'T';
    //                 $newArticulo->PESO_NETO = $newArticulo->PESO_BRUTO = $newArticulo->VOLUMEN = $newArticulo->BULTOS = 0;
    //                 $newArticulo->ARTICULO_CUENTA = $articuloCuenta;
    //                 $newArticulo->IMPUESTO = 'IVA';
    //                 $newArticulo->FACTOR_EMPAQUE = $newArticulo->FACTOR_VENTA = 1;
    //                 $newArticulo->EXISTENCIA_MINIMA = $newArticulo->EXISTENCIA_MAXIMA = $newArticulo->PUNTO_DE_REORDEN = 0;
    //                 $newArticulo->COSTO_FISCAL = 'P';
    //                 $newArticulo->COSTO_COMPARATIVO = 'L';
    //                 $newArticulo->COSTO_PROM_LOC = $newArticulo->COSTO_PROM_DOL = $newArticulo->COSTO_STD_LOC = $newArticulo->COSTO_STD_DOL = $newArticulo->COSTO_ULT_LOC = $newArticulo->COSTO_ULT_DOL = $newArticulo->PRECIO_BASE_LOCAL = $newArticulo->PRECIO_BASE_DOLAR = 0;
    //                 $newArticulo->ULTIMA_SALIDA = $newArticulo->ULTIMO_MOVIMIENTO = $newArticulo->ULTIMO_INGRESO = $newArticulo->ULTIMO_INVENTARIO = '1980-01-01 00:00:00.000';
    //                 $newArticulo->CLASE_ABC = 'A';
    //                 $newArticulo->FRECUENCIA_CONTEO = 0;
    //                 $newArticulo->ACTIVO = 'S';
    //                 $newArticulo->USA_LOTES = 'N';
    //                 $newArticulo->OBLIGA_CUARENTENA = 'N';
    //                 $newArticulo->MIN_VIDA_COMPRA = $newArticulo->MIN_VIDA_CONSUMO = $newArticulo->MIN_VIDA_VENTA = $newArticulo->VIDA_UTIL_PROM = $newArticulo->DIAS_CUARENTENA = $newArticulo->ORDEN_MINIMA = $newArticulo->PLAZO_REABAST = $newArticulo->LOTE_MULTIPLO = 0;
    //                 $newArticulo->UTILIZADO_MANUFACT = $newArticulo->USA_NUMEROS_SERIE = 'N';
    //                 $newArticulo->UNIDAD_ALMACEN = $newArticulo->UNIDAD_EMPAQUE = $newArticulo->UNIDAD_VENTA = '59';
    //                 $newArticulo->PERECEDERO = 'N';
    //                 $newArticulo->TIPO_COSTO = 'A';
    //                 $newArticulo->ES_ENVASE = $newArticulo->USA_CONTROL_ENVASE = 'N';
    //                 $newArticulo->COSTO_PROM_COMPARATIVO_LOC = $newArticulo->COSTO_PROM_COMPARATIVO_DOLAR = $newArticulo->COSTO_PROM_ULTIMO_LOC = $newArticulo->COSTO_PROM_ULTIMO_DOL = 0;
    //                 $newArticulo->UTILIZADO_EN_CONTRATOS = $newArticulo->VALIDA_CANT_FASE_PY = $newArticulo->OBLIGA_INCLUIR_FASE_PY = $newArticulo->ES_IMPUESTO = $newArticulo->CANASTA_BASICA = $newArticulo->ES_OTRO_CARGO = $newArticulo->SERVICIO_MEDICO = 'N';
    //                 $newArticulo->TIENDA = 'No Definido';
    //                 $newArticulo->CALC_PERCEP = $newArticulo->SUGIERE_MIN = $newArticulo->ES_INAFECTO = 'N';
    //                 if (!$newArticulo->save()) {
    //                     throw new Exception(implode("<br />", \yii\helpers\ArrayHelper::getColumn($newArticulo->getErrors(), 0, false)));
    //                 }
    //                 $transaction->commit();
    //             } catch (Exception $e) {
    //                 $transaction->rollBack();
    //                 Yii::$app->session->setFlash('warning', "Error: " . $e->getMessage());
    //                 return $this->redirect(['index']);
    //             }

    //             $transaction = Yii::$app->db2->beginTransaction();
    //             try {
    //                 $newArticulo = new CNYCENTER_ARTICULO_PROVEEDOR();
    //                 $newArticulo->ARTICULO = $siguienteArticulo;
    //                 $newArticulo->PROVEEDOR = 'EX0002';
    //                 $newArticulo->CODIGO_CATALOGO = $siguienteArticulo;
    //                 $newArticulo->LOTE_MINIMO = $newArticulo->LOTE_ESTANDAR = $newArticulo->MULTIPLO_COMPRA = $newArticulo->CANT_ECONOMICA_COM = 1;
    //                 $newArticulo->PESO_MINIMO_ORDEN = 0;
    //                 $newArticulo->UNIDAD_MEDIDA_COMP = 'UND';
    //                 $newArticulo->FACTOR_CONVERSION = $newArticulo->PLAZO_REABASTECIMI = 1;
    //                 $newArticulo->PORC_AJUSTE_COSTO = 1;
    //                 $newArticulo->PAIS = 'ESA';
    //                 $newArticulo->TIPO = 'P';
    //                 $newArticulo->IMPUESTO = '0';
    //                 if (!$newArticulo->save()) {
    //                     throw new Exception(implode("<br />", \yii\helpers\ArrayHelper::getColumn($newArticulo->getErrors(), 0, false)));
    //                 }
    //                 $transaction->commit();
    //                 Yii::$app->session->setFlash('primary', "Articulo creado exitosamente!");
    //                 return $this->redirect(['index']);
    //             } catch (Exception $e) {
    //                 $transaction->rollBack();
    //                 Yii::$app->session->setFlash('warning', "Error: " . $e->getMessage());
    //                 return $this->redirect(['index']);
    //             }
    //         }
    //     } else {
    //         return $this->render('crear-articulo-produccion', [
    //             'articulo' => $articulo,
    //             'clasificaciones' => $clasificaciones
    //         ]);
    //     }
    // }

    public function actionMostrarExistencias()
    {
        $searchModel  = new RegistroModelSearch();
        $articulos = RegistroModel::find()->where("CreateDate > '2023-10-17 00:00:00.000'")->orderBy('Articulo')->all();
        foreach ($articulos as $index => $articulo) {
            $articulos[$index]->Descripcion = $articulo->Articulo . " - " . $articulo->Descripcion;
        }
        $pruebaExistencias = $searchModel->search($this->request->queryParams, 'existencias');
        return $this->render('existencias', [
            'dataProvider' => $pruebaExistencias,
            'searchModel' => $searchModel,
            'articulos' => $articulos
        ]);
    }


    public function actionCrearReporteExistencias()
    {
        $existencias = Yii::$app->db->createCommand(
            "SELECT Articulo, Descripcion, COUNT(Articulo) AS Cantidad, SUM(Libras) AS Libras
            FROM BODEGA.dbo.REGISTRO 
            WHERE CreateDate > '2023-10-17 00:00:00:000'
            AND (Activo = 1 OR Activo IS NULL)
            AND Estado NOT LIKE 'ELIMINADO'
            AND UsuarioCreacion NOT LIKE '%AUDITORIA%'
            AND CodigoBarra NOT IN (SELECT CodigoBarra FROM BODEGA.dbo.DETALLEMOVIMIENTO)
            GROUP BY Articulo, Descripcion
            ORDER BY Articulo"
        )->queryAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('EXISTENCIAS DETALLE');
        $headers = [
            'Articulo', 'Descripcion', 'Cantidad', 'Libras'
        ];
        $column = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($column . '1', $header);
            $column++;
        }
        $sheet->fromArray($existencias, null, 'A2');

        $fillHeader =  [
            'font' => [
                'color' =>  ['rgb' => 'ffffff']
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => '0685f4',
                ],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' =>  ['rgb' => '000000']
                ],
            ],
        ];
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' =>  ['rgb' => '0685f4']
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'ffffff',
                ],
            ]
        ];
        $sheet->getStyle('A1:D1')->applyFromArray($fillHeader);
        $sheet->getStyle('A2:D' . (count($existencias) + 1))->applyFromArray($styleArray);
        $sheet->setAutoFilter('A1:D' . (count($existencias) + 1));

        foreach ($sheet->getColumnIterator() as $column) {
            $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filePath = "Existencias hasta fecha " . date("F j, Y");
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename=' . $filePath. '.xlsx');
        $writer->save('php://output');
        exit;
    }

    public function crearModelArticulo()
    {
        $articulo = new DynamicModel([
            'descripcion', 'clasificacion', 'tipo'
        ]);
        $articulo->setAttributeLabels([
            'descripcion' => 'Descripcion del articulo',
            'clasificacion' => 'Clasificacion del articulo',
            'tipo' => 'Tipo de articulo'
        ]);
        $articulo->addRule(['descripcion', 'clasificacion', 'tipo'], 'required');
        return $articulo;
    }

    public function siguienteArticulo($tipo, $ultimoArticulo)
    {
        if ($tipo == 'FARD0') {
            return "FARD0-" . (explode("-", $ultimoArticulo)[1] + 1);
        } else if ($tipo == 'T') {
            return "T" . (substr($ultimoArticulo, 1) + 1);
        } else if ($tipo == 'P') {
            return "P" . (substr($ultimoArticulo, 1) + 1);
        }
    }

    
    public function articuloCuentaCONINV($clasificacion)
    {
        if (
            $clasificacion == 'CARTERAS'
            || $clasificacion == 'CINCHOS'
            || $clasificacion == 'GANCHOS'
            || $clasificacion == 'GORRAS'
            || $clasificacion == 'ZAPATOS'
        ) {
            return 'ACCE';
        } else if ($clasificacion == 'JUGUETES' || $clasificacion == 'OTROS') {
            return 'OTRO';
        } else {
            return 'ROPA';
        }
    }

    public function articuloCuentaCNYCENTER($clasificacion)
    {
        if ($clasificacion == 'CARTERASYM') {
            return 'CART';
        } else if ($clasificacion == 'CINCHOS') {
            return 'CINC';
        } else if ($clasificacion == 'GANCHOS') {
            return 'GANC';
        } else if ($clasificacion == 'GORRAS') {
            return 'GORR';
        } else if ($clasificacion == 'ZAPATOS') {
            return 'ZAPA';
        } else if ($clasificacion == 'JUGUETESYP') {
            return 'JUGU';
        } else if ($clasificacion == 'OTROS') {
            return 'OTRO';
        } else if ($clasificacion == 'ROPA') {
            return 'ROPA';
        }
    }

    /**
     * Cambia el estado de un registro de produccion a eliminado
     * @param string $codigoBarra el codigo de barra del registro
     * @return view Retorna al index de los registros de produccion
     */
    public function actionDeleteRegistro($codigoBarra, $ruta)
    {
        $model = $this->findRegistroModel($codigoBarra);
        $model->Estado = 'ELIMINADO';
        $model->save();
        Yii::$app->db->createCommand(
            "UPDATE [BODEGA].[dbo].[TRANSACCION] SET Estado = 'E' WHERE CodigoBarra = '$codigoBarra'"
        )->execute();

        if ($ruta == 'busqueda') {
            Yii::$app->session->setFlash('danger', "Codigo de barra $codigoBarra eliminado con exito!");
            return $this->redirect(['reportes/reporte-buscar-codigo']);
        } else if ($ruta == 'index') {
            Yii::$app->session->setFlash('danger', "Codigo de barra $codigoBarra eliminado con exito!");
            return $this->redirect(['index']);
        }
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

    public function actionDeleteDetalleRegistro($IdRegistro, $codigoBarra)
    {
        Yii::$app->db->createCommand("DELETE FROM DETALLEREGISTRO WHERE IdRegistro = $IdRegistro")->execute();
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

    public function crearRegistroProduccionTRANSACCION($codigoBarra, $bodega, $fecha)
    {
        Yii::$app->db->createCommand(
            "INSERT INTO [BODEGA].[dbo].[TRANSACCION] 
            (CodigoBarra, IdTipoTransaccion, Fecha, Bodega, Naturaleza, Estado,
            UsuarioCreacion, FechaCreacion) 
            VALUES 
            ('" . $codigoBarra . "', 2, '$fecha', '$bodega ', 'E', 'P',
            '" . Yii::$app->session->get('user') . "', '" . date("Y-m-d H:i:s") . "')"
        )->execute();
    }

    /**
     * Obtiene el consecutivo a trabajar para registrar una mesa
     * @return string Un consecutivo el cual registrara todo movimiento de una mesa en inventario
     */
    public function obtenerConsecutivo($consecutivo, $esquema)
    {
        $getConsecutivo =  $this->SoftlandConn()->createCommand("SELECT SIGUIENTE_CONSEC 
        FROM [PRUEBAS].$esquema.[CONSECUTIVO_CI] WHERE CONSECUTIVO = '" . $consecutivo . "'")->queryOne();

        return $getConsecutivo['SIGUIENTE_CONSEC'];
    }

    /**
     * Crear un documento de inventario a partir de un consecutivo
     * @param int $consecutivo El consecutivo actual 
     * @throws PDOException Si algun campo/valor proporcionado estan fuera de los campos/valores esperados
     */
    public function crearDocumentoInv($consecutivo, $esquema, $transaccion)
    {
        $this->SoftlandConn()->createCommand(
            "INSERT INTO [PRUEBAS].$esquema.[DOCUMENTO_INV]
            (PAQUETE_INVENTARIO, DOCUMENTO_INV, CONSECUTIVO , REFERENCIA, FECHA_HOR_CREACION, FECHA_DOCUMENTO,
            SELECCIONADO, USUARIO, APROBADO) 
            VALUES 
            (
                '" . Yii::$app->session->get('paquete') . "',
                '$consecutivo',
                '$transaccion',
                '$transaccion del dia " .  date("Y-m-d") . "',
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
    public function obtenerNumeroLineaDocInv($consecutivo, $esquema)
    {
        $ultimaLinea = $this->SoftlandConn()->createCommand("SELECT COUNT(LINEA_DOC_INV) AS linea
        FROM [PRUEBAS].$esquema.[LINEA_DOC_INV] 
        WHERE DOCUMENTO_INV = '" . $consecutivo . "'")->queryOne();

        return $ultimaLinea['linea'] + 1;
    }

    public function actualizarRegistroProduccionREGISTRO($codigoBarra)
    {
        $model = $this->findRegistroModel($codigoBarra);
        $model->Estado = 'FINALIZADO';
        $model->Activo = 1;
        $model->Articulo = $model->Articulo == '' ? 'Articulo vacio' : $model->Articulo;
        $model->EmpresaDestino = $model->EmpresaDestino == '' ? 'EmpresaDestino vacio' : $model->EmpresaDestino;

        $model->MesaOrigen = $model->MesaOrigen == NULL ? 0 : $model->MesaOrigen;
        $model->BodegaActual = 'SM00';
        $model->save();
        if (!$model->save()) {
            throw new Exception(implode("<br />", \yii\helpers\ArrayHelper::getColumn($model->getErrors(), 0, false)));
        }
    }

    public function actualizarRegistroCostoREGISTRO($codigoBarra, $consecutivo, $costo)
    {
        $model = $this->findRegistroModel($codigoBarra);
        $model->DOCUMENTO_INV = $consecutivo;
        $model->Costo = $costo;
        $model->save();
    }

    /**
     * Tomando un codigo de barra, crea un registro en la tabla [BODEGA].[dbo].[TRANSACCION] para representar una salida del inventario
     * @param string $codigoBarra un codigo de barra de un registro existente
     * @param string $bodega la bodega en la que estaba registrado el fardo trabajado
     * @param string $consecutivo un codigo de consecutivo el cual enlaza el inventario con la transaccion
     */
    public function crearRegistroFinalizadoTRANSACCION($codigoBarra, $bodega, $fecha)
    {
        Yii::$app->db->createCommand(
            "INSERT INTO [BODEGA].[dbo].[TRANSACCION] 
            (CodigoBarra, IdTipoTransaccion, Fecha, Bodega, Naturaleza, Estado,
            UsuarioCreacion, FechaCreacion) 
            VALUES 
            ('$codigoBarra', 2, '$fecha', '$bodega', 'E', 'F',
            '" . Yii::$app->session->get('user') . "', '" . date("Y-m-d H:i:s") . "')"
        )->execute();
    }

    public function actualizarRegistroFinalizadoTRANSACCION($codigoBarra, $consecutivo)
    {
        Yii::$app->db->createCommand(
            "UPDATE [BODEGA].[dbo].[TRANSACCION] 
            SET Documento_Inv =  '$consecutivo' 
            WHERE CodigoBarra = '$codigoBarra' AND Estado = 'F' AND Naturaleza = 'F'
            AND IdTipoTransaccion = 2"
        )->execute();
    }

    /**
     * Crea una linea de entrada de inventario la cual representa un registro de articulo barril/fardo
     * @param int $consecutivo El consecutivo actual 
     * @throws PDOException Si algun campo/valor proporcionado estan fuera de los campos/valores esperados
     */
    public function crearLineaDocumentoInvEntrada($consecutivo, $articulo, $bodega, $costo, $esquema)
    {
        $this->SoftlandConn()->createCommand(
            "INSERT INTO [PRUEBAS].$esquema.[LINEA_DOC_INV] 
            (PAQUETE_INVENTARIO, DOCUMENTO_INV, LINEA_DOC_INV, 
            AJUSTE_CONFIG, ARTICULO, BODEGA, TIPO, SUBTIPO, SUBSUBTIPO, CANTIDAD,
            COSTO_TOTAL_LOCAL, COSTO_TOTAL_DOLAR, PRECIO_TOTAL_LOCAL, PRECIO_TOTAL_DOLAR, COSTO_TOTAL_LOCAL_COMP, COSTO_TOTAL_DOLAR_COMP)
            VALUES 
            (
                '" . Yii::$app->session->get('paquete') . "', '" . $consecutivo . "', " . $this->obtenerNumeroLineaDocInv($consecutivo, $esquema) . ",
                '~OO~', '" . $articulo . "', '" . $bodega . "', 'O', 'D', 'L', 1,
                $costo, 0, 0, 0, 0, 0
            )"
        )->execute();
    }

    /**
     * Toma el consecutivo actual de PRODUCCION de [SOFTLAND].[CONINV].[CONSECUTIVO_CI] para luego aumentarlo en 1
     * @return string el nuevo consecutivo 
     */
    public function crearSiguienteConsecutivo($esquema, $transaccion)
    {
        $getConsecutivoCode =  $this->SoftlandConn()->createCommand("SELECT CONSECUTIVO, SIGUIENTE_CONSEC 
        FROM [PRUEBAS].$esquema.[CONSECUTIVO_CI] WHERE CONSECUTIVO = '$transaccion'")->queryOne();

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
    public function actualizarConsecutivo($esquema, $transaccion)
    {
        $this->SoftlandConn()->createCommand("UPDATE [PRUEBAS].$esquema.[CONSECUTIVO_CI] 
        SET SIGUIENTE_CONSEC = '" . $this->crearSiguienteConsecutivo($esquema, $transaccion) . "' WHERE CONSECUTIVO = '$transaccion'")->execute();
    }

    /**
     * Crea un registro en la tabla [BODEGA].[dbo].[TRABAJOMESA] para complementar la salida de inventario a traves
     * de mesas de trabajo
     */
    public function crearRegistroTRABAJOMESA($libras, $costo,  $consecutivo, $fecha, $bodega)
    {
        Yii::$app->db->createCommand(
            "INSERT INTO [BODEGA].[dbo].[TRABAJOMESA] 
            (Libras, Costo,  Documento_inv, Fecha, CreateDate, Bodega)
            VALUES
            (
                $libras,
                $costo,
                '$consecutivo',
                '$fecha',
                '" . date("Y-m-d H:i:s") . "',
                '$bodega'
            )"
        )->execute();
    }

    /**
     * Obtiene todas las bodegas disponibles segun el esquema del usuario logeado
     * @return array un array con las bodegas encontradas
     */
    public function getBodegasByEsquema()
    {
        $bodegas = $this->SoftlandConn()->createCommand(
            "SELECT BODEGA, NOMBRE 
            FROM [PRUEBAS]." . $_SESSION['esquema'] . ".[BODEGA] 
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
            [PRUEBAS].[CONINV].[ARTICULO] 
            WHERE activo = 'S'
            AND CLASIFICACION_2 NOT LIKE 'RIPIO'"
        )->queryAll();
        return $articulos;
    }

    protected function getArticulosByEsquema($clasificacion)
    {
        $articulos = $this->SoftlandConn()->createCommand(
            "SELECT ARTICULO, DESCRIPCION, CLASIFICACION_2 AS ARTICULO_DESCRIPCION 
            FROM [PRUEBAS]." . $_SESSION['esquema'] . ".[ARTICULO] 
            WHERE CLASIFICACION_2 = '$clasificacion' AND ACTIVO = 'S' 
            AND ARTICULO LIKE '%P%' OR ARTICULO LIKE '%T%' OR ARTICULO LIKE '%BA030%'
            ORDER BY ARTICULO ASC"
        )->queryAll();
        if ($clasificacion == '') {
            $articulos = $this->SoftlandConn()->createCommand(
                "SELECT ARTICULO, DESCRIPCION, CLASIFICACION_2 AS ARTICULO_DESCRIPCION 
                FROM [PRUEBAS]." . $_SESSION['esquema'] . ".[ARTICULO] 
                WHERE ACTIVO = 'S' 
                AND ARTICULO LIKE '%P%' OR ARTICULO LIKE '%T%' OR ARTICULO LIKE '%BA030%'
                ORDER BY ARTICULO ASC"
            )->queryAll();
        }
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
     * Obtiene un registro que representa lo asignado a una mesa
     * @param string $numeroMesa La mesa que se desea consultar
     * @return object Un array de un solo valor que contiene la informacion de lo asignado a la mesa
     */
    public function obtenerAsignacionPorFecha($fecha)
    {
        $asignacion = TrabajoMesaModel::find()
            ->select('SUM(Libras) Libras, SUM(Costo) Costo')
            ->where("Fecha BETWEEN '2023-10-27' AND '$fecha'")
            ->one();

        return $asignacion;
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
            . $_SESSION['esquema'] . ".ARTICULO.ACTIVO
        FROM " . $_SESSION['esquema'] . ".ARTICULO 
        WHERE (" . $_SESSION['esquema'] . ".ARTICULO.ACTIVO = 'S') 
        AND (" . $_SESSION['esquema'] . ".ARTICULO.UNIDAD_ALMACEN = '59') 
        AND (" . $_SESSION['esquema'] . ".ARTICULO.USA_LOTES = 'S')  
        AND CLASIFICACION_2 = '$clasificacion'
        AND LEN(ARTICULO) > '5' 
        ORDER BY " . $_SESSION['esquema'] . ".ARTICULO.ARTICULO";

        $articulos = $this->SoftlandConn()->createCommand($query)->queryAll();
        foreach ($articulos as $index => $articulo) {
            $articulos[$index]["ACTIVO"] = $articulo['ARTICULO'] . ' - ' . $articulo["DESCRIPCION"];
        }

        return $articulos;
    }

    public function validarFinalizarDia()
    {
        $finalizado = Yii::$app->db->createCommand(
            "SELECT * FROM BODEGA.dbo.FINALIZACION_DIA WHERE Estado = 'SIN FINALIZAR' ORDER BY Fecha DESC"
        )->queryAll();

        if (count($finalizado) > 1) {
            return "DIA SIN FINALIZAR: " . $finalizado[1]["Fecha"];
        }

        return "FINALIZADO";
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

    public function obtenerSemanaActual()
    {
        $monday = strtotime("last monday");
        $monday = date('w', $monday) == date('w') ? $monday + 7 * 86400 : $monday;

        $sunday = strtotime(date("Y-m-d", $monday) . " +6 days");

        $inicioSemana = date("Y-m-d", $monday);
        $finSemana = date("Y-m-d", $sunday);

        return "BETWEEN '$inicioSemana' AND '$finSemana'";
    }
}
