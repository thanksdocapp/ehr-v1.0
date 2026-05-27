<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class ClinicBookingRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'department_id',
        'service_id',
        'patient_id',
        'doctor_id',
        'appointment_id',
        'accepted_by_user_id',
        'accepted_at',
        'auto_accepted',
        'appointment_date',
        'appointment_time',
        'consultation_type',
        'fee',
        'notes',
        'patient_data',
        'status',
        'created_from',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'appointment_time' => 'datetime:H:i',
        'patient_data' => 'array',
        'fee' => 'decimal:2',
        'accepted_at' => 'datetime',
        'auto_accepted' => 'boolean',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(BookingService::class, 'service_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function acceptedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by_user_id');
    }

    public function scopePendingAcceptance($query)
    {
        return $query->where('status', 'pending_acceptance');
    }

    public function scopeForDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public static function generateRequestNumber(): string
    {
        do {
            $number = 'CB' . date('Ymd') . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        } while (static::where('request_number', $number)->exists());

        return $number;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending_acceptance';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isAutoAccepted(): bool
    {
        if (Schema::hasColumn($this->getTable(), 'auto_accepted') && $this->auto_accepted) {
            return true;
        }

        return $this->status === 'accepted'
            && $this->doctor_id
            && ! $this->accepted_by_user_id;
    }

    public function resolvedDoctor(): ?Doctor
    {
        if ($this->doctor) {
            return $this->doctor;
        }

        return $this->appointment?->doctor;
    }

    public function resolvedDepartment(): ?Department
    {
        if ($this->department) {
            return $this->department;
        }

        return $this->appointment?->department;
    }

    public function assignedDoctorName(): string
    {
        $doctor = $this->resolvedDoctor();
        if (! $doctor) {
            return '—';
        }

        $name = trim((string) ($doctor->user->name ?? ''));
        if ($name !== '') {
            return $name;
        }

        return trim($doctor->first_name.' '.$doctor->last_name) ?: '—';
    }

    public function clinicName(): string
    {
        return $this->resolvedDepartment()?->name ?? '—';
    }

    /**
     * @return array{
     *     primary_label: string,
     *     clinic_name: ?string,
     *     doctor_name: ?string,
     *     department_id: ?int,
     *     evidence_line: ?string,
     *     invoice_number: ?string
     * }
     */
    public function bookingCapture(): array
    {
        return app(\App\Services\PatientBookingSourceService::class)->clinicBookingRequestCapture($this);
    }

    /**
     * Human-readable acceptor for admin lists and exports.
     *
     * @return array{name: string, detail: ?string, is_auto: bool}
     */
    public function acceptorDisplay(): array
    {
        if ($this->isAutoAccepted()) {
            $doctorName = $this->assignedDoctorName();
            $clinicName = $this->clinicName();

            return [
                'name' => $doctorName !== '—' ? $doctorName.' (auto-assigned)' : 'System (auto-assigned)',
                'detail' => $clinicName !== '—' ? $clinicName : null,
                'is_auto' => true,
            ];
        }

        $acceptor = $this->acceptedByUser;

        if ($acceptor) {
            $name = trim((string) ($acceptor->name ?? ''));
            if ($name === '') {
                $name = (string) ($acceptor->email ?? 'User #'.$acceptor->id);
            }

            return ['name' => $name, 'detail' => null, 'is_auto' => false];
        }

        if ($this->status === 'accepted' && $this->resolvedDoctor()) {
            return [
                'name' => $this->assignedDoctorName(),
                'detail' => 'Acceptor not recorded',
                'is_auto' => false,
            ];
        }

        return ['name' => '—', 'detail' => 'Not recorded (legacy)', 'is_auto' => false];
    }
}
