<?php

namespace frontend\controllers;

use frontend\models\forms\UsersFilters;
use frontend\models\forms\UsersForm;
use yii;
use yii\web\Controller;

class UsersController extends Controller
{
    public function actionIndex()
    {
        $usersForm = new UsersForm();
        $usersFilters = new UsersFilters();

        $users = [];
        if ($usersForm->load(Yii::$app->request->post()) === false) {
            $users = $usersFilters->getContractors();
        } elseif ($search = $usersForm->search) {
            unset($usersForm);
            $usersForm = new UsersForm();
            $usersForm->search = $search;

            $users = $usersFilters->getContractors($usersForm);
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
