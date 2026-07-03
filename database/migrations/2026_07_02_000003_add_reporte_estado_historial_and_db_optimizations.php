<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reporte_estado_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporte_id')->constrained('reportes')->cascadeOnDelete();
            $table->string('estado_anterior', 30)->nullable();
            $table->string('estado_nuevo', 30);
            $table->timestamp('cambiado_en')->useCurrent();
            $table->timestamps();
        });

        Schema::table('reportes', function (Blueprint $table) {
            $table->index(['estado', 'distrito_id']);
            $table->index(['categoria_id', 'created_at']);
            $table->index(['es_urgente', 'estado']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::unprepared('DROP TRIGGER IF EXISTS trg_reportes_estado_historial');
            DB::unprepared(<<<'SQL'
CREATE TRIGGER trg_reportes_estado_historial
AFTER UPDATE ON reportes
FOR EACH ROW
BEGIN
    IF OLD.estado <> NEW.estado THEN
        INSERT INTO reporte_estado_historial (reporte_id, estado_anterior, estado_nuevo, cambiado_en, created_at, updated_at)
        VALUES (NEW.id, OLD.estado, NEW.estado, NOW(), NOW(), NOW());
    END IF;
END
SQL);

            DB::unprepared('DROP PROCEDURE IF EXISTS sp_resumen_reportes_mensual');
            DB::unprepared(<<<'SQL'
CREATE PROCEDURE sp_resumen_reportes_mensual(IN p_anio INT, IN p_mes INT)
BEGIN
    SELECT
        d.nombre AS distrito,
        c.nombre AS categoria,
        COUNT(r.id) AS total_reportes,
        SUM(r.estado = 'pendiente') AS pendientes,
        SUM(r.estado = 'en_proceso') AS en_proceso,
        SUM(r.estado = 'resuelto') AS resueltos,
        SUM(r.es_urgente = 1) AS urgentes
    FROM reportes r
    INNER JOIN distritos d ON d.id = r.distrito_id
    INNER JOIN categorias c ON c.id = r.categoria_id
    WHERE YEAR(r.created_at) = p_anio
      AND MONTH(r.created_at) = p_mes
    GROUP BY d.nombre, c.nombre
    ORDER BY total_reportes DESC, distrito ASC;
END
SQL);
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::unprepared('DROP TRIGGER IF EXISTS trg_reportes_estado_historial');
            DB::unprepared('DROP PROCEDURE IF EXISTS sp_resumen_reportes_mensual');
        }

        Schema::table('reportes', function (Blueprint $table) {
            $table->dropIndex(['estado', 'distrito_id']);
            $table->dropIndex(['categoria_id', 'created_at']);
            $table->dropIndex(['es_urgente', 'estado']);
        });

        Schema::dropIfExists('reporte_estado_historial');
    }
};
