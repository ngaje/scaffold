<?php
namespace Ngaje\Scaffold;

interface ICronTask
{
    public function executeCronTask(Request $request = null);
}
