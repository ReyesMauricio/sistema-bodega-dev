<?php

namespace app\controllers;

use app\models\UsuarioModel;
use app\modelsSearch\UsuarioModelSearch;
use yii\web\Controller;
use Yii;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * UsuarioController implements the CRUD actions for UsuarioModel model.
 */
class UsuarioController extends Controller
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
     * Lists all UsuarioModel models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new UsuarioModelSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single UsuarioModel model.
     * @param int $IdUsuario Id Usuario
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($IdUsuario)
    {
        return $this->render('view', [
            'model' => $this->findModel($IdUsuario),
        ]);
    }

    /**
     * Creates a new UsuarioModel model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {

        $model = new UsuarioModel();
        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'IdUsuario' => $model->IdUsuario]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing UsuarioModel model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $IdUsuario Id Usuario
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($IdUsuario)
    {
        $model = $this->findModel($IdUsuario);
        

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'IdUsuario' => $model->IdUsuario]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing UsuarioModel model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $IdUsuario Id Usuario
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($IdUsuario)
    {
        $this->findModel($IdUsuario)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the UsuarioModel model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $IdUsuario Id Usuario
     * @return UsuarioModel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($IdUsuario)
    {
        if (($model = UsuarioModel::findOne(['IdUsuario' => $IdUsuario])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
