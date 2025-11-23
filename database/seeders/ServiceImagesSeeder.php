<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Service;
use Illuminate\Support\Facades\File;

class ServiceImagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $servicesWithImages = [
            'Emergency Care' => 'emergency-care.jpg',
            'Surgical Services' => 'surgical-services.jpg',
            'Diagnostic Imaging' => 'diagnostic-imaging.jpg',
            'Laboratory Services' => 'laboratory-services.jpg',
            'Maternity Care' => 'maternity-care.jpg',
            'Pharmacy Services' => 'pharmacy-services.jpg',
            'Cardiology' => 'cardiology.jpg',
            'Neurology' => 'neurology.jpg',
            'Orthopedics' => 'orthopedics.jpg',
            'Pediatrics' => 'pediatrics.jpg',
            'Radiology' => 'radiology.jpg',
            'Physical Therapy' => 'physical-therapy.jpg',
            'Dermatology' => 'dermatology.jpg',
            'Ophthalmology' => 'ophthalmology.jpg',
            'Dental Services' => 'dental-services.jpg',
            'Mental Health' => 'mental-health.jpg',
            'Oncology' => 'oncology.jpg',
            'Urology' => 'urology.jpg',
            'Gastroenterology' => 'gastroenterology.jpg',
            'Pulmonology' => 'pulmonology.jpg'
        ];

        foreach ($servicesWithImages as $serviceName => $imagePath) {
            $service = Service::where('name', 'like', '%' . $serviceName . '%')->first();
            if ($service) {
                // Create the image filename with service ID to avoid conflicts
                $imageFileName = 'services/' . strtolower(str_replace(' ', '-', $serviceName)) . '-' . $service->id . '.jpg';
                
                // Update the service with the image path
                $service->update(['image' => $imageFileName]);
                
                echo "Updated {$serviceName} with image: {$imageFileName}\n";
            }
        }
    }
}
