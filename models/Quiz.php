<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "quiz".
 *
 * @property int $id
 * @property string $descripcion
 * @property string|null $url_image
 * @property int|null $curso_id
 * @property int|null $ruta_aprendizaje_id
 * @property bool $active
 *
 * @property Curso $curso
 * @property Pregunta[] $preguntas
 */
class Quiz extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'quiz';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['descripcion'], 'required'],
            [['curso_id', 'ruta_aprendizaje_id'], 'default', 'value' => null],
            [['curso_id', 'ruta_aprendizaje_id'], 'integer'],
            [['active'], 'boolean'],
            [['descripcion'], 'string', 'max' => 30],
            [['url_image'], 'string', 'max' => 50],
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
            'descripcion' => 'Descripcion',
            'url_image' => 'Url Image',
            'curso_id' => 'Curso ID',
            'ruta_aprendizaje_id' => 'Ruta Aprendizaje ID',
            'active' => 'Active',
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
     * Gets query for [[Preguntas]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPreguntas()
    {
        return $this->hasMany(Pregunta::class, ['quiz_id' => 'id']);
    }
}
