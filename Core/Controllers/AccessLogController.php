<?php

namespace IntegratedLibrarySystem\Core\Controllers;

use InfinityBrackets\Core\Application;
use InfinityBrackets\Core\Controller;
use InfinityBrackets\Core\Database;
use InfinityBrackets\Core\Request;
use IntegratedLibrarySystem\Core\Models\AccessLog;

class AccessLogController extends Controller {

    public function __construct() {
        $this->RegisterModel(AccessLog::class);
    }

    public function Add($id, $module) {
        if(is_null($id) || is_null($module)) {
            return FALSE;
        }
        $userId = Application::$app->user ? Application::$app->user->user->id : Application::$app->guest->id;
        $userType = Application::$app->user ? 'CVSU' : 'GUEST';
        $dateToday = date('Y-n-j');

        $responseId = NULL;
        $status = FALSE;

        if(!$this->Exists($userId, $id, $module, $dateToday)) {
            $responseId = $this->model->Create([
                'user_id' => $userId,
                'user_type' => $userType,
                'module' => $module,
                'access_id' => $id
            ], TRUE)->Get();
            $status = TRUE;
        }
    }

    public function Exists($userId, $accessId, $module, $dateToday) {
        return $this->model->CountWhere(
            "WHERE `user_id` = :in_user_id AND `access_id` = :in_access_id AND `module` = :in_module AND DATE(`created_at`) = :in_date",
            [
                'in_user_id' => $userId,
                'in_access_id' => $accessId,
                'in_module' => $module,
                'in_date' => $dateToday
            ]
        , TRUE) > 0 ? TRUE : FALSE;
    }
}