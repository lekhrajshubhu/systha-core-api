<?php

namespace Systha\Core\Http\Controllers\Api\V1\Platform\Dashboard;

/**
 * THIS INTELLECTUAL PROPERTY IS COPYRIGHT Ⓒ 2020
 * SYSTHA TECH LLC. ALL RIGHT RESERVED
 * -----------------------------------------------------------
 * SALES@SYSTHATECH.COM 
 * 512 903 2202
 * WWW.SYSTHATECH.COM
 * -----------------------------------------------------------
 */





use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Systha\Core\Models\Quote;
use Systha\Core\Models\QuoteEnq;
use Systha\Core\Models\Appointment;
use Systha\Core\Models\PackageSubscription;

/**
 * @group Platform
 * @subgroup Profile
 */
class DashboardController extends Controller
{
    public function summary(Request $request)
    {
        $client = auth('platform')->user();
        try {
            $today = Carbon::today();

            $stats = [
                [
                    'label' => 'Inquiries',
                    'value' => QuoteEnq::where('client_id', $client->id)->where('status', '!=', 'converted')->count(),
                    'icon' => 'mdi-email-fast-outline',
                    'tone' => 'stat-icon--coral',
                    'routeName' => 'dashboard.inquiries',
                ],
                [
                    'label' => 'Subscriptions',
                    'value' => PackageSubscription::where('client_id', $client->id)->count(),
                    'icon' => 'mdi-cube-outline',
                    'tone' => 'stat-icon--sky',
                    'routeName' => 'dashboard.subscriptions',
                ],
                [
                    'label' => 'Appointments',
                    'value' => Appointment::where('client_id', $client->id)->count(),
                    'icon' => 'mdi-calendar-check-outline',
                    'tone' => 'stat-icon--mint',
                    'routeName' => 'dashboard.appointments',
                ],
                [
                    'label' => 'Quotations',
                    'value' => Quote::where('client_id', $client->id)->count(),
                    'icon' => 'mdi-file-document-outline',
                    'tone' => 'stat-icon--lavender',
                    'routeName' => 'dashboard.quotations',
                ],
            ];

            $todayAppointments = Appointment::where('client_id', $client->id)
                ->whereDate('start_date', $today)
                ->with(['vendor', 'services'])
                ->orderBy('start_time')
                ->limit(10)
                ->get()
                ->map(function ($appointment) {
                    $status = $this->mapAppointmentStatus((string) $appointment->status);
                    $iconData = $this->appointmentIcon($status);
                    $firstService = $appointment->services->first();

                    return [
                        'id' => $appointment->appointment_no ?: $appointment->id,
                        'title' => $firstService->service_name ?? $appointment->description ?? ('Appointment #' . $appointment->id),
                        'vendor' => optional($appointment->vendor)->name ?? 'Unknown Vendor',
                        'time' => $this->formatDateTimeLabel($appointment->start_date, $appointment->start_time),
                        'status' => $status,
                        'icon' => $iconData['icon'],
                        'iconTone' => $iconData['iconTone'],
                    ];
                })
                ->values();

            $latestQuotations = Quote::where('client_id', $client->id)
                ->with(['quoteServices.service'])
                ->latest('created_at')
                ->limit(5)
                ->get()
                ->map(function ($quote) {
                    $firstService = $quote->quoteServices->first();
                    $title = optional($firstService?->service)->service_name
                        ?? $firstService?->item_name
                        ?? ('Quotation ' . $quote->quote_number);

                    return [
                        '_ts' => optional($quote->created_at)->timestamp ?? 0,
                        'id' => 'quote-' . $quote->id,
                        'title' => $title,
                        'subtitle' => 'Quote ' . ucfirst((string) $quote->status),
                        'meta' => $this->formatMeta(optional($quote->created_at)->toDateTimeString()),
                        'icon' => 'mdi-wrench',
                        'tone' => 'update-icon--coral',
                    ];
                });

            $latestAppointments = Appointment::where('client_id', $client->id)
                ->with(['services'])
                ->latest('created_at')
                ->limit(5)
                ->get()
                ->map(function ($appointment) {
                    $firstService = $appointment->services->first();
                    $title = $firstService->service_name ?? $appointment->description ?? ('Appointment #' . $appointment->id);

                    return [
                        '_ts' => optional($appointment->created_at)->timestamp ?? 0,
                        'id' => 'appt-' . $appointment->id,
                        'title' => $title,
                        'subtitle' => ucfirst((string) $appointment->status),
                        'meta' => $this->formatDateTimeLabel($appointment->start_date, $appointment->start_time),
                        'icon' => 'mdi-broom',
                        'tone' => 'update-icon--mint',
                    ];
                });

            $latestSubscriptions = PackageSubscription::where('client_id', $client->id)
                ->with(['package'])
                ->latest('created_at')
                ->limit(5)
                ->get()
                ->map(function ($subscription) {
                    $title = optional($subscription->package)->name ?? ('Subscription ' . ($subscription->subs_no ?: $subscription->id));

                    return [
                        '_ts' => optional($subscription->created_at)->timestamp ?? 0,
                        'id' => 'sub-' . $subscription->id,
                        'title' => $title,
                        'subtitle' => 'On the way',
                        'meta' => $this->formatMeta(optional($subscription->created_at)->toDateTimeString()),
                        'icon' => 'mdi-shield-home-outline',
                        'tone' => 'update-icon--amber',
                    ];
                });

            $latestUpdates = collect()
                ->merge($latestQuotations)
                ->merge($latestAppointments)
                ->merge($latestSubscriptions)
                ->sortByDesc('_ts')
                ->take(10)
                ->map(function ($item) {
                    unset($item['_ts']);
                    return $item;
                })
                ->values();

            return response()->json([
                'stats' => $stats,
                'todayAppointments' => $todayAppointments,
                'latestUpdates' => $latestUpdates,
            ], 200);
        } catch (\Throwable $th) {
            return response(['error' => $th->getMessage()], 422);
        }
    }

