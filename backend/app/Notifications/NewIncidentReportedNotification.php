<?php

namespace App\Notifications;

use App\Models\Incident;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewIncidentReportedNotification extends Notification
{
    use Queueable;

    public function __construct(public Incident $incident) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'incident_id' => $this->incident->id,
            'identifier' => 'INC-'.str_pad((string) $this->incident->id, 4, '0', STR_PAD_LEFT),
            'category' => $this->incident->category,
            'barangay' => $this->incident->barangay,
            'severity' => $this->incident->severity,
            'reported_at' => ($this->incident->reported_at ?? $this->incident->created_at)->toISOString(),
            'reporter_name' => $this->incident->reporter?->name ?? 'Barangay Responder',
            'detail_url' => route('dispatcher.incidents.show', $this->incident),
        ];
    }
}
