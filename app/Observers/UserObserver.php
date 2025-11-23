<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Doctor;
use App\Models\Department;
use Illuminate\Support\Str;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $this->createDoctorRecordIfNeeded($user);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // If role changed to doctor, create doctor record
        if ($user->wasChanged('role') && $user->role === 'doctor') {
            $this->createDoctorRecordIfNeeded($user);
        }
        
        // If role changed from doctor to something else, deactivate doctor record
        if ($user->wasChanged('role') && $user->getOriginal('role') === 'doctor' && $user->role !== 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->first();
            if ($doctor) {
                $doctor->update([
                    'is_active' => false,
                    'status' => 'inactive'
                ]);
            }
        }
        
        // Update doctor record if user is a doctor and certain fields changed
        if ($user->role === 'doctor' && $user->doctor) {
            $updateData = [];
            
            if ($user->wasChanged('name')) {
                $nameParts = explode(' ', trim($user->name));
                $updateData['first_name'] = $nameParts[0] ?? 'Doctor';
                $updateData['last_name'] = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : $nameParts[0];
                $updateData['slug'] = Str::slug($user->name . '-' . $user->id);
            }
            
            if ($user->wasChanged('email')) {
                $updateData['email'] = $user->email;
            }
            
            if ($user->wasChanged('phone')) {
                $updateData['phone'] = $user->phone;
            }
            
            if ($user->wasChanged('specialization')) {
                $updateData['specialization'] = $user->specialization ?? 'GP';
            }
            
            if ($user->wasChanged('department_id')) {
                $updateData['department_id'] = $user->department_id;
            }
            
            if ($user->wasChanged('is_active')) {
                $updateData['is_active'] = $user->is_active;
                $updateData['status'] = $user->is_active ? 'active' : 'inactive';
            }
            
            if (!empty($updateData)) {
                $user->doctor->update($updateData);
            }
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        // Soft delete associated doctor record
        if ($user->doctor) {
            $user->doctor->update([
                'is_active' => false,
                'status' => 'inactive'
            ]);
        }
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        // Restore associated doctor record
        if ($user->role === 'doctor' && $user->doctor) {
            $user->doctor->update([
                'is_active' => $user->is_active,
                'status' => $user->is_active ? 'active' : 'inactive'
            ]);
        }
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        // Hard delete associated doctor record
        if ($user->doctor) {
            $user->doctor->delete();
        }
    }
    
    /**
     * Create a doctor record if the user has role 'doctor'
     */
    private function createDoctorRecordIfNeeded(User $user): void
    {
        if ($user->role === 'doctor' && !$user->doctor) {
            // Parse name parts
            $nameParts = explode(' ', trim($user->name));
            $firstName = $nameParts[0] ?? 'Doctor';
            $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : $firstName;
            
            // Get default department or use user's department
            $departmentId = $user->department_id;
            if (!$departmentId) {
                $defaultDepartment = Department::first();
                $departmentId = $defaultDepartment ? $defaultDepartment->id : null;
            }
            
            if ($departmentId) {
                Doctor::create([
                    'user_id' => $user->id,
                    'title' => 'Dr.',
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'slug' => Str::slug($user->name . '-' . $user->id),
                    'specialization' => $user->specialization ?? 'GP',
                    'department_id' => $departmentId,
                    'bio' => $user->bio ?? 'Experienced medical professional dedicated to providing quality healthcare.',
                    'qualification' => 'MBBS',
                    'experience_years' => 5,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'consultation_fee' => 100.00,
                    'is_active' => $user->is_active,
                    'status' => $user->is_active ? 'active' : 'inactive'
                ]);
            }
        }
    }
}
