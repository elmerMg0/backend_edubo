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
            [['descripcion'], 'string'],
            [['numero_cursos'], 'default', 'value' => null],
            [['numero_cursos'], 'integer'],
            [['active'], 'boolean'],
            [['update_ts', 'create_ts'], 'safe'],
            [['nombre', 'slug', 'url_image'], 'string', 'max' => 50],
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
