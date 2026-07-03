<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Distrito;
use App\Models\EmergenciaContacto;
use App\Models\Notificacion;
use App\Models\Reporte;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $distritos = collect([
            'Cercado',
            'Cayma',
            'Cerro Colorado',
            'José Luis Bustamante y Rivero',
            'Mariano Melgar',
            'Miraflores',
            'Paucarpata',
            'Sachaca',
            'Socabaya',
            'Yanahuara',
        ])->map(fn (string $nombre) => Distrito::firstOrCreate(['nombre' => $nombre]));

        $categorias = collect([
            ['nombre' => 'Seguridad ciudadana', 'icono' => 'shield-alert'],
            ['nombre' => 'Limpieza pública', 'icono' => 'trash-2'],
            ['nombre' => 'Pistas y veredas', 'icono' => 'construction'],
            ['nombre' => 'Alumbrado público', 'icono' => 'lightbulb'],
            ['nombre' => 'Áreas verdes', 'icono' => 'trees'],
            ['nombre' => 'Tránsito', 'icono' => 'traffic-cone'],
        ])->map(fn (array $categoria) => Categoria::firstOrCreate(
            ['nombre' => $categoria['nombre']],
            ['icono' => $categoria['icono']]
        ));

        $password = Hash::make('test1234');

        $ciudadano = User::firstOrCreate(['email' => 'carlos@example.com'], [
            'name' => 'Carlos Ciudadano',
            'password' => $password,
            'role' => 'ciudadano',
            'district_id' => $distritos->first()->id,
        ]);

        User::firstOrCreate(['email' => 'admin@municipal.gob.pe'], [
            'name' => 'Admin Municipal',
            'password' => $password,
            'role' => 'admin_municipal',
            'district_id' => $distritos->first()->id,
        ]);

        User::firstOrCreate(['email' => 'operador1@municipal.gob.pe'], [
            'name' => 'Operador Municipal',
            'password' => $password,
            'role' => 'operador',
            'district_id' => $distritos->first()->id,
        ]);

        User::firstOrCreate(['email' => 'sysadmin@reporteciudadano.pe'], [
            'name' => 'Admin Sistema',
            'password' => $password,
            'role' => 'admin_sistema',
        ]);

        Reporte::firstOrCreate(['titulo' => 'Bache peligroso cerca de la avenida principal'], [
            'user_id' => $ciudadano->id,
            'categoria_id' => $categorias->where('nombre', 'Pistas y veredas')->first()->id,
            'distrito_id' => $distritos->where('nombre', 'Cercado')->first()->id,
            'descripcion' => 'Hay un bache profundo que está afectando el tránsito y puede causar accidentes.',
            'direccion' => 'Av. Independencia, Arequipa',
            'latitud' => -16.3989000,
            'longitud' => -71.5350000,
            'estado' => 'pendiente',
            'estado_moderacion' => 'aprobado',
            'moderado' => true,
            'es_urgente' => true,
        ]);

        collect([
            ['nombre_servicio' => 'Policia Nacional', 'numero' => '105', 'descripcion' => 'Emergencias policiales y seguridad ciudadana.'],
            ['nombre_servicio' => 'Bomberos', 'numero' => '116', 'descripcion' => 'Incendios, rescates y emergencias medicas.'],
            ['nombre_servicio' => 'SAMU', 'numero' => '106', 'descripcion' => 'Atencion medica de urgencia.'],
            ['nombre_servicio' => 'Serenazgo', 'numero' => '054-000000', 'descripcion' => 'Apoyo municipal y patrullaje local.'],
        ])->each(fn (array $contacto) => EmergenciaContacto::firstOrCreate(
            ['nombre_servicio' => $contacto['nombre_servicio']],
            $contacto
        ));

        Notificacion::firstOrCreate([
            'user_id' => $ciudadano->id,
            'mensaje' => 'Bienvenido a Reporte Ciudadano. Ya puedes registrar incidencias.',
        ], [
            'tipo' => 'general',
        ]);
    }
}
