<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "curso".
 *
 * @property int $id
 * @property string $titulo
 * @property string $descripcion
 * @property string $duracion
 * @property string $nivel
 * @property int $ruta_aprendizaje_id
 * @property bool $active
 * @property string|null $update_ts
 * @property string $create_ts
 *
 * @property Clase[] $clases
 * @property Inscripcion[] $inscripcions
 * @property RutaAprendizaje $rutaAprendizaje
 */
class Curso extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'curso';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['titulo', 'descripcion', 'duracion', 'nivel', 'ruta_aprendizaje_id'], 'required'],
            [['ruta_aprendizaje_id'], 'default', 'value' => null],
            [['ruta_aprendizaje_id'], 'integer'],
            [['active'], 'boolean'],
            [['update_ts', 'create_ts'], 'safe'],
            [['titulo'], 'string', 'max' => 50],
            [['descripcion'], 'string', 'max' => 80],
            [['duracion'], 'string', 'max' => 15],
            [['nivel'], 'string', 'max' => 10],
            [['ruta_aprendizaje_id'], 'exist', 'skipOnError' => true, 'targetClass' => RutaAprendizaje::class, 'targetAttribute' => ['ruta_aprendizaje_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'titulo' => 'Titulo',
            'descripcion' => 'Descripcion',
            'duracion' => 'Duracion',
            'nivel' => 'Nivel',
            'ruta_aprendizaje_id' => 'Ruta Aprendizaje ID',
            'active' => 'Active',
            'update_ts' => 'Update Ts',
            'create_ts' => 'Create Ts',
        ];
    }

    /**
     * Gets query for [[Clases]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClases()
    {
        return $this->hasMany(Clase::class, ['curso_id' => 'id']);
    }

    /**
     * Gets query for [[Inscripcions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInscripcions()
    {
        return $this->hasMany(Inscripcion::class, ['curso_id' => 'id']);
    }

    /**
     * Gets query for [[RutaAprendizaje]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRutaAprendizaje()
    {
        return $this->hasOne(RutaAprendizaje::class, ['id' => 'ruta_aprendizaje_id']);
    }
}
