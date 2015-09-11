<?php
namespace Netshine\Scaffold;

interface ICronTask
{
    public function executeCronTask(Request $request = null);
}