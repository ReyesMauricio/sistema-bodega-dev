<?php

namespace app\controllers;

use app\models\TrabajoMesaModel;
use app\modelsSearch\TrabajoMesaModelSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * TrabajoMesaController implements the CRUD actions for TrabajoMesaModel model.
 */
class TrabajoMesaController extends Controller
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
     * Lists all TrabajoMesaModel models.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Displays a single TrabajoMesaModel model.
     * @param int $id_trabajo_mesa Id Trabajo Mesa
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id_trabajo_mesa)
    {
        return $this->render('view', [
            'model' => $this->findModel($id_trabajo_mesa),
        ]);
    }

    /**
     * Creates a new TrabajoMesaModel model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new TrabajoMesaModel();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id_trabajo_mesa' => $model->id_trabajo_mesa]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing TrabajoMesaModel model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id_trabajo_mesa Id Trabajo Mesa
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id_trabajo_mesa)
    {
        $model = $this->findModel($id_trabajo_mesa);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id_trabajo_mesa' => $model->id_trabajo_mesa]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing TrabajoMesaModel model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id_trabajo_mesa Id Trabajo Mesa
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id_trabajo_mesa)
    {
        $this->findModel($id_trabajo_mesa)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the TrabajoMesaModel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id_trabajo_mesa Id Trabajo Mesa
     * @return TrabajoMesaModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id_trabajo_mesa)
    {
        if (($model = TrabajoMesaModel::findOne(['id_trabajo_mesa' => $id_trabajo_mesa])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
