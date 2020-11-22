<?php

namespace frontend\models\forms;

use frontend\models\db\Users;
use Yii;
use yii\base\Model;

class LoginForm extends Model
{
    public $email;
    public $password;

    private $userByForm;

    public function rules()
    {
        return [
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],

            ['password', 'required'],
            ['password', 'validatePassword'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'email' => 'Email',
            'password' => 'Пароль',
        ];
    }

    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $userByForm = $this->getUser();
            $validatePassword = Yii::$app
                ->getSecurity()
                ->validatePassword($this->password, $userByForm->password_key);

            if (!$this->userByForm || !$validatePassword) {
                $this->addError($attribute, 'Неправильный email или пароль');

                echo 'wrong';
            }
        }
    }

    public function getUser()
    {
        if ($this->userByForm === null) {
            $this->userByForm = Users::findOne(['email' => $this->email]);
        }

        return $this->userByForm;
    }
}
