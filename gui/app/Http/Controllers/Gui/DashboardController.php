<?php
declare(strict_types=1);

namespace Scarlett\DMDD\GUI\Http\Controllers\Gui;

use Illuminate\Contracts\View\View;

class DashboardController
{
    public function get(): View
    {
        return view('dashboard');
    }
}
