<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "estudiante".
 *
 * @property int $id
 * @property string $nombre
 * @property string $apellido
 * @property string $email
 * @property string|null $password_hash
 * @property string $access_token
 * @property int $plan_id
 * @property string $create_ts
 * @property string $update_ts
 * @property int $puntos
 *
 * @property Avance[] $avances
 * @property ClaseLikes[] $claseLikes
 * @property Inscripcion[] $inscripcions
 * @property Resultado[] $resultados
 */
class Estudiante extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'estudiante';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre', 'apellido', 'email', 'access_token', 'plan_id', 'update_ts'], 'required'],
            [['plan_id', 'puntos'], 'default', 'value' => null],
            [['plan_id', 'puntos'], 'integer'],
            [['create_ts', 'update_ts'], 'safe'],
            [['nombre'], 'string', 'max' => 50],
            [['apellido'], 'string', 'max' => 80],
            [['email', 'password_hash', 'access_token'], 'string', 'max' => 250],
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
            'apellido' => 'Apellido',
            'email' => 'Email',
            'password_hash' => 'Password Hash',
            'access_token' => 'Access Token',
            'plan_id' => 'Plan ID',
            'create_ts' => 'Create Ts',
            'update_ts' => 'Update Ts',
            'puntos' => 'Puntos',
        ];
    }

    /**
     * Gets query for [[Avances]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAvances()
    {
        return $this->hasMany(Avance::class, ['estudiante_id' => 'id']);
    }

    /**
     * Gets query for [[ClaseLikes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClaseLikes()
    {
        return $this->hasMany(ClaseLikes::class, ['estudiante_id' => 'id']);
    }

    /**
     * Gets query for [[Inscripcions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getInscripcions()
    {
        return $this->hasMany(Inscripcion::class, ['estudiante_id' => 'id']);
    }

    /**
     * Gets query for [[Resultados]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getResultados()
    {
        return $this->hasMany(Resultado::class, ['estudiante_id' => 'id']);
    }
}
