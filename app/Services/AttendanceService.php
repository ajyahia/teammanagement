<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\Setting;
use App\Models\SpecificHoliday;
use App\Models\User;
use Carbon\Carbon;

/**
 * Attendance rule: a working day that has already come (today included) counts as
 * present unless an absent / vacation / excused record was saved for it.
 */
class AttendanceService
{
    /** @return array{weekly: int[], specific: string[]} */
    public function holidays(int $month, int $year): array
    {
        return [
            'weekly' => json_decode(Setting::getVal('weekly_holidays', '[]'), true) ?? [],
            'specific' => SpecificHoliday::whereYear('date', $year)->whereMonth('date', $month)
                ->pluck('date')->map(fn($d) => $d->format('Y-m-d'))->toArray(),
        ];
    }

    /** True when the employee is assumed present on a date that has no saved record. */
    public function isImpliedPresent(User $user, string $date, array $holidays): bool
    {
        $day = Carbon::parse($date);

        if ($day->isFuture() && !$day->isToday()) {
            return false;
        }
        if (in_array($day->dayOfWeek, $holidays['weekly']) || in_array($date, $holidays['specific'])) {
            return false;
        }
        if ($user->created_at && $day->toDateString() < $user->created_at->toDateString()) {
            return false;
        }
        if ($user->deactivated_at && $day->toDateString() >= Carbon::parse($user->deactivated_at)->toDateString()) {
            return false;
        }

        return true;
    }

    /** @return array{present: int, absent: int, vacation: int, excused: int} */
    public function summary(User $user, int $month, int $year): array
    {
        $records = AttendanceRecord::where('user_id', $user->id)
            ->whereMonth('date', $month)->whereYear('date', $year)->get()
            ->keyBy(fn($r) => $r->date->format('Y-m-d'));

        $summary = [
            'present' => $records->where('status', 'present')->count(),
            'absent' => $records->where('status', 'absent')->count(),
            'vacation' => $records->where('status', 'vacation')->count(),
            'excused' => $records->where('status', 'excused')->count(),
        ];

        $holidays = $this->holidays($month, $year);
        $first = Carbon::create($year, $month, 1);
        for ($d = $first->copy(); $d->month === $month; $d->addDay()) {
            $date = $d->toDateString();
            if (!$records->has($date) && $this->isImpliedPresent($user, $date, $holidays)) {
                $summary['present']++;
            }
        }

        return $summary;
    }

    /** Employees present today: everyone active, minus those recorded absent / on leave. */
    public function presentToday(): int
    {
        $today = today()->toDateString();
        $holidays = $this->holidays((int) date('m'), (int) date('Y'));

        $recorded = AttendanceRecord::where('date', $today)->get();
        $isHoliday = in_array((int) date('w'), $holidays['weekly']) || in_array($today, $holidays['specific']);
        if ($isHoliday) {
            return $recorded->where('status', 'present')->count();
        }

        $active = User::where('role', 'employee')->activeOn($today)->pluck('id');
        $notPresent = $recorded->whereIn('user_id', $active)->whereIn('status', ['absent', 'vacation', 'excused'])->count();

        return max(0, $active->count() - $notPresent);
    }
}
