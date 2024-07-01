<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use PDO;
use PDOException;
use yii\helpers\Url;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        if (!isset($_SESSION['user'])) {
            return $this->redirect(Url::to(Yii::$app->request->baseUrl . '/index.php?r=site/login'));
        } else {
            $this->layout = 'main';
            return $this->render('index');
        }
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (isset($_SESSION['user'])) {

            session_destroy();
        }
        $this->layout = 'main-login';

        $model = new LoginForm();
        if ($model->load($this->request->post())) {
            $db = $this->conectarLogin($model->username, $model->password);
            if ($db) {
                $data = $this->SoftlandConn()->createCommand("SELECT USUARIO, BODEGA, HAMACHI, BASE, PAQUETE, ESQUEMA, TIPO
                FROM [PRUEBAS].[dbo].[USUARIOBODEGA]
                WHERE (USUARIO = '$model->username') 
                AND (HAMACHI IS NOT NULL) 
                ORDER BY BODEGA")->queryOne();

                $finalizacion = Yii::$app->db->createCommand(
                    "SELECT * FROM FINALIZACION_DIA WHERE Fecha = '" . date("Y-m-d") . "'"
                )->queryOne();

                if (!$finalizacion) {
                    Yii::$app->db->createCommand(
                        "INSERT INTO FINALIZACION_DIA (Estado, Fecha, FechaCreacion) 
                        VALUES ('SIN FINALIZAR', '" . date("Y-m-d") . "','" . date("Y-m-d H:i:s") . "')"
                    )->execute();
                }

                Yii::$app->session->set('user', $data["USUARIO"]);
                Yii::$app->session->set('esquema', $data["ESQUEMA"]);
                Yii::$app->session->set('paquete', $data["PAQUETE"]);
                return $this->redirect('index');
            } else {
                Yii::$app->session->setFlash('warning', "Usuario o contraseña incorrectos.");
                return $this->render('login', [
                    'model' => $model,
                ]);
            }
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        session_destroy();
        return $this->render('login');
    }

    /** Dbconnection to SoftLand Db
     * 
     */

    function conectarLogin($usuario, $password)
    {
        $conexion = 0;
        try {

            $conexion = new PDO("sqlsrv:server=192.168.0.44;database=PRUEBAS", $usuario, $password);

            $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "<pre class='text-white'>" . $e->getMessage() . "</pre>";
            return false;
        }

        if ($conexion) {
            return true;
        }
    }

    public function SoftlandConn()
    {
        return Yii::$app->db2;
    }

    /**
     * Funcion generica, solo imprime un array de manera bonita, solamente usada para analizar y depurar codigo
     * @param string $datos el array que se desea imprimir
     */
    public function printArrays($datos)
    {
        echo "<pre>";
        print_r($datos);
        echo "</pre>";
        die;
    }
}
