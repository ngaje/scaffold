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

    public function runCronJobs($task = 'ALL')
    {
        $task = $task == null ? 'ALL' : $task;
        foreach ($this->cron_tasks as $cron_task) {
            if ($task == 'ALL' || strpos(strtolower(get_class($cron_task)), strtolower($task)) !== false) {
                $cron_task->executeCronTask();
            }
        }
    }
}