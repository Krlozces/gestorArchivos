<?php

namespace App\Filament\Resources\AreaResource\Pages;

use App\Filament\Resources\AreaResource;
use App\Models\Area;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListAreas extends ListRecords
{
    protected static string $resource = AreaResource::class;
    protected function getTableQuery(): Builder
    {
        $query = Area::query();
        $user = auth()->user();
        
        // Lista de áreas y sus permisos correspondientes
        $areas = [
            'scale_area' => 'Balanzas',
            'assistant_area' => 'Asistente de gerencia',
            'management_area' => 'Gerencia',
            'treasury_area' => 'Tesoreria',
            'accounting_area' => 'Contabilidad',
            'resources_area' => 'Recursos humanos',
            'maintenance_area' => 'Mantenimiento',
            'sanitation_area' => 'Calidad y Saneamiento',
            'hotel_area' => 'Hotel',
        ];
        
        // Array para almacenar las áreas a las que el usuario tiene acceso
        $accessibleAreas = [];
    
        // Iteramos sobre cada área y si el usuario tiene el permiso, añadimos el área a la lista
        foreach ($areas as $permission => $areaName) {
            if ($user->can($permission)) {
                $accessibleAreas[] = $areaName;
            }
        }
    
        // Si el usuario tiene acceso a alguna área, filtramos el query
        if (!empty($accessibleAreas)) {
            $query->whereIn('nombre', $accessibleAreas);
        } else {
            // Si no tiene permisos, devolvemos un query vacío
            $query->whereRaw('1 = 0');
        }
    
        return $query;
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
