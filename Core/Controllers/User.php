<?php

namespace IntegratedLibrarySystem\Core\Controllers;

use InfinityBrackets\Core\Application;
use InfinityBrackets\Core\Controller;
use InfinityBrackets\Core\Database;

class User extends Controller {

    protected $id = NULL;
    protected $active = FALSE;

    public function Create($googleId = NULL, $googleEmail = NULL) {
        if(is_null($googleId)) {
            return FALSE;
        }
        if(is_null($googleEmail)) {
            return FALSE;
        }
        $db = new Database('ils');
        if($db->CountTable("users", "WHERE `gu_id` = :in_google_id", ['in_google_id' => $googleId]) <= 0) {
            $db->InsertOne("users", ['email', 'gu_id'], [':in_email' => $googleEmail, ':in_google_id' => $googleId]);
        }

        $data = $db->SelectOne("SELECT `id`, `active` FROM `users` WHERE `gu_id` = :in_google_id AND `email` = :in_email", [':in_email' => $googleEmail, ':in_google_id' => $googleId])->Get();
        $this->id = $data['id'];
        $this->active = $data['active'];
        return $this;
    }

    public function FindUser($userId = NULL) {
        $masterService = Application::$app->services->masterService;
        if(is_null($userId)) {
            return FALSE;
        }
        $db = new Database('ils');
        if($db->CountTable("users", "WHERE `id` = :in_id", [':in_id' => $userId]) <= 0) {
            return FALSE;
        }

        $user = $db->SelectOne("SELECT * FROM `users` WHERE `id` = :in_id", [':in_id' => $userId])->Get();
        $googleUserInfo = $db->SelectOne("SELECT * FROM `google_userinfo` WHERE `gu_id` = :in_google_userinfo_id", [':in_google_userinfo_id' => $user['gu_id']])->Get();
        $profileInfo = $db->SelectOne("SELECT * FROM `profiles` WHERE `id` = :in_id", [':in_id' => $user['profile_id']])->Get();
        
        // Modify Profile
        $profile = array();
        $userType = array();
        $profile['id'] = $profileInfo['id'];
        $profile['firstName'] = $profileInfo['first_name'];
        $profile['middleName'] = $profileInfo['middle_name'];
        $profile['lastName'] = $profileInfo['last_name'];
        $profile['fullName'] = $profileInfo['first_name'] . " " . $profileInfo['middle_name'] . " " . $profileInfo['last_name'];
        $profile['sex'] = $profileInfo['sex'];
        $profile['address'] = $profileInfo['address'];
        $profile['mobileNumber'] = $profileInfo['mobile_number'];
        $profile['avatar'] = $profileInfo['avatar'] ?? $googleUserInfo['gu_picture'];
        $profile['campus'] = $masterService->Match(['campus' => $profileInfo['campus_id']]);
        $userType = $masterService->Match(['userType' => $profileInfo['user_type_id']]);
        switch($userType['label']) {
            case "Staff":
                $userType['info'] = array(
                    'id' => $profileInfo['employee_id'],
                    'position' => $profileInfo['position'],
                    'office' => $masterService->Match(['office' => $profileInfo['office_id']])
                );
                break;
        }
        $profile['userType'] = $userType;

        $data = array();
        $data['user'] = $user;
        $data['profile'] = $profile;
        $data['google_userinfo'] = $googleUserInfo;

        return $data;        
    }

    public function GetId() {
        return $this->id;
    }

    public function GetActive() {
        return $this->active;
    }
}