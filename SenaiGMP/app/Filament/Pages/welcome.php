<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Welcome extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Início';
    protected static ?int $navigationSort = 0;
    
    // Remove da navegação se quiser como página inicial
    protected static bool $shouldRegisterNavigation = true;
    
    protected static string $view = 'filament.pages.welcome';
    
    // Usa layout simples sem sidebar e topbar
    public function getLayout(): string
    {
        return 'filament-panels::components.layout.simple';
    }
    
    // Título da página
    protected static ?string $title = 'SENAI - Segurança e Facilities';
    
    // Permite acesso sem autenticação
    protected static bool $isDiscovered = true;
}