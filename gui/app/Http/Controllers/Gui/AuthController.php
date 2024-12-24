<?php
declare(strict_types=1);

namespace Scarlett\DMDD\GUI\Http\Controllers\Gui;

use Scarlett\DMDD\GUI\Http\Controllers\GuiController;

class AuthController extends GuiController
{
    public function get()
    {
        return 'login';
    }
}