<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private string $password
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
        $roleText = match ($notifiable->role) {
            1 => 'Super Administrador',
            2 => 'Administrador',
            3 => 'Jefe de Grupo',
            default => 'Usuario'
        };

        return (new MailMessage)
            ->subject('¡Bienvenido a ' . config('app.name') . '!')
            ->greeting('¡Hola ' . $notifiable->name . '!')
            ->line('¡Bienvenido al sistema de gestión de inspecciones!')
            ->line('Tu cuenta ha sido creada con el rol de ' . $roleText . '.')
            ->line('Tus credenciales de acceso son:')
            ->line('Email: ' . $notifiable->email)
            ->line('Contraseña: ' . $this->password)
            ->line('Por favor, cambia tu contraseña después de iniciar sesión por primera vez.')
            ->action('Iniciar Sesión', config('app.frontend_url') . '/login')
            ->line('Si tienes alguna pregunta, no dudes en contactar al equipo de soporte.');
    }
}
