<?php

namespace IntegratedLibrarySystem\Core\Controllers;

use InfinityBrackets\Core\Application;
use InfinityBrackets\Core\Controller;
use InfinityBrackets\Core\Database;

class GoogleUserInfo {

    public function Create($googleInfo = NULL) {
        if(is_null($googleInfo)) {
            return FALSE;
        }
        $db = new Database('ils');
        if($db->CountTable("google_userinfo", "WHERE `gu_email` = :in_email", ['in_email' => $googleInfo->email]) <= 0) {
            $db->InsertOne("google_userinfo", ['gu_gid', 'gu_email', 'gu_givenName', 'gu_familyName', 'gu_name', 'gu_picture', 'gu_verifiedEmail', 'gu_hd'], [':in_id' => $googleInfo->id, ':in_email' => $googleInfo->email, ':in_given_name' => $googleInfo->givenName, ':in_family_name' => $googleInfo->familyName, ':in_name' => $googleInfo->name, ':in_picture' => $googleInfo->picture, ':in_verifiedEmail' => $googleInfo->verifiedEmail, ':in_hd' => $googleInfo->hd]);
        }
    }

    public function GetIdByEmail($email = NULL) {
        if(is_null($email)) {
            return FALSE;
        }

        // Init Database
        $db = new Database('ils');

        // Check record if exists
        if($db->CountTable("google_userinfo", "WHERE `gu_email` = :in_email", ['in_email' => $email]) <= 0) {
            return FALSE;
        }

        return $db->SelectOne("SELECT `gu_id` FROM `google_userinfo` WHERE `gu_email` = :in_email", ['in_email' => $email])->Get()['gu_id'];
    }
}