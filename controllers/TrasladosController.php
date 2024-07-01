<?php

namespace app\controllers;

use app\models\DetalleMovimientoModel;
use app\models\DetalleRegistroModel;
use app\models\MovimientoModel;
use app\models\RegistroModel;
use app\models\TransaccionModel;
use app\modelsSearch\DetalleMovimientoModelSearch;
use app\modelsSearch\RegistroModelSearch;
use Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use yii\base\DynamicModel;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;

/**
 * TransaccionController implements the CRUD actions for TransaccionModel model.
 */
class TrasladosController extends Controller
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
     * @return YiiDbConnection
     */
    function SoftlandConn()
    {
        Yii::$app->db2;
    }

    public function actionIndexTraslados()
    {
        $date = date('Y-m-d');
        $dataProvider = MovimientoModel::find()->where("Fecha = '$date'")->orderBy(['CreateDate' => SORT_DESC])->all();
        return $this->render('index-traslados', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionViewTraslados($IdMovimiento)
    {
        $model = $this->findMovimientoModel($IdMovimiento);
        $searchModel = new DetalleMovimientoModelSearch();
        $searchModel->IdMovimiento = $model->IdMovimiento;
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->pagination->pageSize = 500;
        return $this->render('view-traslados', [
            'model' => $model,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreateTraslado()
    {
        $traslado = $this->createModelTraslado();
        $bodegas = $this->obtenerBodegasParaTraslados(false);
        if ($traslado->load($this->request->post())) {
            $codigosBarra = $this->trimCodigosBarra($traslado->codigo_barra);
            $duplicados = $this->verificarCodigosBarraDuplicados($codigosBarra);

            if ($duplicados != false) {
                Yii::$app->session->setFlash('danger', "Codigos de barra repetidos: " . $duplicados);
                return $this->render(
                    'create-traslados',
                    [
                        'traslado' => $traslado,
                        'bodegas' => $bodegas,
                    ]
                );
            }

            foreach ($codigosBarra as $index => $codigo) {

                $codigoDataREGISTRO = RegistroModel::find()->andWhere(['CodigoBarra' => $codigo])->one();
                $codigoDataTRANSACCION = TransaccionModel::find()->andWhere("CodigoBarra ='$codigo' AND Estado = 'F' AND Naturaleza = 'E'")->one();

                if (!$codigoDataREGISTRO || !$codigoDataTRANSACCION) {
                    Yii::$app->session->setFlash('danger', "Codigo de barra no existe: " . $codigo);
                    return $this->render(
                        'create-traslados',
                        [
                            'traslado' => $traslado,
                            'bodegas' => $bodegas,
                        ]
                    );
                }

                $validaciones = $this->validarCodigoBarraTraslado(substr($traslado->bodega_origen, 0, 4), $traslado->fecha_traslado, $codigoDataREGISTRO, $codigoDataTRANSACCION);
                if (!$validaciones) {
                    Yii::$app->session->setFlash('danger', "Codigos de barra no disponible: " . $codigo);
                    return $this->render(
                        'create-traslados',
                        [
                            'traslado' => $traslado,
                            'bodegas' => $bodegas,
                        ]
                    );
                }
            }
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $despacho = new MovimientoModel();

                $despacho->TipoMovimiento = 'T';
                $despacho->Estado = 'PROCESO';
                $despacho->origen = $traslado->bodega_origen;
                $despacho->destino = $traslado->bodega_destino;
                $despacho->Fecha = $traslado->fecha_traslado;
                $despacho->CreateDate = date("Y-m-d H:i:s");
                if (!$despacho->save()) {
                    throw new Exception(implode("<br />", \yii\helpers\ArrayHelper::getColumn($despacho->getErrors(), 0, false)));
                }


                foreach ($codigosBarra as $codigoBarra) {
                    $detalleDespacho = new DetalleMovimientoModel();
                    $detalleDespacho->IdMovimiento = $despacho->IdMovimiento;
                    $detalleDespacho->CodigoBarra = $codigoBarra;

                    if (!$detalleDespacho->save()) {
                        throw new Exception(implode("<br />", \yii\helpers\ArrayHelper::getColumn($detalleDespacho->getErrors(), 0, false)));
                    }

                    $this->crearRegistroFinalizadoTRANSACCION($codigoBarra, substr($traslado->bodega_destino, 0, 4), $traslado->fecha_traslado, 3);
                    $this->actualizarRegistroProduccionREGISTRO($codigoBarra, substr($traslado->bodega_destino, 0, 4));
                    Yii::$app->db->createCommand("DELETE FROM DETALLEMOVIMIENTO WHERE CodigoBarra = '$codigoBarra'")->execute();
                }
                $transaction->commit();
                return $this->redirect(['view-traslados', 'IdMovimiento' => $despacho->IdMovimiento]);
            } catch (Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('warning', "Error: " . $e->getMessage());
                return $this->render(
                    'create-traslados',
                    [
                        'traslado' => $traslado,
                        'bodegas' => $bodegas,
                    ]
                );
            }
        } else {

            return $this->render('create-traslados', [
                'traslado' => $traslado,
                'bodegas' => $bodegas,
            ]);
        }
    }

    public function actionCreateDespacho()
    {
        $traslado = $this->createModelTraslado();
        $bodegasDespachos = $this->obtenerBodegasParaDespachos();
        $bodegasDestino = $this->obtenerBodegasParaTraslados(true);

        if ($traslado->load($this->request->post())) {

            $codigosBarra = $this->trimCodigosBarra($traslado->codigo_barra);
            $duplicados = $this->verificarCodigosBarraDuplicados($codigosBarra);

            if ($duplicados != false) {
                Yii::$app->session->setFlash('danger', "Codigos de barra repetidos: " . $duplicados);
                return $this->render(
                    'create-despacho',
                    [
                        'traslado' => $traslado,
                        'bodegasDespachos' => $bodegasDespachos,
                        'bodegasDestino' => $bodegasDestino,
                    ]
                );
            }

            foreach ($codigosBarra as $codigoBarra) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $validarUsoCodigoPendiente = TransaccionModel::find()->andWhere("CodigoBarra ='$codigoBarra' AND Estado = 'P' AND Naturaleza = 'S'")->one();
                    if ($validarUsoCodigoPendiente) {
                        Yii::$app->session->setFlash('danger', "Codigo de barra en otro movimiento: " . $codigoBarra);
                        return $this->render(
                            'create-despacho',
                            [
                                'traslado' => $traslado,
                                'bodegasDespachos' => $bodegasDespachos,
                                'bodegasDestino' => $bodegasDestino,
                            ]
                        );
                    }
                    $REGISTRO = RegistroModel::find()->where("CodigoBarra = '$codigoBarra'")->one();
                    $TRANSACCION = TransaccionModel::find()->where("CodigoBarra = '$codigoBarra' AND Estado = 'F' AND Naturaleza = 'E'")->one();

                    if (!$REGISTRO) {
                        Yii::$app->session->setFlash('danger', "Codigo de barra no existe: " . $codigoBarra);
                        return $this->render(
                            'create-despacho',
                            [
                                'traslado' => $traslado,
                                'bodegasDespachos' => $bodegasDespachos,
                                'bodegasDestino' => $bodegasDestino,
                            ]
                        );
                    }
                    $validaciones = $this->validarCodigoBarraDespacho($REGISTRO, $TRANSACCION, substr($traslado->bodega_origen, 0, 4));

                    if (!$validaciones) {
                        Yii::$app->session->setFlash('danger', "Codigos de barra no disponible: " . $codigoBarra);
                        return $this->render(
                            'create-despacho',
                            [
                                'traslado' => $traslado,
                                'bodegasDespachos' => $bodegasDespachos,
                                'bodegasDestino' => $bodegasDestino,
                            ]
                        );
                    }
                    $transaction->commit();
                } catch (Exception $e) {
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('warning', "Error: " . $e->getMessage());
                    return $this->render(
                        'create-despacho',
                        [
                            'traslado' => $traslado,
                            'bodegasDespachos' => $bodegasDespachos,
                            'bodegasDestino' => $bodegasDestino,
                        ]
                    );
                }
            }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $despacho = new MovimientoModel();

                $despacho->TipoMovimiento = 'D';
                $despacho->Estado = 'PROCESO';
                $despacho->origen = $traslado->bodega_origen;
                $despacho->destino = $traslado->bodega_destino;
                $despacho->Fecha = $traslado->fecha_traslado;
                $despacho->CreateDate = date("Y-m-d H:i:s");

                if (!$despacho->save()) {
                    throw new Exception(implode("<br />", \yii\helpers\ArrayHelper::getColumn($despacho->getErrors(), 0, false)));
                }


                foreach ($codigosBarra as $codigoBarra) {
                    $detalleDespacho = new DetalleMovimientoModel();
                    $detalleDespacho->IdMovimiento = $despacho->IdMovimiento;
                    $detalleDespacho->CodigoBarra = $codigoBarra;

                    if (!$detalleDespacho->save()) {
                        throw new Exception(implode("<br />", \yii\helpers\ArrayHelper::getColumn($detalleDespacho->getErrors(), 0, false)));
                    }

                    $this->crearRegistroFinalizadoTRANSACCION($codigoBarra, substr($traslado->bodega_destino, 0, 4), $traslado->fecha_traslado, 7);
                    $this->actualizarRegistroProduccionREGISTRO($codigoBarra, substr($traslado->bodega_destino, 0, 4));
                }
                $transaction->commit();
                return $this->redirect(['view-traslados', 'IdMovimiento' => $despacho->IdMovimiento]);
            } catch (Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('warning', "Error: " . $e->getMessage());
                return $this->render(
                    'create-despacho',
                    [
                        'traslado' => $traslado,
                        'bodegasDespachos' => $bodegasDespachos,
                        'bodegasDestino' => $bodegasDestino,
                    ]
                );
            }
        } else {
            return $this->render('create-despacho', [
                'traslado' => $traslado,
                'bodegasDespachos' => $bodegasDespachos,
                'bodegasDestino' => $bodegasDestino,
            ]);
        }
    }

    public function actionDeleteDetalleMovimiento($IdMovimiento, $IdDetalleMovimiento, $codigoBarra, $bodegaOrigen)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            Yii::$app->db->createCommand("DELETE FROM DETALLEMOVIMIENTO WHERE IdDetalleMovimiento = $IdDetalleMovimiento")->execute();
            Yii::$app->db->createCommand("UPDATE REGISTRO SET BodegaActual = '$bodegaOrigen' WHERE CodigoBarra =  '$codigoBarra'")->execute();
            Yii::$app->db->createCommand("DELETE FROM TRANSACCION WHERE CodigoBarra =  '$codigoBarra' AND Naturaleza = 'S'")->execute();
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('warning', "Error: " . $e->getMessage());
            return $this->redirect(['view-traslados', 'IdMovimiento' => $IdMovimiento]);
        }

        $totalDetalleMovimiento = DetalleMovimientoModel::find()->where("IdMovimiento = $IdMovimiento")->all();
        if (count($totalDetalleMovimiento) == 0) {
            Yii::$app->session->setFlash('success', "Movimiento eliminado");
            $this->actionDeleteMovimiento($IdMovimiento);
            return $this->redirect(['index-traslados']);
        }
        Yii::$app->session->setFlash('warning', "Codigo de barra $codigoBarra eliminado del movimiento");
        return $this->redirect(['view-traslados', 'IdMovimiento' => $IdMovimiento]);
    }

    public function actionDeleteMovimiento($IdMovimiento)
    {
        Yii::$app->db->createCommand("DELETE FROM MOVIMIENTO WHERE IdMovimiento = $IdMovimiento")->execute();
    }

    public function actionValidarMovimiento()
    {
        $model = $this->crearModelValidarMovimiento();
        return $this->render('validar-movimiento', [
            'model' => $model
        ]);
    }

    public function actionVerResumenMovimiento($fechaInicio, $fechaFin, $empresa)
    {
        $despachos = MovimientoModel::find()
            ->where("Fecha BETWEEN '$fechaInicio' AND '$fechaFin' AND Documento_inv IS NULL AND destino LIKE '%$empresa%'")
            ->all();

        if (!$despachos) {
            return $this->renderAjax('resumen-movimiento', [
                'fechaInicio' => $fechaInicio,
                'fechaFin' => $fechaFin,
                'empresa' => $empresa,
                'despachos' => [],
                'registros' => '',
            ]);
        }

        $codigosBarra  = '';
        foreach ($despachos as $despacho) {
            foreach ($despacho->detalleMovimiento as $index => $codigo) {
                if ($index == count($despacho->detalleMovimiento) - 1) {
                    $codigosBarra .= "'$codigo->CodigoBarra'";
                    continue;
                }
                $codigosBarra .= "'$codigo->CodigoBarra', ";
            }
        }

        $registros = RegistroModel::find()
            ->where("CodigoBarra IN ($codigosBarra)")
            ->all();

        $idRegistros = '';
        foreach ($registros as $index => $id) {
            if ($index == count($registros) - 1) {
                $idRegistros .= $id->IdRegistro;
                continue;
            }
            $idRegistros .= $id->IdRegistro . ", ";
        }

        $detalles = Yii::$app->db2->createCommand(
            "SELECT DR.ArticuloDetalle, CN.DESCRIPCION, SUM(DR.Cantidad) Cantidad, SUM(Cantidad * PrecioUnitario) PrecioUnitario
            FROM BODEGA.dbo.DETALLEREGISTRO DR
            LEFT OUTER JOIN PRUEBAS.CONINV.ARTICULO CN 
            ON DR.ArticuloDetalle = CN.ARTICULO
            WHERE DR.IdRegistro IN ($idRegistros)
            GROUP BY DR.ArticuloDetalle, CN.DESCRIPCION
            ORDER BY DR.ArticuloDetalle"
        )->queryAll();


        $articulos = [];
        return $this->renderAjax('resumen-movimiento', [
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'empresa' => $empresa,
            'despachos' => $detalles,
            'registros' => $idRegistros,
        ]);
    }

    public function actionCrearReporteDespachoExcel($registros, $fechaInicio, $fechaFin, $empresa)
    {
        $empresa = $empresa == 'CA' ? 'CARISMA' : $empresa;
        $empresa = $empresa == 'E0' ? 'EVER' : $empresa;
        $empresa = $empresa == 'N0' ? 'NERY' : $empresa;
        $empresa = $empresa == 'T0' ? 'CANNYSHOP' : $empresa;

        $detalles = Yii::$app->pruebas->createCommand(
            "SELECT DR.ArticuloDetalle, CN.DESCRIPCION, SUM(DR.Cantidad) Cantidad, SUM(Cantidad * PrecioUnitario) PrecioUnitario
            FROM BODEGA.dbo.DETALLEREGISTRO DR
            LEFT OUTER JOIN PRUEBAS.CONINV.ARTICULO CN 
            ON DR.ArticuloDetalle = CN.ARTICULO
            WHERE DR.IdRegistro IN ($registros)
            GROUP BY DR.ArticuloDetalle, CN.DESCRIPCION
            ORDER BY DR.ArticuloDetalle"
        )->queryAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('DETALLE');
        $headers = ['Articulo', 'Descripcion', 'Cantidad', 'Total'];
        $column = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($column . '1', $header);
            $column++;
        }
        $sheet->fromArray($detalles, null, 'A2');

        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('ORDEN_COMPRA_LINEA');

        // Agregar encabezados a la primera fila de la Hoja 2
        $headers2 = ['ARTICULO', 'DESCRIPCION', 'CANTIDAD_ORDENADA', 'PRECIO_UNITARIO', 'IMPUESTO1'];
        $column2 = 'A';

        foreach ($headers2 as $header) {
            $sheet2->setCellValue($column2 . '1', $header);
            $column2++;
        }

        $detalles = Yii::$app->db2->createCommand(
            "SELECT DR.ArticuloDetalle, CN.DESCRIPCION, SUM(DR.Cantidad) Cantidad, CAST(' ' AS VARCHAR) as PrecioUnitario, CAST('13' AS VARCHAR) AS IMPUESTO1
            FROM BODEGA.dbo.DETALLEREGISTRO DR
            LEFT OUTER JOIN PRUEBAS.CONINV.ARTICULO CN 
            ON DR.ArticuloDetalle = CN.ARTICULO
            WHERE DR.IdRegistro IN ($registros)
            GROUP BY DR.ArticuloDetalle, CN.DESCRIPCION
            ORDER BY DR.ArticuloDetalle"
        )->queryAll();

        $sheet2->fromArray($detalles, null, 'A2');

        $writer = new Xlsx($spreadsheet);

        $fecha1 =  date("F j, Y", strtotime($fechaInicio));
        $fecha2 = date("F j, Y", strtotime($fechaFin));
        $filePath = "traslado $empresa $fecha1 - $fecha2.xlsx";
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename=' . $filePath. '.xlsx');
        $writer->save('php://output');
        exit;
    }

    public function actionConsultarMovimientos()
    {
        $model = $this->crearModelReporte();
        return $this->render('consultar-movimientos', [
            'model' => $model
        ]);
    }

    public function actionMostrarConsultaMovimientos($fechaInicio, $fechaFin, $tipo)
    {
        $movimientos = MovimientoModel::find()
            ->where("Fecha BETWEEN '$fechaInicio' AND '$fechaFin' AND TipoMovimiento = '$tipo'")
            ->orderBy(['CreateDate' => SORT_DESC])
            ->all();

        if (!$movimientos) {
            return $this->renderAjax('mostrar-movimientos-fecha', [
                'transaccion' => "<h1>No existe movimientos en esta fecha: $fechaInicio - $fechaFin</h1>",
                'fechaInicio' => $fechaInicio,
                'fechaFin' => $fechaFin,
                'tipo' => $tipo
            ]);
        }

        return $this->renderAjax('mostrar-movimientos-fecha', [
            'movimientos' => $movimientos,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'tipo' => $tipo
        ]);
    }

    public function actionGenerarReporteExcelMovimientos($fechaInicio, $fechaFin, $tipo)
    {
        $movimientos = MovimientoModel::find()
            ->where("Fecha BETWEEN '$fechaInicio' AND '$fechaFin' AND TipoMovimiento = '$tipo'")
            ->orderBy(['CreateDate' => SORT_DESC])
            ->all();

        $registros  = '';
        foreach ($movimientos as $indexMovimiento => $despacho) {
            if ($indexMovimiento == count($movimientos) - 1) {
                $registros .= "$despacho->IdMovimiento";
                continue;
            }
            $registros .= "$despacho->IdMovimiento, ";
        }
        $codigosBarra = DetalleMovimientoModel::find()->select('CodigoBarra')->where("IdMovimiento in ($registros)")->all();
        $codigos = '';
        foreach ($codigosBarra as $index => $barra) {
            if ($index == count($codigosBarra) - 1) {
                $codigos .= "'$barra->CodigoBarra'";
                continue;
            }
            $codigos .= "'$barra->CodigoBarra', ";
        }
        
        $detalles = Yii::$app->db->createCommand(
            "SELECT REGISTRO.CodigoBarra, REGISTRO.Articulo, REGISTRO.Descripcion, REGISTRO.Clasificacion,
            (SELECT NOMBRE FROM PRUEBAS.CONINV.BODEGA B WHERE B.BODEGA LIKE REGISTRO.BodegaCreacion) AS BodegaCreacion,
            (SELECT NOMBRE FROM PRUEBAS.CONINV.BODEGA B WHERE B.BODEGA LIKE REGISTRO.BodegaActual) AS BodegaActual,
            REGISTRO.Libras, REGISTRO.FechaCreacion, 
            (SELECT Fecha 
            FROM BODEGA.dbo.MOVIMIENTO M 
            INNER JOIN BODEGA.dbo.DETALLEMOVIMIENTO DM 
            ON DM.IdMovimiento = M.IdMovimiento
            WHERE DM.CodigoBarra = REGISTRO.CodigoBarra) FechaTraslado,
            REGISTRO.FechaCreacion, REGISTRO.UsuarioCreacion, REGISTRO.EmpresaDestino, REGISTRO.Observaciones, 
            CASE 
                WHEN DETALLEREGISTRO.ArticuloDetalle IS NULL
                THEN 'NO POSEE'
                ELSE DETALLEREGISTRO.ArticuloDetalle
            END AS ArticuloDetalle,
            CASE 
                WHEN CONINV.DESCRIPCION IS NULL
                THEN 'NO POSEE'
                ELSE CONINV.DESCRIPCION
            END AS DESCRIPCION,
            CASE 
                WHEN DETALLEREGISTRO.PrecioUnitario IS NULL
                THEN 0
                ELSE DETALLEREGISTRO.PrecioUnitario
            END AS PrecioUnitario,
            CASE
                WHEN DETALLEREGISTRO.Cantidad IS NULL
                THEN 0
                ELSE DETALLEREGISTRO.Cantidad
            END AS Cantidad,
            CASE
                WHEN DETALLEREGISTRO.PrecioUnitario * DETALLEREGISTRO.Cantidad IS NULL
                THEN 0
                ELSE DETALLEREGISTRO.PrecioUnitario * DETALLEREGISTRO.Cantidad
            END AS SUBTOTAL
            FROM BODEGA.dbo.REGISTRO 
            LEFT OUTER JOIN BODEGA.dbo.DETALLEREGISTRO 
            ON REGISTRO.IdRegistro = DETALLEREGISTRO.IdRegistro
            LEFT OUTER JOIN  PRUEBAS.CONINV.ARTICULO CONINV 
            ON DETALLEREGISTRO.ArticuloDetalle = CONINV.ARTICULO
            WHERE CodigoBarra IN ($codigos)
            ORDER BY BodegaActual, CodigoBarra ASC"
        )->queryAll();

        $resumen = Yii::$app->db->createCommand(
            "SELECT R.IdRegistro, R.CodigoBarra, R.Articulo, R.Descripcion, R.Clasificacion,
            (SELECT NOMBRE FROM PRUEBAS.CONINV.BODEGA B WHERE B.BODEGA LIKE R.BodegaCreacion) AS BodegaCreacion,
            (SELECT NOMBRE FROM PRUEBAS.CONINV.BODEGA B WHERE B.BODEGA LIKE R.BodegaActual) AS BodegaActual,
            R.FechaCreacion,
            (SELECT Fecha 
            FROM BODEGA.dbo.MOVIMIENTO M 
            INNER JOIN BODEGA.dbo.DETALLEMOVIMIENTO DM 
            ON DM.IdMovimiento = M.IdMovimiento
            WHERE DM.CodigoBarra = R.CodigoBarra) FechaTraslado,
            R.Libras,
            CASE 
                WHEN (SELECT SUM(Cantidad) FROM BODEGA.dbo.DETALLEREGISTRO DR WHERE DR.IdRegistro = R.IdRegistro) IS NULL
                THEN 0
                ELSE (SELECT SUM(Cantidad) FROM BODEGA.dbo.DETALLEREGISTRO DR WHERE DR.IdRegistro = R.IdRegistro)
            END AS Cantidad,
            CASE 
                WHEN (SELECT SUM(Cantidad * PrecioUnitario) FROM BODEGA.dbo.DETALLEREGISTRO DR WHERE DR.IdRegistro = R.IdRegistro) IS NULL
                THEN 0
                ELSE (SELECT SUM(Cantidad * PrecioUnitario) FROM BODEGA.dbo.DETALLEREGISTRO DR WHERE DR.IdRegistro = R.IdRegistro)
            END AS PrecioUnitario
            FROM BODEGA.dbo.REGISTRO R
            WHERE (R.CodigoBarra IN ($codigos))
            GROUP BY R.IdRegistro, R.CodigoBarra, R.Articulo, R.Descripcion, R.Clasificacion, R.Libras, R.Unidades, BodegaCreacion, BodegaActual, R.FechaCreacion
            ORDER BY R.BodegaActual, R.CodigoBarra"
        )->queryAll();

        $count = Yii::$app->db->createCommand(
            "SELECT DISTINCT CodigoBarra 
            FROM BODEGA.dbo.REGISTRO
            WHERE CodigoBarra IN ($codigos)"
        )->queryAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('DETALLE');

        $headersResumen = ['Registro', 'CodigoBarra', 'Articulo', 'Descripcion', 'Clasificacion', 'Bodega creacion', 
        'Bodega actual', 'Fecha de creacion', 'Fecha traslado', 'Libras', 'Cantidad', 'Totalfinal'];
        $column = 'A';
        foreach ($headersResumen as $header) {
            $sheet->setCellValue($column . '1', $header);
            $column++;
        }
        $sheet->fromArray($resumen, null, 'A2');

        $sheet->setCellValue('N1', 'Cantidad de fardos con desglose');
        $sheet->setCellValue('N2', count($count));
        $sheet->setCellValue('N1', 'Cantidad de fardos sin desglose');
        $sheet->setCellValue('N2', count($count));

        $headers = [
            'CodigoBarra', 'Articulo', 'Descripcion', 'Clasificacion', 'Bodega creacion', 'Bodega actual', 'Libras', 'Fecha de creacion', 'Fecha traslado',
            'UsuarioCreacion', 'EmpresaDestino', 'Observaciones', 'ArticuloDetalle', 'DescripcionDetalle', 'PrecioUnitario',
            'Cantidad', 'Total'
        ];
        $column2 = 'P';
        foreach ($headers as $header) {
            $sheet->setCellValue($column2 . '1', $header);
            $column2++;
        }

        $sheet->fromArray($detalles, null, 'P2');
        $sheet->setAutoFilter('A1:AF' . (count($resumen) + 1));

        foreach ($sheet->getColumnIterator() as $column) {
            $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }

        $fillHeader =  $this->estilosCabezeraReportes();
        $styleArray = $this->estilosContenidoReportes();

        $sheet->getStyle('A1:L1')->applyFromArray($fillHeader);
        $sheet->getStyle('A2:L' . (count($resumen) + 1))->applyFromArray($styleArray);

        $sheet->getStyle('N1')->applyFromArray($fillHeader);
        $sheet->getStyle('N2')->applyFromArray($styleArray);

        $sheet->getStyle('P1:AF1')->applyFromArray($fillHeader);
        $sheet->getStyle('P2:AF' . (count($detalles) + 1))->applyFromArray($styleArray);

        $writer = new Xlsx($spreadsheet);
        $filePath = "Informacion de codigos de barra";
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename=' . $filePath. '.xlsx');
        $writer->save('php://output');
        exit;
    }

    public function estilosContenidoReportes()
    {
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

        return $styleArray;
    }

    public function estilosCabezeraReportes()
    {
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

        return $fillHeader;
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
            'reporte' => 'Tipo de movimiento',
            'fecha' => 'Fecha estimada',
            'persona' => 'Persona',
            'mesa' => 'Mesa',
        ]);
        $reporte->addRule(['reporte', 'fecha', 'persona'], 'required');
        return $reporte;
    }

    public function crearModelValidarMovimiento()
    {
        $reporte = new DynamicModel([
            'reporte', 'fechaInicio', 'fechaFin'
        ]);
        $reporte->setAttributeLabels([
            'fechaInicio' => 'Fecha estimada de inicio',
            'fechaFin' => 'Fecha estimada de finalizacion',
            'movimiento' => 'Tipo de movimiento'
        ]);
        $reporte->addRule(['movimiento', 'fechaInicio', 'fechaFin'], 'required');
        return $reporte;
    }

    public function createModelTraslado()
    {
        $traslado = new DynamicModel([
            'codigo_barra', 'bodega_origen', 'bodega_destino', 'fecha_traslado'
        ]);

        $traslado->setAttributeLabels(
            [
                'codigo_barra' => 'Codigos de barra',
                'bodega_origen' => 'Bodega de origen',
                'bodega_destino' => 'Bodega destino',
                'fecha_traslado' => 'Fecha de traslado'
            ]
        );
        $traslado->addRule(['codigo_barra', 'bodega_origen', 'bodega_destino', 'fecha_traslado'], 'required');

        return $traslado;
    }

    protected function findMovimientoModel($IdMovimiento)
    {
        if (($model = MovimientoModel::findOne(['IdMovimiento' => $IdMovimiento])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    protected function findDetalleMovimientoModel($IdMovimiento)
    {
        if (($model = DetalleMovimientoModel::findOne(['IdMovimiento' => $IdMovimiento])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function obtenerBodegasParaTraslados($despacho)
    {
        $tiendasCanny = Yii::$app->db2->createCommand(
            "SELECT BODEGA, NOMBRE FROM [PRUEBAS].[CANNYSHOP].[BODEGA] WHERE BODEGA NOT LIKE 'ND'"
        )->queryAll();

        $tiendasCarisma = Yii::$app->db2->createCommand(
            "SELECT BODEGA, NOMBRE FROM [PRUEBAS].[CCARISMA].[BODEGA]WHERE BODEGA NOT LIKE 'ND'"
        )->queryAll();

        $tiendasEver = Yii::$app->db2->createCommand(
            "SELECT BODEGA, NOMBRE FROM [PRUEBAS].[CEVER].[BODEGA]WHERE BODEGA NOT LIKE 'ND'"
        )->queryAll();

        $tiendasNery = Yii::$app->db2->createCommand(
            "SELECT BODEGA, NOMBRE FROM [PRUEBAS].[CNERY].[BODEGA]WHERE BODEGA NOT LIKE 'ND'"
        )->queryAll();

        $bodegasSM = Yii::$app->db2->createCommand(
            "SELECT BODEGA, NOMBRE FROM [PRUEBAS].[CONINV].[BODEGA] WHERE BODEGA LIKE '%SM%' AND BODEGA NOT LIKE 'ND'"
        )->queryAll();

        $bodegaUsulutan = Yii::$app->db2->createCommand(
            "SELECT BODEGA, NOMBRE FROM [PRUEBAS].[CONINV].[BODEGA] WHERE  BODEGA LIKE '%US%' AND BODEGA NOT LIKE 'ND'"
        )->queryAll();

        if ($despacho) {
            $bodegas = array_merge($tiendasCanny, $tiendasCarisma, $tiendasEver, $tiendasNery, $bodegaUsulutan);
        } else {
            $bodegas = array_merge($tiendasCanny, $tiendasCarisma, $tiendasEver, $tiendasNery, $bodegasSM);
        }

        foreach ($bodegas as $index => $bodega) {
            $bodegas[$index]['NOMBRE'] = $bodegas[$index]['BODEGA'] . " - " . $bodegas[$index]['NOMBRE'];
        }
        sort($bodegas);
        return $bodegas;
    }

    public function obtenerBodegasParaDespachos()
    {
        $bodegasSM = Yii::$app->db2->createCommand(
            "SELECT BODEGA, NOMBRE FROM [PRUEBAS].[CONINV].[BODEGA] WHERE BODEGA = 'SM00' OR BODEGA = 'SM04'"
        )->queryAll();

        foreach ($bodegasSM as $index => $bodega) {
            $bodegasSM[$index]['NOMBRE'] = $bodegasSM[$index]['BODEGA'] . " - " . $bodegasSM[$index]['NOMBRE'];
        }
        return $bodegasSM;
    }

    public function actionBodegasDestino()
    {

        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $bodegaSeleccionada = end($_POST['depdrop_parents']);
            $esquema = substr($bodegaSeleccionada, 0, 2);
            $esquemaDb = '';

            if ($esquema == 'CA') {
                $esquemaDb = 'CCARISMA';
            } else if ($esquema == 'E0') {
                $esquemaDb = 'CEVER';
            } else if ($esquema == 'N0') {
                $esquemaDb = 'CNERY';
            } else if ($esquema == 'T0') {
                $esquemaDb = 'CANNYSHOP';
            } else if ($esquema == 'SM') {
                $esquemaDb = 'CONINV';
            }

            $list = $this->SoftlandConn()->createCommand(
                "SELECT * FROM [PRUEBAS].$esquemaDb.[BODEGA] WHERE BODEGA NOT LIKE 'ND' AND BODEGA LIKE '%$esquema%' ORDER BY BODEGA ASC"
            )->queryAll();
            $selected  = null;
            if ($esquema != null && count($list) > 0) {
                foreach ($list as $bodega) {
                    $out[] = ['id' => $bodega['BODEGA'] . " - " . $bodega['NOMBRE'], 'name' => $bodega['BODEGA'] . " - " . $bodega['NOMBRE']];
                }
                return Json::encode(['output' => $out, 'selected' => $selected]);
            }
        }
        return Json::encode(['output' => '', 'selected' => '']);
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
     * Valida que el codigo de barra cumple con ciertas condiciones
     * @return bool si el codigo de barra cumple con dichas condiciones retorna true, sino retorna false
     */
    public function validarCodigoBarraDespacho($codigoDataREGISTRO, $codigoDataTRANSACCION, $bodega_origen)
    {
        if (
             //$codigoDataREGISTRO->Activo == 1
            //&& $codigoDataREGISTRO->Estado == 'FINALIZADO' 
            //&& $codigoDataTRANSACCION->Estado == 'F'
            //&& $codigoDataTRANSACCION->Naturaleza == 'E'
            //&& $codigoDataTRANSACCION->IdTipoTransaccion == 2
            $codigoDataREGISTRO->BodegaActual == $codigoDataREGISTRO->BodegaCreacion
            && $codigoDataREGISTRO->BodegaCreacion == $bodega_origen
        ) {
            return true;
        }

        return false;
    }

    public function validarCodigoBarraTraslado($bodegaOrigen, $fechaTraslado, $codigoDataREGISTRO, $codigoDataTRANSACCION)
    {
        if (
            //$codigoDataREGISTRO->Activo == 1
            //&& $codigoDataREGISTRO->Estado == 'FINALIZADO'
            //&& $codigoDataTRANSACCION->Estado == 'F'
            //&& $codigoDataTRANSACCION->Naturaleza == 'E'
            //&& $codigoDataTRANSACCION->IdTipoTransaccion == 2
             $codigoDataREGISTRO->BodegaActual == $bodegaOrigen
            && $codigoDataREGISTRO->FechaCreacion <= $fechaTraslado
        ) {
            return true;
        }

        return false;
    }

    public function crearRegistroFinalizadoTRANSACCION($codigoBarra, $bodega, $fecha, $movimiento)
    {
        Yii::$app->db->createCommand(
            "INSERT INTO [BODEGA].[dbo].[TRANSACCION] 
            (CodigoBarra, IdTipoTransaccion, Fecha, Bodega, Naturaleza, Estado,
            UsuarioCreacion, FechaCreacion) 
            VALUES 
            ('$codigoBarra', $movimiento, '$fecha', '$bodega', 'S', 'P',
            '" . Yii::$app->session->get('user') . "', '" . date("Y-m-d H:i:s") . "')"
        )->execute();
    }

    public function actualizarRegistroProduccionREGISTRO($codigoBarra, $bodegaActual)
    {
        $model = $this->findRegistroModel($codigoBarra);
        $model->BodegaActual = $bodegaActual;
        $model->save();
    }

    protected function findRegistroModel($codigoBarra)
    {
        if (($model = RegistroModel::findOne(['CodigoBarra' => $codigoBarra])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
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
