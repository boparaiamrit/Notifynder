<?php

namespace Boparaiamrit\Notifynder\Contracts;

use Boparaiamrit\Notifynder\Handler\NotifynderEvent;

/**
 * Interface NotifyListener.
 *
 * This interface is needed when u want dispatch
 * event through the native laravel dispatcher
 * and not from Notifynder::fire()
 */
interface NotifyListener
{
    /**
     * @return NotifynderEvent
     */
    public function getNotifynderEvent();
}
