<?php

namespace IntegratedLibrarySystem\Core\Models;

use InfinityBrackets\Exceptional\Model;
use InfinityBrackets\Exceptional\ExceptionalDatabase;

class Office extends Model {
    protected string $application = ExceptionalDatabase::ILS;
    protected string $table = 'master_offices';
}