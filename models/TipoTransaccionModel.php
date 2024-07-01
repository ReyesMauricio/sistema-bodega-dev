<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "TIPOTRANSACCION".
 *
 * @property int $IdTipoTransaccion
 * @property string $TipoTransaccion
 *
 * @property TRANSACCION[] $tRANSACCIONs
 */
    class TipoTransaccionModel extends \yii\db\ActiveRecord
    {
        /**
         * {@inheritdoc}
         */
        public static function tableName()
        {
            return 'TIPOTRANSACCION';
        }

        /**
         * {@inheritdoc}
         */
        public function rules()
        {
            return [
                [['TipoTransaccion'], 'required'],
                [['TipoTransaccion'], 'string', 'max' => 50],
            ];
        }

        /**
         * {@inheritdoc}
         */
        public function attributeLabels()
        {
            return [
                'IdTipoTransaccion' => 'Id Tipo Transaccion',
                'TipoTransaccion' => 'Tipo Transaccion',
            ];
        }

        public function getTransacciones()
        {
            return $this->hasMany(TransaccionModel::class, ['IdTipoTransaccion' => 'IdTipoTransaccion']);
        }

        
    }
