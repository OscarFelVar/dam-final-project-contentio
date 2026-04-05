<?php

namespace App\Notifications;

use App\Models\ContentItem;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContentStatusChanged extends Notification
{
    public function __construct(
        public readonly ContentItem $contentItem,
        public readonly string $fromStatus,
        public readonly string $toStatus,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $item    = $this->contentItem;
        $title   = $item->title;
        $url     = config('app.frontend_url') . "/content-items/{$item->id}/review";

        return match ($this->toStatus) {

            'ready_for_review' => (new MailMessage)
                ->subject("[Contentio] Nuevo contenido pendiente de revisión")
                ->greeting('Hola,')
                ->line("La agencia ha enviado el siguiente contenido para tu revisión:")
                ->line("**{$title}**")
                ->action('Revisar contenido', $url)
                ->line('Por favor, revísalo y comunica tu decisión desde la plataforma.')
                ->salutation('— Contentio'),

            'approved' => (new MailMessage)
                ->subject("[Contentio] Contenido aprobado: {$title}")
                ->greeting('¡Buenas noticias!')
                ->line("La clínica ha **aprobado** el siguiente contenido:")
                ->line("**{$title}**")
                ->action('Ver contenido', $url)
                ->salutation('— Contentio'),

            'in_correction' => (new MailMessage)
                ->subject("[Contentio] Correcciones solicitadas: {$title}")
                ->greeting('Hola,')
                ->line("La clínica ha solicitado correcciones en el siguiente contenido:")
                ->line("**{$title}**")
                ->action('Ver indicaciones', $url)
                ->line('Revisa los comentarios en la plataforma y envía de nuevo cuando esté listo.')
                ->salutation('— Contentio'),

            'rejected' => (new MailMessage)
                ->subject("[Contentio] Contenido rechazado: {$title}")
                ->greeting('Hola,')
                ->line("La clínica ha **rechazado** el siguiente contenido:")
                ->line("**{$title}**")
                ->action('Ver detalles', $url)
                ->line('Puedes consultar el motivo en los comentarios de la plataforma.')
                ->salutation('— Contentio'),

            default => (new MailMessage)
                ->subject("[Contentio] Actualización de estado: {$title}")
                ->line("El estado del contenido **{$title}** ha cambiado a: {$this->toStatus}.")
                ->action('Ver contenido', $url)
                ->salutation('— Contentio'),
        };
    }
}
