<?php

namespace IntegratedLibrarySystem\Core\Controllers;

use InfinityBrackets\Core\Application;
use InfinityBrackets\Core\Controller;
use InfinityBrackets\Core\Database;
use InfinityBrackets\Core\Request;
use IntegratedLibrarySystem\Core\Models\Campus;
use IntegratedLibrarySystem\Core\Models\College;
use IntegratedLibrarySystem\Core\Models\Office;
use IntegratedLibrarySystem\Core\Models\Course;
use IntegratedLibrarySystem\Core\Models\Guest;

class GuestController extends Controller {

    public function __construct() {
        $this->BindModel([College::class, Campus::class, Office::class, Course::class, Guest::class]);
    }

    public function SignIn(Request $request) {
        $this->SetLayout('ebooks');
        $module = $request->GetValue('next');
        $id = $request->GetValue('id');
        Application::$app->view->title = "Sign In &sdot; CvSU ILS &sdot; Official Home Page";

        $colleges = $this->models['College']->All(1)->ToObject();
        $campuses = $this->models['Campus']->All(1)->ToObject();
        $offices = $this->models['Office']->All(1)->ToObject();
        $courses = $this->models['Course']->All(1)->ToObject();

        return $this->Render('guest/landing', [
            'colleges' => $colleges,
            'campuses' => $campuses,
            'offices' => $offices,
            'courses' => $courses,
            'module' => $module,
            'id' => $id
        ]);
    }

    public function HasAuthUserOrGuest() {
        if(Application::$app->user && !empty(Application::$app->user->user->profile_id)) {
            return TRUE;
        }
        if(Application::$app->guest) {
            return TRUE;
        }
        return FALSE;
    }

    public function RedirectUserOrGuest($redirect = NULL) {
        $url = './?view=auth';
        if(Application::$app->user && empty(Application::$app->user->user->profile_id)) {
            $url = '?view=createprofile';
        }
        return Application::$app->response->Header($url . (!is_null($redirect) ? '&next=' . $redirect : ''));
    }

    public function IsGuest() {
        if(!Application::$app->user && Application::$app->guest) {
            return TRUE;
        }
        return FALSE;
    }

    public function GetCourses(Request $request) {
        $id = (int)$request->GetValue('id');
        
        $courses = $this->models['Course']->Where('college_id', $id)->ToObject();

        Application::$app->ToJSON(['data' => $courses]);
    }
    
    public function Auth(Request $request) {
        $module = $request->GetValue('module');
        $id = $request->GetValue('id');
        $guestId = uniqid();

        $this->models['Guest']->Create([
            'id' => $guestId,
            'campus' => $request->GetValue('campus'),
            'userType' => $request->GetValue('user'),
            'college' => $request->GetValue('college') ?? NULL,
            'course' => $request->GetValue('course') ?? NULL,
            'office' => $request->GetValue('office') ?? NULL
        ], TRUE);

        // Set UserID as Session ID
        Application::$app->session->AuthGuest($guestId);

        Application::$app->ToJSON([
            'status' => "success",
            'title' => "Welcome to CvSU ILS!",
            'message' => "Successfully signed in",
            'redirect' => "?view=" . $module . ($id ? '&id=' . $id : '')
        ]);
    }

    public function FindGuestUser($guestId = NULL) {
        if(is_null($guestId)) {
            return FALSE;
        }
        $db = new Database('ils');
        if($db->CountTable("guests", "WHERE `id` = :in_id", [':in_id' => $guestId]) <= 0) {
            return FALSE;
        }
        $guest = $db->SelectOne("SELECT * FROM `guests` WHERE `id` = :in_id", [':in_id' => $guestId])->Get();     

        return Application::$app->ToObject($guest);
    }
}