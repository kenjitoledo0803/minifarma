@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 text-center">
         <h2 class="text-2xl font-bold mb-4">Módulo de Punto de Venta</h2>
         <p class="mb-4">El sistema está integrado. Para la versión interactiva completa, asegura ejecutar el build de Vue.</p>
         
         <!-- Placeholder for where the Vue App mounts in the real implementation -->
         <div id="pos-app" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 h-96 flex items-center justify-center border-2 border-dashed border-gray-300">
             Cargando Interfaz POS... (Requiere JS Build)
         </div>
    </div>
</div>
@endsection