    private function mapAppointmentStatus(string $status): string
    {
        $normalized = strtolower(trim($status));
        $map = [
            'booked' => 'Confirmed',
            'confirmed' => 'Confirmed',
            'pending' => 'Upcoming',
            'upcoming' => 'Upcoming',
            'completed' => 'Completed',
        ];

        return $map[$normalized] ?? ucfirst($normalized ?: 'Upcoming');
    }

    private function appointmentIcon(string $status): array
    {
        $map = [
            'Confirmed' => ['icon' => 'mdi-broom', 'iconTone' => 'appointment-icon--peach'],
            'Upcoming' => ['icon' => 'mdi-flash-outline', 'iconTone' => 'appointment-icon--sun'],
            'Completed' => ['icon' => 'mdi-check-decagram', 'iconTone' => 'appointment-icon--mint'],
        ];

        return $map[$status] ?? $map['Upcoming'];
    }

    private function formatDateTimeLabel($date, $time): string
    {
        if (empty($date)) {
            return 'No date';
        }

        $datePart = Carbon::parse($date);
        $today = Carbon::today();
        $label = $datePart->isSameDay($today) ? 'Today' : $datePart->format('M d, Y');

        if (empty($time)) {
            return $label;
        }

        try {
            $timeLabel = Carbon::parse($time)->format('g:i A');
        } catch (\Throwable $th) {
            $timeLabel = (string) $time;
        }

        return $label . ', ' . $timeLabel;
    }

    private function formatMeta(?string $dateTime): string
    {
        if (empty($dateTime)) {
            return 'No time';
        }

        $dt = Carbon::parse($dateTime);
        return $dt->isToday() ? $dt->format('g:i A') : $dt->format('M d, Y');
    }
}
