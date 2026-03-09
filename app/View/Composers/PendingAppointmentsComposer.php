<?php

namespace App\View\Composers;

use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class PendingAppointmentsComposer
{
    /**
     * Bind pending appointment counts to staff/doctor layouts.
     */
    public function compose(View $view): void
    {
        $user = Auth::user();
        if (!$user) {
            $view->with('layoutPendingPastCount', 0);
            $view->with('layoutPendingUpcomingCount', 0);
            return;
        }

        $userDepartmentIds = $this->getUserDepartmentIds();

        $appointmentsQuery = Appointment::query();

        if (!empty($userDepartmentIds)) {
            $appointmentsQuery->whereHas('doctor', function ($q) use ($userDepartmentIds) {
                $q->byDepartments($userDepartmentIds);
            });
        } elseif ($user->department_id) {
            $appointmentsQuery->whereHas('doctor', function ($q) use ($user) {
                $q->byDepartment($user->department_id);
            });
        } else {
            // No department - show nothing (or all if admin)
            $view->with('layoutPendingPastCount', 0);
            $view->with('layoutPendingUpcomingCount', 0);
            return;
        }

        $view->with('layoutPendingPastCount', (clone $appointmentsQuery)->pendingPast()->count());
        $view->with('layoutPendingUpcomingCount', (clone $appointmentsQuery)->pendingUpcoming()->count());
    }

    private function getUserDepartmentIds(): array
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }

        if ($user->role === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->with('departments')->first();
            if ($doctor) {
                if ($doctor->departments->isNotEmpty()) {
                    return $doctor->departments->pluck('id')->toArray();
                }
                if ($doctor->department_id) {
                    return [$doctor->department_id];
                }
            }
            return [];
        }

        $user->load('departments');
        if ($user->departments->isNotEmpty()) {
            return $user->departments->pluck('id')->toArray();
        }
        if ($user->department_id) {
            return [$user->department_id];
        }
        return [];
    }
}
