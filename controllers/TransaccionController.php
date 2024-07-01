<?php

namespace app\controllers;

use app\models\TransaccionModel;
use app\modelsSearch\TransaccionModelSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * TransaccionController implements the CRUD actions for TransaccionModel model.
 */
class TransaccionController extends Controller
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
     * Lists all TransaccionModel models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new TransaccionModelSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, '');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single TransaccionModel model.
     * @param int $IdTransaccion Id Transaccion
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($IdTransaccion)
    {
        return $this->render('view', [
            'model' => $this->findModel($IdTransaccion),
        ]);
    }

    /**
     * Creates a new TransaccionModel model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new TransaccionModel();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'IdTransaccion' => $model->IdTransaccion]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing TransaccionModel model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $IdTransaccion Id Transaccion
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($IdTransaccion)
    {
        $model = $this->findModel($IdTransaccion);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'IdTransaccion' => $model->IdTransaccion]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing TransaccionModel model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $IdTransaccion Id Transaccion
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($IdTransaccion)
    {
        $this->findModel($IdTransaccion)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the TransaccionModel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $IdTransaccion Id Transaccion
     * @return TransaccionModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($IdTransaccion)
    {
        if (($model = TransaccionModel::findOne(['IdTransaccion' => $IdTransaccion])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
