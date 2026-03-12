<?php

namespace app\rbac;

use Yii;
use yii\rbac\Rule;

abstract class BaseOwnerRule extends Rule
{
    protected function isAdmin(int $userId): bool
    {
        if ($userId <= 0) {
            return false;
        }

        $auth = Yii::$app->authManager;
        if ($auth === null) {
            return false;
        }

        return isset($auth->getAssignments($userId)['admin']);
    }
}
