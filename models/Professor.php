<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "professor".
 *
 * @property string $biography
 * @property int $id
 * @property string $firstname
 * @property string $lastname
 * @property string|null $nickname
 * @property string|null $url_image
 * @property bool|null $active
 *
 * @property Curso[] $cursos
 */
class Professor extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'professor';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['biography', 'firstname', 'lastname'], 'required'],
            [['active'], 'boolean'],
            [['biography'], 'string', 'max' => 100],
            [['firstname', 'lastname', 'url_image'], 'string', 'max' => 50],
            [['nickname'], 'string', 'max' => 30],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'biography' => 'Biography',
            'id' => 'ID',
            'firstname' => 'Firstname',
            'lastname' => 'Lastname',
            'nickname' => 'Nickname',
            'url_image' => 'Url Image',
            'active' => 'Active',
        ];
    }

    /**
     * Gets query for [[Cursos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCursos()
    {
        return $this->hasMany(Curso::class, ['professor_id' => 'id']);
    }
}
