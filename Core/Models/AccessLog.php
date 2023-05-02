<?php

namespace IntegratedLibrarySystem\Core\Models;

use InfinityBrackets\Exceptional\Model;
use InfinityBrackets\Exceptional\ExceptionalDatabase;

class AccessLog extends Model {
    protected string $application = ExceptionalDatabase::ILS;
    protected string $table = 'access_logs';

    public function GetUniqueViews($id, $module) {
        return $this->CountWhere(
            "WHERE `access_id` = :in_access_id AND `module` = :in_module GROUP BY `user_id`",
            [
                'in_access_id' => $id,
                'in_module' => $module
            ]
        , TRUE);
    }

    public function GetTodayViews($id, $module) {
        $dateToday = date('Y-n-j');

        return $this->CountWhere(
            "WHERE `access_id` = :in_access_id AND `module` = :in_module AND DATE(`created_at`) = :in_date  GROUP BY `user_id`",
            [
                'in_access_id' => $id,
                'in_module' => $module,
                'in_date' => $dateToday
            ]
        , TRUE);
    }
}