<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "inscripcion".
 *
 * @property int $id
 * @property int $curso_id
 * @property int $estudiante_id
 * @property string $fecha_inscripcion
 * @property bool $aprobado
 *
 * @property Curso $curso
 * @property Estudiante $estudiante
 */
class Inscripcion extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'inscripcion';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['curso_id', 'estudiante_id'], 'required'],
            [['curso_id', 'estudiante_id'], 'default', 'value' => null],
            [['curso_id', 'estudiante_id'], 'integer'],
            [['fecha_inscripcion'], 'safe'],
            [['aprobado'], 'boolean'],
            [['curso_id'], 'exist', 'skipOnError' => true, 'targetClass' => Curso::class, 'targetAttribute' => ['curso_id' => 'id']],
            [['estudiante_id'], 'exist', 'skipOnError' => true, 'targetClass' => Estudiante::class, 'targetAttribute' => ['estudiante_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'curso_id' => 'Curso ID',
            'estudiante_id' => 'Estudiante ID',
            'fecha_inscripcion' => 'Fecha Inscripcion',
            'aprobado' => 'Aprobado',
        ];
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
     * Gets query for [[Estudiante]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getEstudiante()
    {
        return $this->hasOne(Estudiante::class, ['id' => 'estudiante_id']);
    }
}
