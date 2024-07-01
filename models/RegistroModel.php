<?php

namespace app\models;

use Yii;
use yii\db\Query;

/**
 * This is the model class for table "REGISTRO".
 *
 * @property int $IdRegistro
 * @property string|null $CodigoBarra
 * @property string|null $Articulo
 * @property string|null $Descripcion
 * @property string|null $Clasificacion
 * @property float|null $Libras
 * @property int|null $Unidades
 * @property int|null $IdTipoEmpaque
 * @property int|null $IdUbicacion
 * @property string|null $EmpacadoPor
 * @property string|null $ProducidoPor
 * @property string|null $BodegaCreacion
 * @property string|null $BodegaActual
 * @property string|null $Observaciones
 * @property string|null $UsuarioCreacion
 * @property string|null $DOCUMENTO_INV
 * @property string|null $Estado
 * @property int|null $Activo
 * @property float|null $Costo
 * @property string|null $FechaCreacion
 * @property string|null $FechaModificacion
 * @property int|null $Sesion
 * @property int|null $IdTipoRegistro
 * @property string $CreateDate
 * @property int|null $ContadorImpresiones
 * @property string|null $EmpresaDestino
 *
 * @property DETALLEDESGLOSE[] $dETALLEDESGLOSEs
 * @property DETALLEREGISTRO[] $dETALLEREGISTROs
 * @property TIPOEMPAQUE $idTipoEmpaque
 * @property TIPOREGISTRO $idTipoRegistro
 * @property UBICACION $idUbicacion
 * @property TRANSACCION[] $tRANSACCIONs
 * @property USUARIO $usuarioCreacion
 */
class RegistroModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'REGISTRO';
    }



    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['Libras', 'Costo'], 'number'],
            [['Unidades', 'IdTipoEmpaque', 'IdUbicacion', 'Activo', 'Sesion', 'IdTipoRegistro', 'ContadorImpresiones', 'MesaOrigen'], 'integer'],
            [['Observaciones'], 'string'],
            [['FechaCreacion', 'FechaModificacion', 'CreateDate'], 'safe'],
            [['CodigoBarra'], 'string', 'max' => 25],
            [['Articulo'], 'string', 'max' => 90],
            [['Clasificacion', 'UsuarioCreacion', 'DOCUMENTO_INV', 'Estado', 'EmpresaDestino'], 'string', 'max' => 50],
            [['Descripcion'], 'string', 'max' => 250],
            [['BodegaCreacion', 'BodegaActual'], 'string', 'max' => 10],
            [['Unidades', 'Libras', 'MesaOrigen', 'BodegaActual', 'EmpacadoPor', 'ProducidoPor', 'IdTipoEmpaque', 'FechaCreacion', 'Clasificacion', 'Articulo', 'EmpresaDestino'], 'required',],
            [['UsuarioCreacion'], 'exist', 'skipOnError' => true, 'targetClass' => UsuarioModel::class, 'targetAttribute' => ['UsuarioCreacion' => 'Usuario']],
            [['IdTipoRegistro'], 'exist', 'skipOnError' => true, 'targetClass' => TipoRegistroModel::class, 'targetAttribute' => ['IdTipoRegistro' => 'IdTipoRegistro']],
            [['IdTipoEmpaque'], 'exist', 'skipOnError' => true, 'targetClass' => TipoEmpaqueModel::class, 'targetAttribute' => ['IdTipoEmpaque' => 'IdTipoEmpaque']],
            [['IdUbicacion'], 'exist', 'skipOnError' => true, 'targetClass' => UbicacionModel::class, 'targetAttribute' => ['IdUbicacion' => 'IdUbicacion']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'IdRegistro' => 'Id',
            'CodigoBarra' => 'Codigo de barra',
            'Articulo' => 'Articulo',
            'Descripcion' => 'Descripcion',
            'Clasificacion' => 'Clasificacion',
            'Libras' => 'Libras',
            'Unidades' => 'Unidades',
            'IdTipoEmpaque' => 'Tipo de empaque',
            'IdUbicacion' => 'Ubicacion',
            'EmpacadoPor' => 'Empacado Por',
            'ProducidoPor' => 'Producido Por',
            'BodegaCreacion' => 'Bodega creacion',
            'BodegaActual' => 'Bodega actual',
            'Observaciones' => 'Observaciones',
            'UsuarioCreacion' => 'Usuario creacion',
            'DOCUMENTO_INV' => 'Documento inventario',
            'Estado' => 'Estado',
            'Activo' => 'Activo',
            'Costo' => 'Costo',
            'FechaCreacion' => 'Fecha de producción',
            'FechaModificacion' => 'Fecha Modificacion',
            'Sesion' => 'Sesion',
            'IdTipoRegistro' => 'Tipo de registro',
            'CreateDate' => 'Create Date',
            'ContadorImpresiones' => 'Contador Impresiones',
            'EmpresaDestino' => 'Empresa destino',
            'MesaOrigen' => 'Mesa de origen'
        ];
    }

    public static function obtenerCodigoBarra($codigoBarra)
    {
        $model = RegistroModel::find()->where(['CodigoBarra' => $codigoBarra])->one();
        $detalle = DetalleRegistroModel::find()->where("IdRegistro = $model->IdRegistro")->all();
        foreach ($detalle as $d) {
            $clasificacion = Yii::$app->db2->createCommand("SELECT DESCRIPCION, CLASIFICACION_2 FROM CONINV.ARTICULO WHERE ARTICULO = '$d->ArticuloDetalle'")->queryOne();
            $d->ArticuloDetalle =  $d->ArticuloDetalle . " - " . $clasificacion["DESCRIPCION"] . ' - ' . $clasificacion["CLASIFICACION_2"];
        }
        $out = ['model' => $model, 'detalle' => $detalle];
        return $out;
    }

    public static function obtenerCodigosExistentes($articulo)
    {
        $data = RegistroModel::find()->where(
            "CreateDate > '2023-10-17 00:00:00:000'
            AND (Activo = 1 OR Activo IS NULL)
            AND Estado NOT LIKE 'ELIMINADO'
            AND articulo = '$articulo'
            AND UsuarioCreacion NOT LIKE '%AUDITORIA%'
            AND CodigoBarra NOT IN (SELECT CodigoBarra FROM BODEGA.dbo.DETALLEMOVIMIENTO)"
        )->all();

        return $data;
    }


    public static function listaCodigos(){
        $query = new Query();
        $result = $query->select('*')->from('REGISTRO')->limit(100)->all();  
        return $result;  
    }

    public static function getNextCodigoBarra()
    {
        $maxCodigoBarra = static::find()
        ->select(['maxCodigoBarra' => new \yii\db\Expression('MAX(CAST(CodigoBarra AS BIGINT))')])
        ->where(new \yii\db\Expression('ISNUMERIC(CodigoBarra) = 1'))
        ->scalar();

        // Calcular el siguiente código de barras
        return $maxCodigoBarra !== null ? $maxCodigoBarra + 1 : 1;
    }

    public function getDetalleMovimiento()
    {
        return $this->hasMany(DetalleMovimientoModel::class, ['CodigoBarra' => 'CodigoBarra']);
    }

    

    /**
     * Gets query for [[DETALLEREGISTROs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDetalleRegistro()
    {
        return $this->hasMany(DetalleRegistroModel::class, ['IdRegistro' => 'IdRegistro']);
    }

    /**
     * Gets query for [[IdTipoEmpaque]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTipoEmpaque()
    {
        return $this->hasOne(TipoEmpaqueModel::class, ['IdTipoEmpaque' => 'IdTipoEmpaque']);
    }

    /**
     * Gets query for [[IdTipoRegistro]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTipoRegistro()
    {
        return $this->hasOne(TipoRegistroModel::class, ['IdTipoRegistro' => 'IdTipoRegistro']);
    }

    /**
     * Gets query for [[IdUbicacion]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUbicacion()
    {
        return $this->hasOne(UbicacionModel::class, ['IdUbicacion' => 'IdUbicacion']);
    }

    /**
     * Gets query for [[TRANSACCIONs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTRANSACCIONs()
    {
        return $this->hasMany(TransaccionModel::class, ['CodigoBarra' => 'CodigoBarra']);
    }

    public function getTransacciones()
    {
        return $this->hasMany(TransaccionModel::class, ['CodigoBarra' => 'CodigoBarra']);
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
}
