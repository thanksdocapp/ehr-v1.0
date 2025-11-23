<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Doctor;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DoctorApiController extends BaseApiController
{
    /**
     * Get list of all active doctors with pagination.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'per_page' => 'nullable|integer|min:1|max:100',
            'department_id' => 'nullable|exists:departments,id',
            'search' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        try {
            $query = Doctor::with(['department', 'schedules'])
                ->where('is_active', true);

            // Filter by department
            if ($request->has('department_id')) {
                $query->where('department_id', $request->department_id);
            }

            // Search by name or specialization
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('specialization', 'LIKE', "%{$search}%")
                      ->orWhere('license_number', 'LIKE', "%{$search}%");
                });
            }

            // Filter by specialization
            if ($request->has('specialization')) {
                $query->where('specialization', 'LIKE', "%{$request->specialization}%");
            }

            $perPage = $request->get('per_page', 15);
            $doctors = $query->orderBy('name', 'asc')->paginate($perPage);

            return $this->sendPaginatedResponse($doctors, 'Doctors retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendServerError('Failed to retrieve doctors: ' . $e->getMessage());
        }
    }

    /**
     * Get specific doctor details.
     */
    public function show($id)
    {
        try {
            $doctor = Doctor::with([
                'department', 
                'schedules', 
                'appointments' => function ($query) {
                    $query->where('appointment_date', '>=', now()->format('Y-m-d'))
                          ->orderBy('appointment_date', 'asc')
                          ->limit(10);
                }
            ])->where('is_active', true)->find($id);

            if (!$doctor) {
                return $this->sendNotFound('Doctor');
            }

            // Add availability status
            $doctor->is_available_today = $this->checkDoctorAvailability($doctor);
            
            return $this->sendResponse($doctor, 'Doctor details retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendServerError('Failed to retrieve doctor details: ' . $e->getMessage());
        }
    }

    /**
     * Get doctors by department.
     */
    public function getByDepartment($departmentId)
    {
        try {
            $department = Department::find($departmentId);
            if (!$department || !$department->is_active) {
                return $this->sendNotFound('Department');
            }

            $doctors = Doctor::with('schedules')
                ->where('department_id', $departmentId)
                ->where('is_active', true)
                ->orderBy('name', 'asc')
                ->get();

            // Add availability status for each doctor
            $doctors->each(function ($doctor) {
                $doctor->is_available_today = $this->checkDoctorAvailability($doctor);
            });

            $data = [
                'department' => $department,
                'doctors' => $doctors
            ];

            return $this->sendResponse($data, 'Department doctors retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendServerError('Failed to retrieve department doctors: ' . $e->getMessage());
        }
    }

    /**
     * Get doctor's schedule.
     */
    public function getSchedule($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'nullable|date|after_or_equal:today',
            'week' => 'nullable|date'
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        try {
            $doctor = Doctor::with('schedules')->where('is_active', true)->find($id);

            if (!$doctor) {
                return $this->sendNotFound('Doctor');
            }

            $schedules = $doctor->schedules()
                ->where('is_active', true)
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get();

            // If specific date requested, filter and add appointment slots
            if ($request->has('date')) {
                $date = $request->date;
                $dayOfWeek = date('w', strtotime($date)); // 0=Sunday, 1=Monday, etc.
                
                $schedules = $schedules->where('day_of_week', $dayOfWeek);
                
                // Add available time slots
                $schedules->each(function ($schedule) use ($date) {
                    $schedule->available_slots = $this->getAvailableTimeSlots($schedule, $date);
                });
            }

            $data = [
                'doctor' => $doctor->only(['id', 'name', 'specialization']),
                'schedules' => $schedules
            ];

            return $this->sendResponse($data, 'Doctor schedule retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendServerError('Failed to retrieve doctor schedule: ' . $e->getMessage());
        }
    }

    /**
     * Search doctors.
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2|max:255',
            'per_page' => 'nullable|integer|min:1|max:50'
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        try {
            $query = $request->query;
            $perPage = $request->get('per_page', 10);

            $doctors = Doctor::with(['department'])
                ->where('is_active', true)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                      ->orWhere('specialization', 'LIKE', "%{$query}%")
                      ->orWhere('license_number', 'LIKE', "%{$query}%")
                      ->orWhereHas('department', function ($dept) use ($query) {
                          $dept->where('name', 'LIKE', "%{$query}%");
                      });
                })
                ->orderBy('name', 'asc')
                ->paginate($perPage);

            return $this->sendPaginatedResponse($doctors, 'Search results retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendServerError('Search failed: ' . $e->getMessage());
        }
    }

    /**
     * Get available specializations.
     */
    public function getSpecializations()
    {
        try {
            $specializations = Doctor::where('is_active', true)
                ->whereNotNull('specialization')
                ->where('specialization', '!=', '')
                ->select('specialization')
                ->distinct()
                ->orderBy('specialization')
                ->pluck('specialization');

            return $this->sendResponse($specializations, 'Specializations retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendServerError('Failed to retrieve specializations: ' . $e->getMessage());
        }
    }

    /**
     * Check if doctor is available today.
     */
    private function checkDoctorAvailability($doctor)
    {
        $today = date('w'); // 0=Sunday, 1=Monday, etc.
        $currentTime = now()->format('H:i:s');

        $todaySchedule = $doctor->schedules()
            ->where('day_of_week', $today)
            ->where('is_active', true)
            ->where('start_time', '<=', $currentTime)
            ->where('end_time', '>', $currentTime)
            ->first();

        return $todaySchedule !== null;
    }

    /**
     * Get available time slots for a specific schedule and date.
     */
    private function getAvailableTimeSlots($schedule, $date)
    {
        $slots = [];
        $startTime = strtotime($schedule->start_time);
        $endTime = strtotime($schedule->end_time);
        $slotDuration = 30 * 60; // 30 minutes in seconds

        // Get existing appointments for this doctor on this date
        $existingAppointments = \App\Models\Appointment::where('doctor_id', $schedule->doctor_id)
            ->where('appointment_date', $date)
            ->where('status', '!=', 'cancelled')
            ->pluck('appointment_time')
            ->toArray();

        for ($time = $startTime; $time < $endTime; $time += $slotDuration) {
            $timeSlot = date('H:i:s', $time);
            
            if (!in_array($timeSlot, $existingAppointments)) {
                $slots[] = [
                    'time' => $timeSlot,
                    'formatted_time' => date('h:i A', $time),
                    'is_available' => true
                ];
            }
        }

        return $slots;
    }
}
