<?php

namespace App\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class DoctorLayoutComposer
{
    public function compose(View $view)
    {
        $user = Auth::user();
        
        // Set layout based on user role
        if ($user && $user->role === 'doctor') {
            $view->getFactory()->getEngineResolver()->resolve('blade')->getCompiler()->setLayout('layouts.doctor');
        }
    }
}

