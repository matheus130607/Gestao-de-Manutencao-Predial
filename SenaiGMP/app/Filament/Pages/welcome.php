<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Welcome extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Site institucional';

    protected static ?int $navigationSort = 90;

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.welcome';

    protected static ?string $title = 'SENAI - Segurança e Facilities';
}
