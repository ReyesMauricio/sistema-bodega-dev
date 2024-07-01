<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "TIPOREGISTRO".
 *
 * @property int $IdTipoRegistro
 * @property string|null $TipoRegistro
 *
 * @property REGISTRO[] $rEGISTROs
 */
class TipoRegistroModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'TIPOREGISTRO';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['TipoRegistro'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'IdTipoRegistro' => 'Id Tipo Registro',
            'TipoRegistro' => 'Tipo Registro',
        ];
    }

    /**
     * Gets query for [[REGISTROs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRegistros()
    {
        return $this->hasMany(RegistroModel::class, ['IdTipoRegistro' => 'IdTipoRegistro']);
    }
}
