<?php

namespace app\modules\auth\controllers;

use app\models\UsuarioBodega;
use app\modules\auth\models\UsuarioBodegaSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * UsuarioBodegaController implements the CRUD actions for UsuarioBodega model.
 */
class UsuarioBodegaController extends Controller
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
     * Lists all UsuarioBodega models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new UsuarioBodegaSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single UsuarioBodega model.
     * @param string $USUARIO Usuario
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($USUARIO)
    {
        return $this->render('view', [
            'model' => $this->findModel($USUARIO),
        ]);
    }

    /**
     * Creates a new UsuarioBodega model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new UsuarioBodega();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'USUARIO' => $model->USUARIO]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing UsuarioBodega model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $USUARIO Usuario
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($USUARIO)
    {
        $model = $this->findModel($USUARIO);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'USUARIO' => $model->USUARIO]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing UsuarioBodega model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $USUARIO Usuario
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($USUARIO)
    {
        $this->findModel($USUARIO)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the UsuarioBodega model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $USUARIO Usuario
     * @return UsuarioBodega the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($USUARIO)
    {
        if (($model = UsuarioBodega::findOne(['USUARIO' => $USUARIO])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
