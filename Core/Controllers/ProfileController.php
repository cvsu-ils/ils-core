<?php

namespace IntegratedLibrarySystem\Core\Controllers;

use InfinityBrackets\Core\Application;
use InfinityBrackets\Core\Controller;
use InfinityBrackets\Core\Database;
use InfinityBrackets\Core\Request;
use InfinityBrackets\Core\Response;
use IntegratedLibrarySystem\Core\Middlewares\ProfileMiddleware;
use InfinityBrackets\Middlewares\AuthMiddleware;
use IntegratedLibrarySystem\Core\Models\Campus;
use IntegratedLibrarySystem\Core\Models\College;
use IntegratedLibrarySystem\Core\Models\Course;
use IntegratedLibrarySystem\Core\Models\Office;
use IntegratedLibrarySystem\Core\Models\Student;

class ProfileController extends Controller {

    public function __construct() {
        $this->BindModel([College::class, Campus::class, College::class, Office::class, Course::class, Student::class]);
        $this->RegisterMiddleware(new AuthMiddleware(['Index', 'Create']));
        $this->RegisterMiddleware(new ProfileMiddleware(['Index', 'Create']));
    }

    public function Index() {
        $this->SetLayout('admin');
        Application::$app->view->title = Application::$app->user->google_userinfo->gu_name . " &sdot; CvSU ILS";
        return $this->Render('profile/index');
    }

    public function Create(Request $request, Response $response) {
        if($request->GetMethod() === 'get') {
            $next = $request->GetValue('next');
            $this->SetLayout('admin');

            $campuses = $this->models['Campus']->All(1)->ToObject();
            $colleges = $this->models['College']->All(1)->ToObject();
            $offices = $this->models['Office']->All(1)->ToObject();
            $student = $this->models['Student']->WhereFirst('email', Application::$app->user->user->email)->ToObject();

            Application::$app->view->title = "Create Profile &sdot; CvSU ILS";
            return $this->Render('profile/create', [
                'user' => Application::$app->user,
                'campuses' => $campuses,
                'colleges' => $colleges,
                'offices' => $offices,
                'tempInfo' => $student,
                'next' => $next ?? FALSE
            ]);
        }
        if($request->GetMethod() === 'post') {
            $data = $request->GetBody();
            switch($data['userType']) {
                case "Student":
                    $userType = 1;

                    if($data['campus'] != 1) {
                        $data['college'] = NULL;
                        $data['course'] = NULL;
                    }

                    $data['office'] = NULL;
                    $data['employeeId'] = NULL;
                    $data['position'] = NULL;
                    break;
                case "Faculty":
                    $userType = 2;

                    if($data['campus'] != 1) {
                        $data['college'] = NULL;
                    }

                    $data['studentNumber'] = NULL;
                    $data['course'] = NULL;
                    $data['office'] = NULL;
                    break;
                case "Staff":
                    $userType = 3;

                    if($data['campus'] != 1) {
                        $data['office'] = NULL;
                    }

                    $data['studentNumber'] = NULL;
                    $data['college'] = NULL;
                    $data['course'] = NULL;
                    break;
            }

            $db = new Database('ils');
            $id = $db->InsertOne("profiles", ['first_name', 'middle_name', 'last_name', 'campus_id', 'user_type_id', 'employee_id', 'position', 'office_id', 'student_number', 'college_id', 'course_id', 'sex', 'address', 'mobile_number'], [
                ':in_first_name' => $data['firstName'],
                ':in_middle_name' => $data['middleName'] ?? NULL,
                ':in_last_name' => $data['lastName'],
                ':in_campus_id' => $data['campus'],
                ':in_user_type_id' => $userType,
                ':in_employee_id' => $data['employeeId'],
                ':in_position' => $data['position'],
                ':in_office_id' => $data['office'],
                ':in_student_number' => $data['studentNumber'],
                ':in_college_id' => $data['college'],
                ':in_course_id' => $data['course'],
                ':in_sex' => $data['sex'],
                ':in_address' => $data['address'],
                ':in_mobile_number' => $data['mobileNumber']
            ]);
            $db->Update("users", ['profile_id' => ':in_id'], "WHERE `email` = :in_email", ['in_email' => Application::$app->user->user->email, 'in_id' => $id]);

            Application::$app->ToJSON(array('data' => $data, 'status' => "success"));
        }
    }

    public function HasProfile() {
        return !empty(Application::$app->user->user->profile_id) ? TRUE : FALSE;
    }

    public function GetCourses(Request $request) {
        $id = (int)$request->GetBody()['id'];
        
        $courses = $this->models['Course']->Where('college_id', $id)->ToObject();

        $data = '<option value="" selected disabled>Choose course...</option>';
        if($courses) {
            foreach($courses as $course) {
                $data .= '<option value="' . $course->id . '">' . $course->abbr . '</option>';
            }
        }

        Application::$app->ToJSON($data);
    }
}