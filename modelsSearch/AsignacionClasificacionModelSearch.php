<?php

namespace app\modelsSearch;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\AsignacionClasificacion;

/**
 * AsignacionClasificacionModelSearch represents the model behind the search form of `app\models\AsignacionClasificacion`.
 */
class AsignacionClasificacionModelSearch extends AsignacionClasificacion
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['IdASignacionClasificacion', 'NumeroMesa'], 'integer'],
            [['Libras', 'Costo'], 'number'],
            [['Documento_inv'], 'string'],
            [['Fecha', 'CreateDate'], 'safe'],
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
    public function search($params, $where)
    {
        $query = AsignacionClasificacion::find();
        if($where != null){
            $query = AsignacionClasificacion::find()
            ->select('NumeroMesa, SUM(Libras) as Libras, SUM(Costo) as Costo')
            ->groupBy('NumeroMesa');
        }
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'IdASignacionClasificacion' => $this->IdASignacionClasificacion,
            'Libras' => $this->Libras,
            'Costo' => $this->Costo,
            'NumeroMesa' => $this->NumeroMesa,
            'Fecha' => $this->Fecha,
            'CreateDate' => $this->CreateDate,
        ]);
        $query->andFilterWhere(['like', 'Documento_inv', $this->Documento_inv]);

        return $dataProvider;
    }
}
