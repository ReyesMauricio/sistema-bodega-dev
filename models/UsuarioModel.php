<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "USUARIO".
 *
 * @property int $IdUsuario
 * @property string $Usuario
 * @property string $Nombre
 * @property int $Digita
 * @property int $Produce
 * @property int $Empaca
 * @property int $Activo
 *
 * @property REGISTRO[] $rEGISTROs
 * @property TRANSACCION[] $tRANSACCIONs
 * @property USUARIOPRIVILEGIO[] $uSUARIOPRIVILEGIOs
 */
class UsuarioModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'USUARIO';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['Usuario', 'Nombre'], 'required'],
            [['Digita', 'Produce', 'Empaca', 'Activo'], 'integer'],
            [['Usuario'], 'string', 'max' => 50],
            [['Nombre'], 'string', 'max' => 250],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'IdUsuario' => 'Id Usuario',
            'Usuario' => 'Usuario',
            'Nombre' => 'Nombre',
            'Digita' => 'Digita',
            'Produce' => 'Produce',
            'Empaca' => 'Empaca',
            'Activo' => 'Activo',
        ];
    }

    /**
     * Gets query for [[REGISTROs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getREGISTROs()
    {
        return $this->hasMany(REGISTRO::class, ['UsuarioCreacion' => 'Usuario']);
    }

    /**
     * Gets query for [[TRANSACCIONs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTRANSACCIONs()
    {
        return $this->hasMany(TRANSACCION::class, ['UsuarioCreacion' => 'Usuario']);
    }

    /**
     * Gets query for [[USUARIOPRIVILEGIOs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUSUARIOPRIVILEGIOs()
    {
        return $this->hasMany(USUARIOPRIVILEGIO::class, ['IdUsuario' => 'IdUsuario']);
    }

    public static function saveToDatabase($attributes, $dbName = 'db')
    {
        $model = new static();
        $model->setAttributes($attributes);
        $model->setDb(Yii::$app->$dbName);
        return $model->save();
    }
}
