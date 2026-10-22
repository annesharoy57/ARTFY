<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class ArtfyDatabaseNotification extends Notification
{
    use Queueable;

    protected string $notificationType;

    protected array $notificationData;

    public function __construct(string $type, array $data)
    {
        $this->notificationType = $type;
        $this->notificationData = $data;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function databaseType(object $notifiable): string
    {
        return $this->notificationType;
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->notificationData;
    }

    /**
     * Generate a UUID for the database notification.
     */
    public function toArray(object $notifiable): array
    {
        return $this->notificationData;
    }
}
