<?php

namespace Topxia\Service\Schedule\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Schedule\Dao\ScheduleDao;

class ScheduleDaoImpl extends BaseDao implements ScheduleDao
{
	protected $table = 'schedule';

	public function addSchedule($schedule)
	{
        $affected = $this->getConnection()->insert($this->table, $schedule);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert schedule error.');
        }
        return $this->getSchedule($this->getConnection()->lastInsertId());
	}

	public function getSchedule($id)
	{
		$sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
	}

	public function findScheduleByPeriod($classId, $startDay, $endDay)
	{
		$sql = "SELECT * FROM {$this->table} WHERE classId = ? AND date >= ? AND date <= ?";
		return $this->getConnection()->fetchAll($sql, array($classId, $startDay, $endDay)) ? : null;
	}
}