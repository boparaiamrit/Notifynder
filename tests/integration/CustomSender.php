<?php

use Boparaiamrit\Notifynder\Contracts\NotifynderSender;
use Boparaiamrit\Notifynder\Contracts\Sender;

/**
 * Class CustomSender.
 */
class CustomDefaultSender implements Sender
{
    /**
     * @var array
     */
    protected $notifications;

    /**
     * @var \Boparaiamrit\Notifynder\NotifynderManager
     */
    private $notifynder;

    /**
     * @param array                        $notifications
     * @param \Boparaiamrit\Notifynder\NotifynderManager $notifynder
     */
    public function __construct(array $notifications, \Boparaiamrit\Notifynder\NotifynderManager $notifynder)
    {
        $this->notifications = $notifications;
        $this->notifynder = $notifynder;
    }

    /**
     * Send notification.
     *
     * @param NotifynderSender $sender
     * @return mixed
     */
    public function send(NotifynderSender $sender)
    {
        //        dd($storeNotification);
        return $sender->send($this->notifications);
    }
}
