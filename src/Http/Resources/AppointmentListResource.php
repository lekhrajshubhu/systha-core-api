<?php

namespace Systha\Core\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class AppointmentListResource extends JsonResource
{
    public function toArray($request): array
    {
        $status = strtolower((string) $this->status);
        $iconMap = [
            'booked' => ['icon' => 'mdi-calendar-check', 'iconTone' => 'appointment-icon--peach'],
            'pending' => ['icon' => 'mdi-timer-sand', 'iconTone' => 'appointment-icon--amber'],
            'completed' => ['icon' => 'mdi-check-decagram', 'iconTone' => 'appointment-icon--green'],
            'cancelled' => ['icon' => 'mdi-close-circle', 'iconTone' => 'appointment-icon--red'],
        ];
        $iconMeta = $iconMap[$status] ?? $iconMap['pending'];

        // $appointmentNo = (string) ($this->appointment_no ?: ('apt-' . $this->id));
        // $title = trim((string) $this->description) !== ''
        //     ? (string) $this->description
        //     : ('Appointment ' . $appointmentNo);

        return [
            'id' => $this->id,
            'title' => $this->appointment_no,
            'vendor' => (string) (optional($this->vendor)->name ?? 'Unknown Vendor'),
            'time' => $this->formatTimeLabel(),
            'status' => str($status)->replace('_', ' ')->title()->toString(),
            'isPaid' => (bool) $this->is_paid,
            'icon' => $iconMeta['icon'],
            'iconTone' => $iconMeta['iconTone'],
        ];
    }

    private function formatTimeLabel(): string
    {
        if (!$this->start_date) {
            return 'No date';
        }

        $date = Carbon::parse($this->start_date);
        $today = Carbon::today();
        $dateLabel = $date->isSameDay($today)
            ? 'Today'
            : ($date->isSameDay($today->copy()->addDay())
                ? 'Tomorrow'
                : $date->format('M j, Y'));

        if (!$this->start_time) {
            return $dateLabel;
        }

        try {
            $timeLabel = Carbon::parse($this->start_time)->format('g:i A');
        } catch (\Throwable $e) {
            $timeLabel = (string) $this->start_time;
        }

        return $dateLabel . ', ' . $timeLabel;
    }
}

