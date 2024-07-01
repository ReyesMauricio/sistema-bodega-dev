<?php

namespace app\modules\auth\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\UsuarioBodega;

/**
 * UsuarioBodegaSearch represents the model behind the search form of `app\models\UsuarioBodega`.
 */
class UsuarioBodegaSearch extends UsuarioBodega
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['USUARIO', 'BODEGA', 'CAJA', 'PAQUETE', 'CORREOSUP', 'CORREOTIENDA', 'BASE', 'HAMACHI', 'CENTRO_COSTO', 'TIPO', 'ESQUEMA', 'PREFIJODOC', 'RESOL_ACTUAL', 'SERIEMAQUINA', 'NUM_AUTORIZACION', 'FECHA_AUTORIZACION', 'CANT_LIMITE_TICKET', 'RESOLUCION', 'FECHA_SOLICITUD', 'SERIE'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = UsuarioBodega::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere(['like', 'USUARIO', $this->USUARIO])
            ->andFilterWhere(['like', 'BODEGA', $this->BODEGA])
            ->andFilterWhere(['like', 'CAJA', $this->CAJA])
            ->andFilterWhere(['like', 'PAQUETE', $this->PAQUETE])
            ->andFilterWhere(['like', 'CORREOSUP', $this->CORREOSUP])
            ->andFilterWhere(['like', 'CORREOTIENDA', $this->CORREOTIENDA])
            ->andFilterWhere(['like', 'BASE', $this->BASE])
            ->andFilterWhere(['like', 'HAMACHI', $this->HAMACHI])
            ->andFilterWhere(['like', 'CENTRO_COSTO', $this->CENTRO_COSTO])
            ->andFilterWhere(['like', 'TIPO', $this->TIPO])
            ->andFilterWhere(['like', 'ESQUEMA', $this->ESQUEMA])
            ->andFilterWhere(['like', 'PREFIJODOC', $this->PREFIJODOC])
            ->andFilterWhere(['like', 'RESOL_ACTUAL', $this->RESOL_ACTUAL])
            ->andFilterWhere(['like', 'SERIEMAQUINA', $this->SERIEMAQUINA])
            ->andFilterWhere(['like', 'NUM_AUTORIZACION', $this->NUM_AUTORIZACION])
            ->andFilterWhere(['like', 'FECHA_AUTORIZACION', $this->FECHA_AUTORIZACION])
            ->andFilterWhere(['like', 'CANT_LIMITE_TICKET', $this->CANT_LIMITE_TICKET])
            ->andFilterWhere(['like', 'RESOLUCION', $this->RESOLUCION])
            ->andFilterWhere(['like', 'FECHA_SOLICITUD', $this->FECHA_SOLICITUD])
            ->andFilterWhere(['like', 'SERIE', $this->SERIE]);

        return $dataProvider;
    }
}
