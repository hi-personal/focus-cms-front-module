<?php

namespace Modules\FocusCmsFrontModule\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function index()
    {
        $currentThemeName = app('options.repository')->get('currentThemeName');
        $viewFile = base_path("Themes/{$currentThemeName}/resources/views/maintenance.blade.php");

        if (File::exists($viewFile) == true) {
            return view($viewFile);
        }

        return view('front.maintenance');
    }
}