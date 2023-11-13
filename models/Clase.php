<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "clase".
 *
 * @property int $id
 * @property string $titulo
 * @property string $descripcion
 * @property string|null $duracion
 * @property int $curso_id
 * @property bool $active
 * @property int $numero_clase
 * @property string|null $update_ts
 * @property string $create_ts
 * @property bool|null $is_public
 *
 * @property Avance[] $avances
 * @property Curso $curso
 * @property Pregunta[] $preguntas
 * @property Subject[] $subjects
 */
class Clase extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'clase';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['titulo', 'descripcion', 'curso_id', 'numero_clase'], 'required'],
            [['curso_id', 'numero_clase'], 'default', 'value' => null],
            [['curso_id', 'numero_clase'], 'integer'],
            [['active', 'is_public'], 'boolean'],
            [['update_ts', 'create_ts'], 'safe'],
            [['titulo'], 'string', 'max' => 50],
            [['descripcion'], 'string', 'max' => 80],
            [['duracion'], 'string', 'max' => 10],
            [['curso_id'], 'exist', 'skipOnError' => true, 'targetClass' => Curso::class, 'targetAttribute' => ['curso_id' => 'id']],
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
            'curso_id' => 'Curso ID',
            'active' => 'Active',
            'numero_clase' => 'Numero Clase',
            'update_ts' => 'Update Ts',
            'create_ts' => 'Create Ts',
            'is_public' => 'Is Public',
        ];
    }

    /**
     * Gets query for [[Avances]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAvances()
    {
        return $this->hasMany(Avance::class, ['clase_id' => 'id']);
    }

    /**
     * Gets query for [[Curso]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCurso()
    {
        return $this->hasOne(Curso::class, ['id' => 'curso_id']);
    }

    /**
     * Gets query for [[Preguntas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPreguntas()
    {
        return $this->hasMany(Pregunta::class, ['clase_id' => 'id']);
    }

    /**
     * Gets query for [[Subjects]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubjects()
    {
        return $this->hasMany(Subject::class, ['clase_id' => 'id']);
    }
}
