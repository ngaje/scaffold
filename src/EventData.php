<?php
namespace Netshine\Scaffold;

class EventData
{
    /** @var string **/
    public $event_name = '';
    /** @var mixed **/
    public $payload = null;

    public function __construct($event_name, $payload = null)
    {
        $this->event_name = $event_name;
        $this->payload = $payload;
    }
}