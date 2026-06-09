<?php

namespace App\Notifications;

use App\Models\Chamado;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ChamadoStatusAlterado extends Notification
{
    public function __construct(private readonly Chamado $chamado) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'chamado_id'   => $this->chamado->id,
            'status_novo'  => $this->chamado->status,
            'status_label' => $this->chamado->statusLabel(),
            'setor'        => $this->chamado->setor?->nome,
            'tipo'         => $this->chamado->tipoLabel(),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label = $this->chamado->statusLabel();
        $id    = $this->chamado->id;

        return (new MailMessage)
            ->subject("Chamado #{$id} — {$label}")
            ->line("O chamado #{$id} ({$this->chamado->tipoLabel()}) teve seu status alterado para \"{$label}\".")
            ->line("Setor: " . ($this->chamado->setor?->nome ?? 'Não informado'))
            ->action('Ver chamado', url("/admin/chamados/{$id}/edit"));
    }
}
