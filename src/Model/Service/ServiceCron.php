<?php
namespace Netshine\Scaffold\Model\Service;

use Netshine\Scaffold\ICronTask;
use Netshine\Scaffold\Model\ServiceBase;

class ServiceCron extends ServiceBase
{
    protected $cron_tasks = array();

    public function registerTask(ICronTask $cron_task)
    {
        $this->cron_tasks[] = $cron_task;
    }

    public function runCronJobs()
    {
        foreach ($this->cron_tasks as $cron_task) {
            $cron_task->executeCronTask();
        }
    }
}