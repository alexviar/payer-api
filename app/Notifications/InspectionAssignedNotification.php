<?php

namespace App\Notifications;

use App\Models\AppNotification;
use App\Models\Inspection;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InspectionAssignedNotification extends Notification implements ShouldQueue
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
            'type' => AppNotification::INSPECTION_ASSIGNED,
            'payload' => [
                'inspectionId' => $this->inspection->id,
                'productName' => $this->inspection->product->name,
                'clientName' => $this->inspection->client->name
            ],
            'user_id' => $notifiable->id,
            'read' => false
        ]);

        return (new MailMessage)
            ->subject('Nueva Inspección Asignada')
            ->greeting('Hola ' . $notifiable->name)
            ->line('Se te ha asignado una nueva inspección.')
            ->line('Detalles de la inspección:')
            ->line('- ID: ' . $this->inspection->id)
            ->line('- Producto: ' . $this->inspection->product->name)
            ->line('- Cliente: ' . $this->inspection->client->name)
            ->line('- Planta: ' . $this->inspection->plant->name)
            ->action('Ver Inspección', config('app.frontend_url') . '/inspections/' . $this->inspection->id)
            ->line('Por favor, revisa los detalles y comienza con la inspección lo antes posible.');
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
            'submit_date' => $this->inspection->submit_date->format('Y-m-d'),
        ];
    }
}
