<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "ruta_aprendizaje".
 *
 * @property int $id
 * @property string $nombre
 * @property string $descripcion
 * @property int $numero_cursos
 * @property bool $active
 * @property string|null $update_ts
 * @property string $create_ts
 * @property string|null $slug
 * @property string|null $url_image
 * @property string|null $subtitle
 * @property string|null $carrers
 * @property string|null $duration
 * @property string|null $admission_mode
 * @property string|null $period
 * @property string|null $url_image_icon
 *
 * @property Curso[] $cursos
 */
class RutaAprendizaje extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ruta_aprendizaje';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre', 'descripcion', 'numero_cursos'], 'required'],
            [['descripcion', 'subtitle', 'carrers'], 'string'],
            [['numero_cursos'], 'default', 'value' => null],
            [['numero_cursos'], 'integer'],
            [['active'], 'boolean'],
            [['update_ts', 'create_ts'], 'safe'],
            [['nombre', 'slug', 'url_image', 'url_image_icon'], 'string', 'max' => 50],
            [['duration'], 'string', 'max' => 30],
            [['admission_mode'], 'string', 'max' => 80],
            [['period'], 'string', 'max' => 15],
            [['nombre'], 'unique'],
            [['slug'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nombre' => 'Nombre',
            'descripcion' => 'Descripcion',
            'numero_cursos' => 'Numero Cursos',
            'active' => 'Active',
            'update_ts' => 'Update Ts',
            'create_ts' => 'Create Ts',
            'slug' => 'Slug',
            'url_image' => 'Url Image',
            'subtitle' => 'Subtitle',
            'carrers' => 'Carrers',
            'duration' => 'Duration',
            'admission_mode' => 'Admission Mode',
            'period' => 'Period',
            'url_image_icon' => 'Url Image Icon',
        ];
    }

    /**
     * Gets query for [[Cursos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCursos()
    {
        return $this->hasMany(Curso::class, ['ruta_aprendizaje_id' => 'id']);
    }
}
