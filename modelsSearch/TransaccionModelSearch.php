<?php

namespace app\modelsSearch;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\TransaccionModel;
use app\models\RegistroModel;

/**
 * TransaccionModelSearch represents the model behind the search form of `app\models\TransaccionModel`.
 */
class TransaccionModelSearch extends TransaccionModel
{
    public $libras;
    public $Articulo;
    public $Descripcion;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['IdTransaccion', 'IdTipoTransaccion'], 'integer'],
            [['CodigoBarra', 'Fecha', 'Bodega', 'Naturaleza', 'Estado', 'UsuarioCreacion', 'UsuarioModificacion', 'FechaCreacion', 'FechaModificacion', 'Documento_Inv','NumeroDocumento'], 'safe'],
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
        
        $query = TransaccionModel::find();

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
            'IdTransaccion' => $this->IdTransaccion,
            'IdTipoTransaccion' => $this->IdTipoTransaccion,
            'Fecha' => $this->Fecha,
            'FechaCreacion' => $this->FechaCreacion,
            'FechaModificacion' => $this->FechaModificacion,
        ]);

        $query->andFilterWhere(['like', 'CodigoBarra', $this->CodigoBarra])
            ->andFilterWhere(['like', 'Bodega', $this->Bodega])
            ->andFilterWhere(['like', 'Naturaleza', $this->Naturaleza])
            ->andFilterWhere(['like', 'Estado', $this->Estado])
            ->andFilterWhere(['like', 'UsuarioCreacion', $this->UsuarioCreacion])
            ->andFilterWhere(['like', 'UsuarioModificacion', $this->UsuarioModificacion])
            ->andFilterWhere(['like', 'Documento_Inv', $this->Documento_Inv])
            ->andFilterWhere(['like', 'NumeroDocumento', $this->NumeroDocumento]);

        return $dataProvider;
    }

    public function searchCajas($params)
    {
        $query = TransaccionModel::find()
        ->alias('t')
        ->joinWith('codigoBarra r')
        ->where(['t.IdTipoTransaccion' => 1010])
        ->andWhere(['or', ['r.Estado' => 'PENDIENTE'], ['r.Estado' => 'FINALIZADO']])
        ->andWhere(['t.Naturaleza' => 'E'])
        ->orderBy(['t.Fecha' => SORT_DESC])
        ->select([
            't.CodigoBarra',
            't.NumeroDocumento',
            't.IdTipoTransaccion',
            't.Documento_Inv',
            'r.Libras',
            'r.Costo',
            'r.Articulo',
            'r.Descripcion',
            'r.Estado',
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // Si la validación falla, retorna todos los registros
            return $dataProvider;
        }

        // Aplicar filtros adicionales si es necesario
        $query->andFilterWhere([
            't.CodigoBarra' => $this->CodigoBarra,
            'r.libras' => $this->libras,
            'r.Articulo' => $this->Articulo,
            'r.Descripcion' => $this->Descripcion,
            't.NumeroDocumento' => $this->NumeroDocumento,
            't.IdTipoTransaccion' => $this->IdTipoTransaccion,
            'r.Estado' => $this->Estado,
        ]);

        return $dataProvider;
    }

    public function searchBarrilesCaja($params, $numeroDocumento, $idTipoTransaccion, $naturaleza)
    {
        $query = TransaccionModel::find()
            ->where(['NumeroDocumento' => $numeroDocumento, 'IdTipoTransaccion' => $idTipoTransaccion, 'Naturaleza' => $naturaleza  ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Agrega aquí otros filtros si es necesario

        return $dataProvider;
    }

    public function searchTransaccion($params)
    {
            $query = TransaccionModel::find()
            ->select([
                'NumeroDocumento',
                'MIN(IdTipoTransaccion) AS IdTipoTransaccion',
                'MIN(CodigoBarra) AS CodigoBarra',
                'MIN(Bodega) AS Bodega',
                'MIN(FechaCreacion) AS FechaCreacion',
                'Estado',
                'Documento_Inv'
            ])
            ->where(['IdTipoTransaccion' => 10])
            ->andWhere(['is not', 'NumeroDocumento', null])
            ->groupBy(['NumeroDocumento', 'Estado', 'Documento_Inv']);
            
            // Ordenar por FechaCreacion de forma descendente (la más reciente primero)
            $query->orderBy(['FechaCreacion' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        // Cargar los parámetros de búsqueda
        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }
        // Filtrar por FechaCreacion si se ha proporcionado
        if (!empty($this->FechaCreacion)) {
            // Convertir la fecha a formato yyyy-mm-dd para ignorar la hora
            $fechaSinHora = Yii::$app->formatter->asDate($this->FechaCreacion, 'yyyy-MM-dd');
            // Aplicar el filtro
            $query->andWhere("CONVERT(date, FechaCreacion) >= :fechaInicio", [':fechaInicio' => $fechaSinHora])
            ->andWhere("CONVERT(date, FechaCreacion) < :fechaFin", [':fechaFin' => date('Y-m-d', strtotime($fechaSinHora . ' +1 day'))]);
        }

        // Filtrar por atributos del modelo principal
        $query->andFilterWhere(['like', 'CodigoBarra', $this->CodigoBarra])
        ->andFilterWhere(['like', 'TipoTransaccion', $this->Bodega])
        ->andFilterWhere(['like', 'NumeroDocumento', $this->NumeroDocumento])
        ->andFilterWhere(['like', 'Estado', $this->Estado])
        ->andFilterWhere(['like', 'Documento_Inv', $this->Documento_Inv]);


        return $dataProvider;
    }

    public function searchTransactionOne($params, $uuid)
    {
        $query = RegistroModel::find()
            ->select(['CodigoBarra', 'Articulo', 'Descripcion', 'Libras'])
            ->where(['DOCUMENTO_INV' => $uuid]); // Usamos el parámetro $uuid en lugar de la cadena estática

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10, // Número de elementos por página
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        return $dataProvider;
    }

}
