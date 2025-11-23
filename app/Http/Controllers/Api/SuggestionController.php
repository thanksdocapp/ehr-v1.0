<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SuggestionController extends Controller
{
    /**
     * Get diagnosis suggestions
     */
    public function getDiagnosisSuggestions(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        // Get suggestions from existing medical records and prescriptions
        $suggestions = collect();
        
        // From medical records
        $medicalRecordDiagnoses = DB::table('medical_records')
            ->select('diagnosis')
            ->whereNotNull('diagnosis')
            ->where('diagnosis', 'LIKE', '%' . $query . '%')
            ->distinct()
            ->limit(20)
            ->pluck('diagnosis');
        
        $suggestions = $suggestions->merge($medicalRecordDiagnoses);
        
        // From prescriptions
        $prescriptionDiagnoses = DB::table('prescriptions')
            ->select('diagnosis')
            ->whereNotNull('diagnosis')
            ->where('diagnosis', 'LIKE', '%' . $query . '%')
            ->distinct()
            ->limit(20)
            ->pluck('diagnosis');
        
        $suggestions = $suggestions->merge($prescriptionDiagnoses);
        
        // Common diagnoses list (ICD-10 inspired common conditions)
        $commonDiagnoses = $this->getCommonDiagnoses();
        $matchingCommon = collect($commonDiagnoses)
            ->filter(function($diagnosis) use ($query) {
                return stripos($diagnosis, $query) !== false;
            })
            ->take(10);
        
        $suggestions = $suggestions->merge($matchingCommon);
        
        // Remove duplicates, sort, and limit
        $suggestions = $suggestions->unique()->filter()->sort()->take(15)->values();
        
        return response()->json($suggestions->toArray());
    }

    /**
     * Get medication suggestions
     */
    public function getMedicationSuggestions(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $suggestions = collect();
        
        // From prescriptions - extract medication names from JSON
        $prescriptions = DB::table('prescriptions')
            ->select('medications')
            ->whereNotNull('medications')
            ->get();
        
        foreach ($prescriptions as $prescription) {
            $medications = json_decode($prescription->medications, true);
            if (is_array($medications)) {
                foreach ($medications as $medication) {
                    if (isset($medication['name']) && stripos($medication['name'], $query) !== false) {
                        $suggestions->push($medication['name']);
                    }
                }
            }
        }
        
        // Common medications list
        $commonMedications = $this->getCommonMedications();
        $matchingCommon = collect($commonMedications)
            ->filter(function($medication) use ($query) {
                return stripos($medication, $query) !== false;
            })
            ->take(15);
        
        $suggestions = $suggestions->merge($matchingCommon);
        
        // Remove duplicates, sort, and limit
        $suggestions = $suggestions->unique()->filter()->sort()->take(20)->values();
        
        return response()->json($suggestions->toArray());
    }

    /**
     * Get common diagnoses list
     */
    private function getCommonDiagnoses(): array
    {
        return [
            'Acute Upper Respiratory Infection',
            'Acute Bronchitis',
            'Pneumonia',
            'Hypertension',
            'Type 2 Diabetes Mellitus',
            'Type 1 Diabetes Mellitus',
            'Asthma',
            'Chronic Obstructive Pulmonary Disease (COPD)',
            'Acute Gastroenteritis',
            'Urinary Tract Infection',
            'Sinusitis',
            'Otitis Media',
            'Pharyngitis',
            'Tonsillitis',
            'Migraine',
            'Tension Headache',
            'Anxiety Disorder',
            'Depression',
            'Gastritis',
            'Gastroesophageal Reflux Disease (GERD)',
            'Peptic Ulcer Disease',
            'Rheumatoid Arthritis',
            'Osteoarthritis',
            'Low Back Pain',
            'Allergic Rhinitis',
            'Atopic Dermatitis',
            'Contact Dermatitis',
            'Acne Vulgaris',
            'Cellulitis',
            'Herpes Zoster',
            'Conjunctivitis',
            'Anemia',
            'Iron Deficiency Anemia',
            'Vitamin D Deficiency',
            'Hyperlipidemia',
            'Hypothyroidism',
            'Hyperthyroidism',
            'Obesity',
            'Acute Myocardial Infarction',
            'Atrial Fibrillation',
            'Heart Failure',
            'Stroke',
            'Seizure Disorder',
            'Chronic Kidney Disease',
            'Acute Kidney Injury',
            'Hepatitis',
            'Gout',
            'Osteoporosis',
            'Fibromyalgia',
            'Sleep Apnea',
            'Insomnia',
        ];
    }

    /**
     * Get common medications list
     */
    private function getCommonMedications(): array
    {
        return [
            'Acetaminophen',
            'Ibuprofen',
            'Aspirin',
            'Amoxicillin',
            'Azithromycin',
            'Cephalexin',
            'Ciprofloxacin',
            'Doxycycline',
            'Metronidazole',
            'Trimethoprim-Sulfamethoxazole',
            'Amoxicillin-Clavulanate',
            'Atorvastatin',
            'Simvastatin',
            'Metformin',
            'Glipizide',
            'Insulin',
            'Losartan',
            'Lisinopril',
            'Amlodipine',
            'Hydrochlorothiazide',
            'Furosemide',
            'Atenolol',
            'Metoprolol',
            'Propranolol',
            'Omeprazole',
            'Pantoprazole',
            'Ranitidine',
            'Albuterol',
            'Prednisone',
            'Levothyroxine',
            'Sertraline',
            'Citalopram',
            'Fluoxetine',
            'Amitriptyline',
            'Gabapentin',
            'Tramadol',
            'Codeine',
            'Morphine',
            'Warfarin',
            'Clopidogrel',
            'Digoxin',
            'Furosemide',
            'Spironolactone',
            'Montelukast',
            'Loratadine',
            'Cetirizine',
            'Diphenhydramine',
            'Hydroxyzine',
            'Diclofenac',
            'Naproxen',
            'Celecoxib',
            'Allopurinol',
            'Colchicine',
            'Metoclopramide',
            'Ondansetron',
            'Loperamide',
            'Bisacodyl',
            'Senna',
            'Lactulose',
            'Sildenafil',
            'Tadalafil',
            'Finasteride',
            'Tamsulosin',
            'Sildenafil',
        ];
    }
}


