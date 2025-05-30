<?php

namespace App\Notifications;

use App\Models\AppNotification;
use App\Models\Inspection;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InspectionUnderReviewNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Inspection $inspection
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Create app notification
        AppNotification::create([
            'type' => AppNotification::INSPECTION_UNDER_REVIEW,
            'payload' => [
                'inspectionId' => $this->inspection->id,
                'productName' => $this->inspection->product->name,
                'clientName' => $this->inspection->product->client->name
            ],
            'user_id' => $notifiable->id,
            'read' => false
        ]);

        return (new MailMessage)
            ->subject('Inspección En Revisión')
            ->greeting('Hola ' . $notifiable->name)
            ->line('Una inspección ha pasado a estado de revisión.')
            ->line('Detalles de la inspección:')
            ->line('- ID: ' . $this->inspection->id)
            ->line('- Producto: ' . $this->inspection->product->name)
            ->line('- Cliente: ' . $this->inspection->product->client->name)
            ->line('- Planta: ' . $this->inspection->plant->name)
            ->line('- Jefe de Grupo: ' . $this->inspection->groupLeader->name)
            ->action('Ver Inspección', config('app.frontend_url') . '/inspections/' . $this->inspection->id)
            ->line('Por favor, revisa los detalles y procede con la revisión correspondiente.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'inspection_id' => $this->inspection->id,
            'product_name' => $this->inspection->product->name,
            'client_name' => $this->inspection->product->client->name,
            'plant_name' => $this->inspection->plant->name,
            'group_leader_name' => $this->inspection->groupLeader->name,
            'submit_date' => $this->inspection->submit_date->format('Y-m-d'),
        ];
    }
}
