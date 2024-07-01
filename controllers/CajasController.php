<?php

namespace app\controllers;

use app\models\RegistroModel;
use app\models\TipoEmpaqueModel;
use app\models\TransaccionModel;
use app\modelsSearch\RegistroModelSearch;
use yii\web\Controller;
use Yii;
use yii\base\DynamicModel;
use yii\filters\VerbFilter;
use yii\helpers\Url;

/**
 * CajasController es un controlador que permite manipular la separacion de productos provenientes de fardos.
 */
class CajasController extends Controller
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
    function SoftlandConn()
    {
        return Yii::$app->db2;
    }


    public function actionIndexCajas($condicionImprimir)
    {
        if (!isset($_SESSION['user'])) {
            return $this->redirect(Url::to(Yii::$app->request->baseUrl . '/index.php?r=site/login'));
        }
        $searchModel = new RegistroModelSearch();
        $articulos = $this->getArticulosCajas(['ROPA', 'ZAPATOS', 'JUGUETES', 'OTROS', 'CARTERAS', 'CINCHOS', 'GORRAS']);
        $dataProvider = $searchModel->search($this->request->queryParams, 'IdTipoRegistro = 4 AND IdTipoEmpaque IN (1, 2, 3, 4) AND Activo = 1');
        $dataProvider->sort->defaultOrder = ['FechaCreacion' => SORT_DESC];

        return $this->render('index-cajas', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'articulos' => $articulos,
            'imprimir' => $condicionImprimir,
        ]);
    }

    /**
     * Verifica un conjunto de codigos de barra para luego ser distribuidos en barriles
     * Si los codigos de barra existen, muestra los valores asociados a los codigos de barra
     * @return Array Que contiene los valores asociados a los codigos de barra
     */
    public function actionCreateCaja()
    {
        $bodegas = $this->getBodegasByEsquema();
        $caja = $this->createModelCajas();
        if ($caja->load($this->request->post())) {
            $codigosBarra = $this->trimCodigosBarra($caja->codigo_barra);
            $duplicado = $this->verificarCodigosBarraDuplicados($codigosBarra);

            if ($duplicado != false) {
                Yii::$app->session->setFlash('danger', "Codigos de barra repetidos: " . $duplicado);
                $caja = $this->createModelCajas();
                return $this->render(
                    'form-create-caja',
                    [
                        'bodegas' => $bodegas,
                        'caja' => $caja,
                        'detalle' => false,
                    ]
                );
            }

            $totalLibras = 0;
            $totalCosto = 0;
            $contador = 0;
            $articulos = [];
            foreach ($codigosBarra as $index => $codigo) {

                $codigoDataREGISTRO = RegistroModel::find()->andWhere(['CodigoBarra' => $codigo])->one();
                $codigoDataTRANSACCION = TransaccionModel::find()->andWhere(['CodigoBarra' => $codigo])->one();

                if ($this->verificarCodigoRipio($codigoDataREGISTRO->Clasificacion)) {
                    Yii::$app->session->setFlash('danger', "Codigo de ripio no puede ser agregado a cajas!");
                    $caja = $this->createModelCajas();
                    return $this->render(
                        'form-create-caja',
                        [
                            'bodegas' => $bodegas,
                            'caja' => $caja,
                            'detalle' => false,
                        ]
                    );
                }


                if ($codigoDataREGISTRO->Articulo != 'BA030')
                    $articulos[$contador] = $codigoDataREGISTRO->Articulo;
                $contador--;

                if ($contador > 0) {
                    if ($articulos[$contador - 1] != $articulos[$contador]) {
                        Yii::$app->session->setFlash('danger', "Los barriles deben pertenecer al mismo articulo");
                        $caja = $this->createModelCajas();
                        return $this->render(
                            'form-create-caja',
                            [
                                'bodegas' => $bodegas,
                                'caja' => $caja,
                                'detalle' => false,
                            ]
                        );
                    }
                }
                $contador++;
                $validaciones = $this->validarCodigoBarra($codigoDataREGISTRO, $codigoDataTRANSACCION, $caja->bodega);
                if ($validaciones) {
                    $codigosBarra[$index] = $codigo . ', '
                        . $codigoDataREGISTRO->Articulo . ', '
                        . $codigoDataREGISTRO->Clasificacion . ', '
                        . $codigoDataREGISTRO->Descripcion . ', '
                        . $codigoDataREGISTRO->Libras . ', '
                        . $codigoDataREGISTRO->BodegaActual . ', '
                        . $codigoDataREGISTRO->Costo . ', '
                        . $codigoDataREGISTRO->FechaCreacion;
                    $totalLibras += $codigoDataREGISTRO->Libras;
                    $totalCosto += $codigoDataREGISTRO->Costo;
                } else {
                    Yii::$app->session->setFlash('danger', "Codigos de barra \"$codigo\" no disponible en bodega: $caja->bodega");
                    $caja = $this->createModelCajas();
                    return $this->render(
                        'form-create-caja',
                        [
                            'bodegas' => $bodegas,
                            'caja' => $caja,
                            'detalle' => false,
                        ]
                    );
                }
            }
            $datosJson = json_encode($codigosBarra);

            return $this->render(
                'form-create-caja',
                [
                    'bodegas' => $bodegas,
                    'caja' => $caja,
                    'detalle' => true,
                    'registros' => $codigosBarra,
                    'registrosJson' => $datosJson,
                    'totalLibras' => $totalLibras,
                    'totalCosto' => $totalCosto,
                ]
            );
        } else {
            return $this->render(
                'form-create-caja',
                [
                    'bodegas' => $bodegas,
                    'caja' => $caja,
                    'detalle' => false
                ]
            );
        }
    }

    public function actionCreateDetalle($registros, $totalLibrasBarriles, $totalCostoBarriles, $empaque)
    {

        $registros = json_decode($registros);
        $informacionBarril = explode(", ", $registros[0]);

        $consecutivoActual = $this->obtenerConsecutivo('REC-LBS');
        $this->crearDocumentoInv($consecutivoActual);

        /**
         * $registros => array asociativo que contiene informacion sobre fardos a trabajar 
         * $registros[i][0] => VP200918523 => codigo de barra de fardo
         * $registros[i][1] => FARD0-0795 => articulo del fardo
         * $registros[i][2] => ROPA => clasificacion_2 del fardo
         * $registros[i][3] => PACA MIXTA PREMIUM => descripcion del fardo
         * $registros[i][4] => 1108.84 => libras del fardo
         * $registros[i][5] => SM00 => bodega actual del fardo
         * $registros[i][6] =>  1790.385421 => costo del fardo
         * $registros[i][7] => 2023-09-20 => Fecha de registro del fardo
         */
        foreach ($registros as $key => $value) {
            $datos = explode(", ", $value);

            $this->crearLineaDocumentoInvSalida($consecutivoActual, $datos[1], $datos[5]);
            $this->actualizarRegistroFardoREGISTRO($datos[0]);
            $this->crearRegistroFardoSalidaTRANSACCION($datos[0], $datos[5], $consecutivoActual);
        }

        $this->crearLineaDocumentoInvEntrada($consecutivoActual, $informacionBarril[1], $informacionBarril[5], $totalCostoBarriles);
        $codigoBarra = $this->generateBarCode(date("Y-m-d"));
        $this->crearRegistroREGISTROBarril(
            $codigoBarra,
            $informacionBarril[1],
            $informacionBarril[3],
            $informacionBarril[2],
            $totalLibrasBarriles,
            $informacionBarril[5],
            $consecutivoActual,
            $totalCostoBarriles,
            $empaque
        );
        $this->crearRegistroTRANSACCIONBarril($codigoBarra, $informacionBarril[5], $consecutivoActual);

        $this->actualizarConsecutivo();
        $imprimirCaja = "<script>window.open('http://localhost/yii2-prod/views/cajas/pdf-caja.php?codigoBarra=" . $codigoBarra . "&consecutivo=" . $consecutivoActual . "', '_blank')</script>";
        return $this->redirect(['index-cajas', 'condicionImprimir' => $imprimirCaja]);
    }

    /**
     * Limpia los espacios en blanco de los codigos de barra y los separa en lineas
     * @return array un array nuevo con codigos de barra limpios
     */
    public function trimCodigosBarra($codigosBarra)
    {
        $codigosBarra = trim($codigosBarra);
        $codigosBarra = explode("\n", $codigosBarra);

        foreach ($codigosBarra as $index => $codigo) {
            $codigosBarra[$index] = trim($codigosBarra[$index]);
        }

        return $codigosBarra;
    }

    /**
     * Verifica si los codigos de barra enviados estan duplicados
     * @return string|bool Si algun codigo se repite retorna ese codigo, sino retorna false
     */
    public function verificarCodigosBarraDuplicados($codigosBarra)
    {
        $countCodigosBarra = array_count_values($codigosBarra);
        foreach ($codigosBarra as $codigo) {
            if ($countCodigosBarra[$codigo] > 1) {
                return $codigo;
            }
        }

        return false;
    }

    /**
     * Verificar si el codigo de barra enviado es de RIPIO
     * @return bool si el codigo de barra es de ripio retorna true, sino retorna false
     */
    public function verificarCodigoRipio($clasificacion)
    {
        if ($clasificacion == 'RIPIO') {
            return true;
        }

        return false;
    }

    /**
     * Valida que el codigo de barra cumple con ciertas condiciones
     * @return bool si el codigo de barra cumple con dichas condiciones retorna true, sino retorna false
     */
    public function validarCodigoBarra($codigoDataREGISTRO, $codigoDataTRANSACCION, $bodega)
    {
        if (
            $codigoDataREGISTRO->Estado == 'FINALIZADO'
            && $codigoDataREGISTRO->BodegaActual == $bodega
            && $codigoDataREGISTRO->Activo == 1
            && $codigoDataTRANSACCION->Estado == 'F'
            && $codigoDataTRANSACCION->Naturaleza == 'E'
            && $codigoDataTRANSACCION->IdTipoTransaccion == 9
        ) {
            return true;
        }

        return false;
    }

    /**
     * Obtiene el consecutivo a trabajar al momento de la creacion de barriles
     * @return string Un consecutivo el cual registrara todo movimiento de barriles en inventario
     */
    public function obtenerConsecutivo($consecutivo)
    {
        $getConsecutivo =  $this->SoftlandConn()->createCommand("SELECT SIGUIENTE_CONSEC 
        FROM [PRUEBAS].[CONINV].[CONSECUTIVO_CI] WHERE CONSECUTIVO = '" . $consecutivo . "'")->queryOne();

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
            "INSERT INTO [PRUEBAS].[CONINV].[DOCUMENTO_INV]
            (PAQUETE_INVENTARIO, DOCUMENTO_INV, CONSECUTIVO , REFERENCIA, FECHA_HOR_CREACION, FECHA_DOCUMENTO,
            SELECCIONADO, USUARIO, APROBADO) 
            VALUES 
            (
                '" . Yii::$app->session->get('paquete') . "',
                '" . $consecutivo . "',
                'REC-LBS',
                'REC-LBS del dia " .  date("Y-m-d") . "',
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
        FROM [PRUEBAS].[CONINV].[LINEA_DOC_INV] 
        WHERE DOCUMENTO_INV = '" . $consecutivo . "'")->queryOne();

        return $ultimaLinea['linea'] + 1;
    }

    /**
     * Crea una linea de salida de inventario la cual representa un registro de articulo barril/fardo
     * @param int $consecutivo El consecutivo actual 
     * @throws PDOException Si algun campo/valor proporcionado estan fuera de los campos/valores esperados
     */
    public function crearLineaDocumentoInvSalida($consecutivo, $articulo, $bodega)
    {
        $this->SoftlandConn()->createCommand(
            "INSERT INTO [PRUEBAS].[CONINV].[LINEA_DOC_INV] 
            (PAQUETE_INVENTARIO, DOCUMENTO_INV, LINEA_DOC_INV, 
            AJUSTE_CONFIG, ARTICULO, BODEGA, TIPO, SUBTIPO, SUBSUBTIPO, CANTIDAD,
            COSTO_TOTAL_LOCAL, COSTO_TOTAL_DOLAR, PRECIO_TOTAL_LOCAL, PRECIO_TOTAL_DOLAR, COSTO_TOTAL_LOCAL_COMP, COSTO_TOTAL_DOLAR_COMP)
            VALUES 
            (
                '" . Yii::$app->session->get('paquete') . "',
                '" . $consecutivo . "',
                " . $this->obtenerNumeroLineaDocInv($consecutivo) . ",
                '~CC~', '" . $articulo . "', '" . $bodega . "', 'C', 'D', 'N', 1,
                0,0,0,0,0,0
            )"
        )->execute();
    }

    /**
     * Tomando un codigo de barra, actualiza su estado en la tabla REGISTRO
     * @param string $codigoBarra un codigo de barra de un registro existente
     */
    public function actualizarRegistroFardoREGISTRO($codigoBarra)
    {
        Yii::$app->db->createCommand(
            "UPDATE [BODEGA].[dbo].[REGISTRO] SET Activo = 0 
            WHERE CodigoBarra = '$codigoBarra' 
            AND IdTipoRegistro = 4"
        )->execute();
    }

    /**
     * Tomando un codigo de barra, crea un registro en la tabla [BODEGA].[dbo].[TRANSACCION] para representar una salida del inventario
     * @param string $codigoBarra un codigo de barra de un registro existente
     * @param string $bodega la bodega en la que estaba registrado el fardo trabajado
     * @param string $consecutivo un codigo de consecutivo el cual enlaza el inventario con la transaccion
     */
    public function crearRegistroFardoSalidaTRANSACCION($codigoBarra, $bodega, $consecutivo)
    {
        Yii::$app->db->createCommand(
            "INSERT INTO [BODEGA].[dbo].[TRANSACCION] 
            (CodigoBarra, IdTipoTransaccion, Fecha, Bodega, Naturaleza, Estado,
            UsuarioCreacion, FechaCreacion, Documento_Inv) 
            VALUES 
            ('" . $codigoBarra . "', 1010, '" . date("Y-m-d") . "', '" . $bodega . "', 'S', 'F',
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
            "INSERT INTO [PRUEBAS].[CONINV].[LINEA_DOC_INV] 
            (PAQUETE_INVENTARIO, DOCUMENTO_INV, LINEA_DOC_INV, 
            AJUSTE_CONFIG, ARTICULO, BODEGA, TIPO, SUBTIPO, SUBSUBTIPO, CANTIDAD,
            COSTO_TOTAL_LOCAL, COSTO_TOTAL_DOLAR, PRECIO_TOTAL_LOCAL, PRECIO_TOTAL_DOLAR, COSTO_TOTAL_LOCAL_COMP, COSTO_TOTAL_DOLAR_COMP)
            VALUES 
            (
                '" . Yii::$app->session->get('paquete') . "', '$consecutivo', " . $this->obtenerNumeroLineaDocInv($consecutivo) . ",
                '~CC~', '$articulo', '$bodega', 'O', 'D', 'L', 1,
                $costo, 0, 0, 0, 0, 0
            )"
        )->execute();
    }

    public function crearRegistroREGISTROBarril($codigoBarra, $articulo, $descripcion, $clasificacion, $libras, $bodega, $consecutivo, $costo, $empaque)
    {
        Yii::$app->db->createCommand(
            "INSERT INTO [BODEGA].[dbo].[REGISTRO] 
            (CodigoBarra, Articulo, Descripcion, Clasificacion, Libras, IdTipoEmpaque, IdUbicacion, 
            BodegaCreacion, BodegaActual, UsuarioCreacion, DOCUMENTO_INV, Estado, Activo, 
            Costo, FechaCreacion, Sesion, IdTipoRegistro, CreateDate)
            VALUES
            (
                '$codigoBarra', '$articulo', '$descripcion', '$clasificacion', '$libras', $empaque, 1,
                '$bodega', '$bodega', '" . Yii::$app->session->get('user') . "', '$consecutivo', 'FINALIZADO', 1, 
                $costo, '" . date("Y-m-d ") . "', '" . ($this->getSessionDataRegisterDay(date("Y-m-d")) + 1) . "', 4, '" . date("Y-m-d H:i:s") . "'
            )"
        )->execute();
    }

    public function crearRegistroTRANSACCIONBarril($codigoBarra, $bodega, $consecutivo)
    {
        Yii::$app->db->createCommand(
            "INSERT INTO [BODEGA].[dbo].[TRANSACCION] 
            (CodigoBarra, IdTipoTransaccion, Fecha, Bodega, Naturaleza, Estado, UsuarioCreacion, FechaCreacion, Documento_Inv)
            VALUES
            (
                '" . $codigoBarra . "',
                1010,
                '" . date("Y-m-d") . "',
                '" . $bodega . "',
                'E',
                'F',
                '" . Yii::$app->session->get('user') . "',
                '" . date("Y-m-d H:i:s") . "',
                '" . $consecutivo . "'
            )"
        )->execute();
    }

    /**
     * Toma el consecutivo actual de REC-LBS de [SOFTLAND].[CONINV].[CONSECUTIVO_CI] para luego aumentarlo en 1
     * @return string el nuevo consecutivo 
     */
    public function crearSiguienteConsecutivo()
    {
        $getConsecutivoCode =  $this->SoftlandConn()->createCommand("SELECT CONSECUTIVO, SIGUIENTE_CONSEC 
        FROM [PRUEBAS].[CONINV].[CONSECUTIVO_CI] WHERE CONSECUTIVO = 'REC-LBS'")->queryOne();

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
     * Actualiza el consecutivo REC-LBS de [SOFTLAND].[CONINV].[CONSECUTIVO_CI]
     */
    public function actualizarConsecutivo()
    {
        $this->SoftlandConn()->createCommand("UPDATE [PRUEBAS].[CONINV].[CONSECUTIVO_CI] 
        SET SIGUIENTE_CONSEC = '" . $this->crearSiguienteConsecutivo() . "' WHERE CONSECUTIVO = 'REC-LBS'")->execute();
    }

    /**
     * Genera un modelo dinamico para consultar la disponibilidad de un fardo a procesar
     * @return DynamicModel Un nuevo modelo con las especificaciones necesarias
     */
    public function createModelCajas()
    {
        $caja = new DynamicModel([
            'codigo_barra', 'bodega'
        ]);

        $caja->setAttributeLabels(['codigo_barra' => 'Codigo de barra', 'bodega' => 'Bodega']);
        $caja->addRule(['codigo_barra', 'bodega'], 'required');

        return $caja;
    }

    /**
     * Obtiene todas las bodegas existentes segun el esquema del usuario logeado 
     * @return Array Contiene las bodegas contatenadas a su nombre
     * @throws PDOException Si existe algun error en el query
     */
    public function getBodegasByEsquema()
    {
        $bodegas = $this->SoftlandConn()->createCommand("SELECT BODEGA, NOMBRE FROM " . $_SESSION['esquema'] . ".BODEGA WHERE bodega LIKE'%00'  or BODEGA like 'SM%'")->queryAll();

        foreach ($bodegas as $index => $bodega) {
            $bodegas[$index]["NOMBRE"] = $bodegas[$index]["BODEGA"] . ' - ' . $bodegas[$index]["NOMBRE"];
        }
        return $bodegas;
    }

    /**
     * Obtiene todos los articulos de barriles ('BA000') segun la clasificacion de los fardos a trabajar, incluyendo ripio
     * @return Array Contiene todos los articulos de barriles, concatenados con su clasificacion, descripcion y codigo de articulo
     * @throws PDOEXception si la clasificacion dada no existe
     */
    public function getArticulosCajas($clasificaciones)
    {
        $stringClasificaciones = '';

        foreach ($clasificaciones as $key => $value) {
            if ($key == (count($clasificaciones) - 1)) {
                $stringClasificaciones .= "'" . $value . "'";
                continue;
            }
            $stringClasificaciones .= "'" . $value . "', ";
        }
        $articulos = $this->SoftlandConn()->createCommand("SELECT ARTICULO, DESCRIPCION, CLASIFICACION_2, ARTICULO AS ARTICULODESCRIPCION, ARTICULO as ARTICULODESCRIPIONCLASIFICACION 
        FROM [PRUEBAS].[CONINV].[ARTICULO] 
        WHERE ARTICULO Like '%BA%' 
        AND ACTIVO = 'S'
        AND CLASIFICACION_2 IN ($stringClasificaciones) 
        OR CLASIFICACION_1 = 'RIPIO'
        ORDER BY ARTICULO")->queryAll();

        foreach ($articulos as $index => $articulo) {
            $articulos[$index]["ARTICULODESCRIPIONCLASIFICACION"] = $articulos[$index]["ARTICULO"] . ' - ' . $articulos[$index]["DESCRIPCION"] . ' - ' . $articulos[$index]["CLASIFICACION_2"];
            $articulos[$index]["ARTICULODESCRIPCION"] = $articulos[$index]["ARTICULO"] . ' - ' . $articulos[$index]["DESCRIPCION"];
        }
        return $articulos;
    }

    /**
     * Genera un codigo de barra para identificar de manera unica cada registro que pase en [BODEGA].[dbo]
     * @param date $fecha Una fecha necesaria para generar el codigo de barra
     * @return string un nuevo codigo de barra unico
     */
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

    /**
     * A partir de una fecha, toma el conteo de todos los registros creados en un dia
     * @param date $fecha una fecha para filtrar los registros 
     * @return int un numero de registros diarios
     */
    public function getSessionDataRegisterDay($fecha)
    {
        $sesionDataDay = Yii::$app->db->createCommand("SELECT ISNULL(MAX(Sesion), 0) AS maximo 
        FROM REGISTRO 
        WHERE FechaCreacion = '" . $fecha . "' AND IdTipoRegistro = 4")->queryOne();

        return $sesionDataDay['maximo'];
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
