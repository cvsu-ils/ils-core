<?php

namespace IntegratedLibrarySystem\Core\Models;

use InfinityBrackets\Exceptional\Model;
use InfinityBrackets\Exceptional\ExceptionalDatabase;

class Course extends Model {
    protected string $application = ExceptionalDatabase::ILS;
    protected string $table = 'master_courses';
}