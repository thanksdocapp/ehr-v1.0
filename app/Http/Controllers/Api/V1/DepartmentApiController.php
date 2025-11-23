<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DepartmentApiController extends BaseApiController
{
    /**
     * Get list of all active departments.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'per_page' => 'nullable|integer|min:1|max:100',
            'search' => 'nullable|string|max:255',
            'with_doctors' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        try {
            $query = Department::where('is_active', true);

            // Include doctors if requested
            if ($request->boolean('with_doctors')) {
                $query->with(['doctors' => function ($doctorQuery) {
                    $doctorQuery->where('is_active', true)->orderBy('name');
                }]);
            }

            // Search functionality
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%");
                });
            }

            $query->withCount(['doctors' => function ($doctorQuery) {
                $doctorQuery->where('is_active', true);
            }]);

            if ($request->has('per_page')) {
                $perPage = $request->get('per_page', 15);
                $departments = $query->orderBy('name', 'asc')->paginate($perPage);
                return $this->sendPaginatedResponse($departments, 'Departments retrieved successfully');
            } else {
                $departments = $query->orderBy('name', 'asc')->get();
                return $this->sendResponse($departments, 'Departments retrieved successfully');
            }

        } catch (\Exception $e) {
            return $this->sendServerError('Failed to retrieve departments: ' . $e->getMessage());
        }
    }

    /**
     * Get specific department details.
     */
    public function show($id)
    {
        try {
            $department = Department::with([
                'doctors' => function ($query) {
                    $query->where('is_active', true)->orderBy('name');
                }
            ])
            ->withCount(['doctors' => function ($doctorQuery) {
                $doctorQuery->where('is_active', true);
            }])
            ->where('is_active', true)
            ->find($id);

            if (!$department) {
                return $this->sendNotFound('Department');
            }

            // Add additional statistics
            $department->total_appointments = $department->appointments()->count();
            $department->today_appointments = $department->appointments()
                ->whereDate('appointment_date', now()->format('Y-m-d'))
                ->count();

            return $this->sendResponse($department, 'Department details retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendServerError('Failed to retrieve department details: ' . $e->getMessage());
        }
    }

    /**
     * Get department statistics.
     */
    public function getStatistics($id)
    {
        try {
            $department = Department::where('is_active', true)->find($id);

            if (!$department) {
                return $this->sendNotFound('Department');
            }

            $stats = [
                'total_doctors' => $department->doctors()->where('is_active', true)->count(),
                'total_appointments' => $department->appointments()->count(),
                'pending_appointments' => $department->appointments()->where('status', 'pending')->count(),
                'confirmed_appointments' => $department->appointments()->where('status', 'confirmed')->count(),
                'completed_appointments' => $department->appointments()->where('status', 'completed')->count(),
                'today_appointments' => $department->appointments()
                    ->whereDate('appointment_date', now()->format('Y-m-d'))
                    ->count(),
                'this_week_appointments' => $department->appointments()
                    ->whereBetween('appointment_date', [
                        now()->startOfWeek()->format('Y-m-d'),
                        now()->endOfWeek()->format('Y-m-d')
                    ])
                    ->count(),
                'this_month_appointments' => $department->appointments()
                    ->whereBetween('appointment_date', [
                        now()->startOfMonth()->format('Y-m-d'),
                        now()->endOfMonth()->format('Y-m-d')
                    ])
                    ->count(),
            ];

            $data = [
                'department' => $department->only(['id', 'name', 'description']),
                'statistics' => $stats
            ];

            return $this->sendResponse($data, 'Department statistics retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendServerError('Failed to retrieve department statistics: ' . $e->getMessage());
        }
    }

    /**
     * Search departments.
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

            $departments = Department::where('is_active', true)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                      ->orWhere('description', 'LIKE', "%{$query}%");
                })
                ->withCount(['doctors' => function ($doctorQuery) {
                    $doctorQuery->where('is_active', true);
                }])
                ->orderBy('name', 'asc')
                ->paginate($perPage);

            return $this->sendPaginatedResponse($departments, 'Search results retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendServerError('Search failed: ' . $e->getMessage());
        }
    }

    /**
     * Get departments with their available doctors for appointment booking.
     */
    public function getForBooking()
    {
        try {
            $departments = Department::where('is_active', true)
                ->with(['doctors' => function ($query) {
                    $query->where('is_active', true)
                          ->with(['schedules' => function ($scheduleQuery) {
                              $scheduleQuery->where('is_active', true);
                          }])
                          ->orderBy('name');
                }])
                ->withCount(['doctors' => function ($doctorQuery) {
                    $doctorQuery->where('is_active', true);
                }])
                ->having('doctors_count', '>', 0)
                ->orderBy('name', 'asc')
                ->get();

            // Add availability information for each doctor
            $departments->each(function ($department) {
                $department->doctors->each(function ($doctor) {
                    $doctor->has_schedules = $doctor->schedules->count() > 0;
                    $doctor->is_available_today = $this->checkDoctorAvailabilityToday($doctor);
                });
            });

            return $this->sendResponse($departments, 'Departments for booking retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendServerError('Failed to retrieve departments for booking: ' . $e->getMessage());
        }
    }

    /**
     * Get popular departments based on appointment count.
     */
    public function getPopular(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'nullable|integer|min:1|max:20',
            'period' => 'nullable|in:week,month,year'
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        try {
            $limit = $request->get('limit', 5);
            $period = $request->get('period', 'month');

            // Define date range based on period
            switch ($period) {
                case 'week':
                    $startDate = now()->startOfWeek()->format('Y-m-d');
                    $endDate = now()->endOfWeek()->format('Y-m-d');
                    break;
                case 'year':
                    $startDate = now()->startOfYear()->format('Y-m-d');
                    $endDate = now()->endOfYear()->format('Y-m-d');
                    break;
                default: // month
                    $startDate = now()->startOfMonth()->format('Y-m-d');
                    $endDate = now()->endOfMonth()->format('Y-m-d');
                    break;
            }

            $departments = Department::where('is_active', true)
                ->withCount(['appointments' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('appointment_date', [$startDate, $endDate]);
                }])
                ->withCount(['doctors' => function ($doctorQuery) {
                    $doctorQuery->where('is_active', true);
                }])
                ->orderBy('appointments_count', 'desc')
                ->limit($limit)
                ->get();

            $data = [
                'period' => $period,
                'date_range' => [
                    'start' => $startDate,
                    'end' => $endDate
                ],
                'departments' => $departments
            ];

            return $this->sendResponse($data, 'Popular departments retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendServerError('Failed to retrieve popular departments: ' . $e->getMessage());
        }
    }

    /**
     * Check if any doctor in the department is available today.
     */
    private function checkDoctorAvailabilityToday($doctor)
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
}
