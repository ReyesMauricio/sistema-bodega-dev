<?php

namespace app\models;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "TRANSACCION".
 *
 * @property int $IdTransaccion
 * @property string|null $CodigoBarra
 * @property int|null $IdTipoTransaccion
 * @property string $Fecha
 * @property string|null $Bodega
 * @property string|null $Naturaleza
 * @property string|null $Estado
 * @property string|null $UsuarioCreacion
 * @property string|null $UsuarioModificacion
 * @property string $FechaCreacion
 * @property string|null $FechaModificacion
 * @property string|null $Documento_Inv
 * @property string|null $NumeroDocumento
 *
 * @property REGISTRO $codigoBarra
 * @property TIPOTRANSACCION $idTipoTransaccion
 * @property USUARIO $usuarioCreacion
 */
class TransaccionModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'TRANSACCION';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['IdTipoTransaccion'], 'integer'],
            [['Fecha'], 'required'],
            [['Fecha', 'FechaCreacion', 'FechaModificacion'], 'safe'],
            [['CodigoBarra'], 'string', 'max' => 25],
            [['Bodega'], 'string', 'max' => 10],
            [['Naturaleza', 'Estado'], 'string', 'max' => 1],
            [['UsuarioCreacion', 'UsuarioModificacion', 'Documento_Inv', 'NumeroDocumento'], 'string', 'max' => 50],
            [['UsuarioCreacion'], 'exist', 'skipOnError' => true, 'targetClass' => UsuarioModel::class, 'targetAttribute' => ['UsuarioCreacion' => 'Usuario']],
            [['CodigoBarra'], 'exist', 'skipOnError' => true, 'targetClass' => RegistroModel::class, 'targetAttribute' => ['CodigoBarra' => 'CodigoBarra']],
            [['IdTipoTransaccion'], 'exist', 'skipOnError' => true, 'targetClass' => TipoTransaccionModel::class, 'targetAttribute' => ['IdTipoTransaccion' => 'IdTipoTransaccion']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'IdTransaccion' => 'Id Transaccion',
            'CodigoBarra' => 'Codigo Barra',
            'IdTipoTransaccion' => 'Id Tipo Transaccion',
            'Fecha' => 'Fecha',
            'Bodega' => 'Bodega',
            'Naturaleza' => 'Naturaleza',
            'Estado' => 'Estado',
            'UsuarioCreacion' => 'Usuario Creacion',
            'UsuarioModificacion' => 'Usuario Modificacion',
            'FechaCreacion' => 'Fecha Creacion',
            'FechaModificacion' => 'Fecha Modificacion',
            'Documento_Inv' => 'Documento Inv',
            'NumeroDocumento' => 'Numero Documento',
        ];
    }

    /**
     * Gets query for [[CodigoBarra]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCodigoBarra()
    {
        return $this->hasOne(RegistroModel::class, ['CodigoBarra' => 'CodigoBarra']);
    }

    /**
     * Gets query for [[IdTipoTransaccion]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdTipoTransaccion()
    {
        return $this->hasOne(TipoTransaccionModel::class, ['IdTipoTransaccion' => 'IdTipoTransaccion']);
    }

    public function getTipoTransaccion()
    {
        return $this->hasOne(TipoTransaccionModel::class, ['IdTipoTransaccion' => 'IdTipoTransaccion']);
    } 
    public function getRegistro()
    {
        return $this->hasOne(RegistroModel::class, ['CodigoBarra' => 'CodigoBarra']);
    }

    /**
     * Gets query for [[UsuarioCreacion]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsuarioCreacion()
    {
        return $this->hasOne(UsuarioModel::class, ['Usuario' => 'UsuarioCreacion']);
    }

    /**
     * Función index que devuelve las transacciones con los registros que cumplen con ciertas condiciones.
     * @return ActiveDataProvider
     */
    // public function index()
    // {
    //     // Subconsulta para obtener los IdTransaccion mínimos para cada NumeroDocumento
    //     $subquery = self::find()
    //     ->select('MIN(IdTransaccion) AS minIdTransaccion')
    //     ->where(['IdTipoTransaccion' => 10, 'Documento_Inv' => null])
    //     ->groupBy('NumeroDocumento');

    //     // Consulta principal que une la subconsulta y ordena por fecha descendente 
    //     $query = self::find()
    //         ->where(['IdTipoTransaccion' => 10, 'Documento_Inv' => null])
    //         ->andWhere(['IN', 'IdTransaccion', $subquery])
    //         ->orderBy(['fecha' => SORT_DESC]);

    //     // Proveedor de datos para mostrar los resultados paginados
    //     $dataProvider = new ActiveDataProvider([
    //         'query' => $query,
    //         'pagination' => [
    //             'pageSize' => 10,
    //         ],
    //     ]);

    //     $query->andFilterWhere([
    //         'CodigoBarra' => $this->CodigoBarra
    //     ]);

    //     return $dataProvider;
    // }

    public static function getTotal($provider, $fieldName)
    {
        $total = 0;

        foreach ($provider as $item) {
            $total += $item[$fieldName];
        }

        return $total;
    }
}
