<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "curso".
 *
 * @property int $id
 * @property string $name
 * @property string $informacion
 * @property string $duracion
 * @property string $nivel
 * @property int $ruta_aprendizaje_id
 * @property bool $active
 * @property string|null $update_ts
 * @property string $create_ts
 * @property string|null $url_image
 * @property bool|null $is_free
 * @property int|null $students_count
 * @property string|null $subtitle
 * @property string|null $you_learn
 * @property string|null $addressed_to
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
            [['name', 'informacion', 'duracion', 'nivel', 'ruta_aprendizaje_id', 'active'], 'required'],
            [['informacion', 'you_learn'], 'string'],
            [['ruta_aprendizaje_id', 'students_count'], 'default', 'value' => null],
            [['ruta_aprendizaje_id', 'students_count'], 'integer'],
            [['active', 'is_free'], 'boolean'],
            [['update_ts', 'create_ts'], 'safe'],
            [['name', 'addressed_to'], 'string', 'max' => 50],
            [['duracion'], 'string', 'max' => 15],
            [['nivel'], 'string', 'max' => 10],
            [['url_image'], 'string', 'max' => 30],
            [['subtitle'], 'string', 'max' => 80],
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
            'name' => 'Name',
            'informacion' => 'Informacion',
            'duracion' => 'Duracion',
            'nivel' => 'Nivel',
            'ruta_aprendizaje_id' => 'Ruta Aprendizaje ID',
            'active' => 'Active',
            'update_ts' => 'Update Ts',
            'create_ts' => 'Create Ts',
            'url_image' => 'Url Image',
            'is_free' => 'Is Free',
            'students_count' => 'Students Count',
            'subtitle' => 'Subtitle',
            'you_learn' => 'You Learn',
            'addressed_to' => 'Addressed To',
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
