<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "UBICACION".
 *
 * @property int $IdUbicacion
 * @property string $Ubicacion
 *
 * @property REGISTRO[] $rEGISTROs
 */
class UbicacionModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'UBICACION';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['Ubicacion'], 'required'],
            [['Ubicacion'], 'string', 'max' => 250],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'IdUbicacion' => 'Id Ubicacion',
            'Ubicacion' => 'Ubicacion',
        ];
    }

    /**
     * Gets query for [[REGISTROs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getREGISTROs()
    {
        return $this->hasMany(REGISTRO::class, ['IdUbicacion' => 'IdUbicacion']);
    }
}
