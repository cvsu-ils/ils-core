<?php

namespace IntegratedLibrarySystem\Core\Middlewares;

use InfinityBrackets\Core\Application;
use InfinityBrackets\Exception\ForbiddenException;
use InfinityBrackets\Middlewares\BaseMiddleware;

class ProfileMiddleware extends BaseMiddleware
{
    public array $actions = [];

    public function __construct(array $actions = []) {
        $this->actions = $actions;
    }

    public function execute() {
        if(empty($this->actions) || in_array(Application::$app->controller->action, $this->actions)) {
            if(Application::$app->controller->action == "Index" && !Application::$app->controller->HasProfile()) {
                Application::$app->response->Header('?view=createprofile');
            }
            if(Application::$app->controller->action == "Create" && Application::$app->controller->HasProfile()) {
                Application::$app->response->Header('?view=profile');
            }
        }
    }
}