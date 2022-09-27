<?php

namespace IntegratedLibrarySystem\Core\Controllers;

use InfinityBrackets\Core\Application;
use InfinityBrackets\Core\Controller;
use InfinityBrackets\Core\Database;
use InfinityBrackets\Exception\BadRequestException;
use InfinityBrackets\Exception\UnauthorizedException;
use IntegratedLibrarySystem\Core\Controllers\GoogleUserInfo;
use IntegratedLibrarySystem\Core\Controllers\User;

class AuthController extends Controller {

    public function GetGoogleSignInURL() {
        return Application::$app->config->auth->google->url;
    }

    public function SignIn() {
        Application::$app->response->Redirect($this->GetGoogleSignInURL());
    }

    public function Verify() {
        if(!Application::$app->session->GetAuth()) {
            return FALSE;
        }
        return TRUE;
    }

    public function Verification() {
        Application::$app->ToJSON(array('auth' => $this->Verify(), 'url' => $this->GetGoogleSignInURL()));
    }

    public function Auth() {
        $data = Application::$app->request->GetBody();

        if(isset($data['code']) && !empty($data['code'])) {
            // Initialize Google Client
            $client = Application::$app->services->googleService->client;

            // Set Google Token
            $token = $client->fetchAccessTokenWithAuthCode($data['code']);
            $client->setAccessToken($token);

            // Initialize Google Service Oauth2 and retrive user Google Information
            $gauth = new \Google\Service\Oauth2($client);
            $googleInfo = $gauth->userinfo->get();

            // Create google user record if not exists
            $googleUserInfo = new GoogleUserInfo();
            $googleUserInfo->Create($googleInfo);

            if($googleInfo->hd == "cvsu.edu.ph") {
                // Valid user
                $googleUserInfoId = $googleUserInfo->GetIdByEmail($googleInfo->email);

                // Find user
                $user = new User();
                $user->Create($googleUserInfoId, $googleInfo->email);

                if($user->GetActive()) {
                    // Set UserID as Session ID
                    Application::$app->session->Auth($user->GetId());

                    // Refresh page
                    Application::$app->session->SetSwal('success', "Welcome to CvSU ILS!", "Successfully signed in");
                    Application::$app->response->Redirect();
                } else {
                    throw new UnauthorizedException();
                }
            } else {
                throw new UnauthorizedException();
            }
        } else {
            throw new BadRequestException();
        }
    }

    public function DeAuth() {
        // Check Session User
        if(!Application::$app->session->GetAuth()) {
            return FALSE;
        }

        // Unset Session User
        Application::$app->session->DeAuth();

        // Redirect to landing
        Application::$app->session->SetSwal('success', "Signed out!", "Successfully signed out");
        Application::$app->response->Redirect();
    }
}