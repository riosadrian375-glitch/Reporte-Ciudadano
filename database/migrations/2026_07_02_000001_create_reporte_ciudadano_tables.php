<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distritos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->timestamps();
        });

        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->string('icono', 50)->default('alert-circle');
            $table->timestamps();
        });

        Schema::create('reportes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('categoria_id')->constrained('categorias')->cascadeOnDelete();
            $table->foreignId('distrito_id')->constrained('distritos')->cascadeOnDelete();
            $table->string('titulo', 200);
            $table->text('descripcion');
            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();
            $table->string('direccion', 300)->nullable();
            $table->enum('estado', ['pendiente', 'en_proceso', 'resuelto', 'rechazado'])->default('pendiente');
            $table->boolean('moderado')->default(false);
            $table->enum('estado_moderacion', ['pendiente', 'aprobado', 'rechazado', 'en_revision'])->default('pendiente');
            $table->boolean('es_urgente')->default(false);
            $table->json('clima_momento')->nullable();
            $table->timestamps();
        });

        Schema::create('reporte_imagenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporte_id')->constrained('reportes')->cascadeOnDelete();
            $table->string('ruta_archivo', 300);
            $table->timestamps();
        });

        Schema::create('reporte_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporte_id')->constrained('reportes')->cascadeOnDelete();
            $table->string('ruta_archivo', 300);
            $table->integer('duracion_segundos')->nullable();
            $table->timestamps();
        });

        Schema::create('comentarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporte_id')->constrained('reportes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('contenido');
            $table->boolean('moderado')->default(false);
            $table->enum('estado_moderacion', ['pendiente', 'aprobado', 'rechazado', 'en_revision'])->default('pendiente');
            $table->timestamps();
        });

        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporte_id')->constrained('reportes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['reporte_id', 'user_id']);
        });

        Schema::create('guardados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporte_id')->constrained('reportes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['reporte_id', 'user_id']);
        });

        Schema::create('compartidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporte_id')->constrained('reportes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('asignaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporte_id')->constrained('reportes')->cascadeOnDelete();
            $table->foreignId('operador_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->enum('estado', ['activa', 'completada', 'cancelada'])->default('activa');
            $table->timestamps();
        });

        Schema::create('reporte_evidencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporte_id')->constrained('reportes')->cascadeOnDelete();
            $table->foreignId('operador_id')->constrained('users')->cascadeOnDelete();
            $table->text('comentario_resolucion');
            $table->string('ruta_archivo', 300);
            $table->string('tipo_mime', 100);
            $table->timestamps();
        });

        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('mensaje');
            $table->boolean('leida')->default(false);
            $table->string('tipo', 50)->default('general');
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->timestamps();
        });

        Schema::create('emergencias_contactos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_servicio', 150);
            $table->string('numero', 20);
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergencias_contactos');
        Schema::dropIfExists('notificaciones');
        Schema::dropIfExists('reporte_evidencias');
        Schema::dropIfExists('asignaciones');
        Schema::dropIfExists('compartidos');
        Schema::dropIfExists('guardados');
        Schema::dropIfExists('likes');
        Schema::dropIfExists('comentarios');
        Schema::dropIfExists('reporte_videos');
        Schema::dropIfExists('reporte_imagenes');
        Schema::dropIfExists('reportes');
        Schema::dropIfExists('categorias');
        Schema::dropIfExists('distritos');
    }
};
