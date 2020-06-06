<?php

namespace frontend\controllers;

use frontend\models\forms\UsersForm;
use frontend\models\forms\UsersFilters;
use yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;


class UsersController extends Controller
{
    public function actionIndex() 
    {
        $usersForm = new UsersForm;
        $usersFilters = new UsersFilters;

        $users = [];
        if ($usersForm->load(Yii::$app->request->post()) === false) {
            $users = $usersFilters->getContractors();
        } else {
            $users = $usersFilters->getContractors($usersForm);
        }

        $rating = $usersFilters->getRating();

        if ($type = Yii::$app->request->get('sorting')) {
            $users = $usersFilters->getSortedUsers($type);
        }

        return $this->render('index', ['users' => $users, 'rating' => $rating, 'usersForm' => $usersForm]);
    }
}
