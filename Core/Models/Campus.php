<?php

namespace IntegratedLibrarySystem\Core\Models;

use InfinityBrackets\Exceptional\Model;
use InfinityBrackets\Exceptional\ExceptionalDatabase;

class Campus extends Model {
    protected string $application = ExceptionalDatabase::ILS;
    protected string $table = 'master_campuses';
}