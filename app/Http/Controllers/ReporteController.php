<?php

namespace App\Http\Controllers;

use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        $contexto = $this->buildContext($request);

        return view('reportes.ventas', $contexto);
    }

    public function export(Request $request)
    {
        $contexto = $this->buildContext($request);
        $periodo = $contexto['periodo'];
        $fecha = $contexto['fecha'];
        $mes = $contexto['mes'];
        $filename = sprintf('reporte_ventas_%s.csv', $periodo === 'mensual' ? str_replace('-', '', $mes) : str_replace('-', '', $fecha));

        return response()->streamDownload(function () use ($contexto) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Reporte de ventas', $contexto['periodLabel']]);
            fputcsv($handle, []);
            fputcsv($handle, ['Ventas totales', $contexto['ventasTotales']]);
            fputcsv($handle, ['Ingresos totales', '$' . number_format($contexto['ingresoTotal'], 0, ',', '.')]);
            fputcsv($handle, ['Items vendidos', $contexto['itemsTotales']]);
            fputcsv($handle, ['Hora de mayor tráfico', $contexto['peakHourLabel']]);
            fputcsv($handle, ['Ventas anuladas', $contexto['ventasAnuladas']]);
            fputcsv($handle, []);

            fputcsv($handle, ['Productos más vendidos']);
            fputcsv($handle, ['Producto', 'Cantidad vendida', 'Ingreso generado']);
            foreach ($contexto['topProducts'] as $producto) {
                fputcsv($handle, [
                    $producto->producto->nombre ?? 'N/A',
                    $producto->total_cantidad,
                    '$' . number_format($producto->total_revenue, 0, ',', '.'),
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Productos menos vendidos']);
            fputcsv($handle, ['Producto', 'Cantidad vendida', 'Ingreso generado']);
            foreach ($contexto['leastProducts'] as $producto) {
                fputcsv($handle, [
                    $producto->producto->nombre ?? 'N/A',
                    $producto->total_cantidad,
                    '$' . number_format($producto->total_revenue, 0, ',', '.'),
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Alertas de errores o robos']);
            if (empty($contexto['alertas'])) {
                fputcsv($handle, ['No hay alertas detectadas.']);
            } else {
                foreach ($contexto['alertas'] as $alerta) {
                    fputcsv($handle, [$alerta]);
                }
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function buildContext(Request $request): array
    {
        $periodo = $request->query('periodo', 'diario');
        $fecha = $request->query('fecha', now()->toDateString());
        $mes = $request->query('mes', now()->format('Y-m'));

        if (! in_array($periodo, ['diario', 'mensual'], true)) {
            $periodo = 'diario';
        }

        if ($periodo === 'mensual' && ! preg_match('/^\d{4}-\d{2}$/', $mes)) {
            $mes = now()->format('Y-m');
        }

        if ($periodo === 'diario' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            $fecha = now()->toDateString();
        }

        $periodLabel = $periodo === 'mensual'
            ? Carbon::parse($mes . '-01')->format('F Y')
            : Carbon::parse($fecha)->format('Y-m-d');

        $ventasBase = $this->applyPeriodoFilter(Venta::query()->where('estado', 'completada'), $periodo, $fecha, $mes);
        $anuladasBase = $this->applyPeriodoFilter(Venta::query()->where('estado', 'anulada'), $periodo, $fecha, $mes);

        $ventasTotales = $ventasBase->count();
        $ingresoTotal = (float) $ventasBase->sum('total');
        $itemsTotales = DetalleVenta::query()
            ->whereHas('venta', function ($query) use ($periodo, $fecha, $mes) {
                $this->applyPeriodoFilter($query->where('estado', 'completada'), $periodo, $fecha, $mes);
            })
            ->sum('cantidad');

        $topProducts = $this->buildProductRanking($periodo, $fecha, $mes, 'desc');
        $leastProducts = $this->buildProductRanking($periodo, $fecha, $mes, 'asc');

        $hourExpression = $this->datePartExpression('hour');
        $dayExpression = $this->datePartExpression('day');

        $hourlyResults = Venta::query()
            ->select(DB::raw("{$hourExpression} AS period"), DB::raw('COUNT(*) AS count'))
            ->where('estado', 'completada')
            ->when($periodo === 'diario', function ($query) use ($fecha) {
                $query->whereDate('created_at', $fecha);
            })
            ->when($periodo === 'mensual', function ($query) use ($mes) {
                [$year, $month] = explode('-', $mes);
                $query->whereYear('created_at', $year)->whereMonth('created_at', $month);
            })
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->keyBy(fn ($item) => $item->period)
            ->map(fn ($item) => (int) $item->count)
            ->all();

        $hours = range(0, 23);
        $hourLabels = array_map(fn ($hour) => sprintf('%02d:00', $hour), $hours);
        $hourCounts = array_map(fn ($hour) => $hourlyResults[$hour] ?? 0, $hours);

        $incomeResults = Venta::query()
            ->select(DB::raw(($periodo === 'mensual' ? "{$dayExpression} AS period" : "{$hourExpression} AS period")), DB::raw('SUM(total) AS revenue'))
            ->where('estado', 'completada')
            ->when($periodo === 'diario', function ($query) use ($fecha) {
                $query->whereDate('created_at', $fecha);
            })
            ->when($periodo === 'mensual', function ($query) use ($mes) {
                [$year, $month] = explode('-', $mes);
                $query->whereYear('created_at', $year)->whereMonth('created_at', $month);
            })
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->keyBy(fn ($item) => (int) $item->period)
            ->map(fn ($item) => (float) $item->revenue)
            ->all();

        if ($periodo === 'mensual') {
            $daysInMonth = Carbon::parse($mes . '-01')->daysInMonth;
            $incomeLabels = array_map(fn ($day) => sprintf('%02d', $day), range(1, $daysInMonth));
            $incomeValues = array_map(fn ($day) => $incomeResults[$day] ?? 0, range(1, $daysInMonth));
            $incomeLabelText = 'Ingresos por día';
        } else {
            $incomeLabels = $hourLabels;
            $incomeValues = array_map(fn ($hour) => $incomeResults[$hour] ?? 0, $hours);
            $incomeLabelText = 'Ingresos por hora';
        }

        $peakHour = null;
        if (! empty($hourlyResults)) {
            $bestHour = array_reduce(array_keys($hourlyResults), function ($carry, $hour) use ($hourlyResults) {
                if ($carry === null || $hourlyResults[$hour] > $hourlyResults[$carry]) {
                    return $hour;
                }

                return $carry;
            });

            $peakHour = (object) [
                'hour' => (int) $bestHour,
                'count' => $hourlyResults[$bestHour],
            ];
        }

        $peakHourLabel = $peakHour
            ? sprintf('%02d:00 - %02d:00 (%d ventas)', $peakHour->hour, ($peakHour->hour + 1) % 24, $peakHour->count)
            : 'No se registraron ventas en este periodo';

        $ventasAnuladas = $anuladasBase->count();
        $inventarioNegativo = Producto::where('stock', '<', 0)->count();
        $ventasSospechosas = Venta::query()
            ->where('estado', 'completada')
            ->when($periodo === 'diario', function ($query) use ($fecha) {
                $query->whereDate('created_at', $fecha);
            })
            ->when($periodo === 'mensual', function ($query) use ($mes) {
                [$year, $month] = explode('-', $mes);
                $query->whereYear('created_at', $year)->whereMonth('created_at', $month);
            })
            ->where('total', '<=', 0)
            ->count();

        $alertas = [];

        if ($ventasAnuladas > 0) {
            $alertas[] = "Se detectaron {$ventasAnuladas} venta(s) anulada(s) en este periodo. Revisa posibles errores, devoluciones o cancelaciones sospechosas.";
        }

        if ($inventarioNegativo > 0) {
            $alertas[] = "Existen {$inventarioNegativo} producto(s) con stock negativo. Investiga posibles inconsistencias de inventario o pérdida.";
        }

        if ($ventasSospechosas > 0) {
            $alertas[] = "Se detectaron {$ventasSospechosas} venta(s) con total menor o igual a cero. Verifica que no haya errores de captura.";
        }

        if ($ventasTotales === 0) {
            $alertas[] = 'No hay ventas registradas en este periodo. Esto puede indicar un problema en el flujo de caja o en el registro de ventas.';
        }

        return [
            'periodo' => $periodo,
            'fecha' => $fecha,
            'mes' => $mes,
            'periodLabel' => $periodLabel,
            'ventasTotales' => $ventasTotales,
            'ingresoTotal' => $ingresoTotal,
            'itemsTotales' => $itemsTotales,
            'topProducts' => $topProducts,
            'leastProducts' => $leastProducts,
            'hourLabels' => $hourLabels,
            'hourCounts' => $hourCounts,
            'incomeLabels' => $incomeLabels,
            'incomeValues' => $incomeValues,
            'incomeLabelText' => $incomeLabelText,
            'peakHourLabel' => $peakHourLabel,
            'ventasAnuladas' => $ventasAnuladas,
            'alertas' => $alertas,
        ];
    }

    private function applyPeriodoFilter($query, string $periodo, string $fecha, string $mes)
    {
        if ($periodo === 'mensual') {
            [$year, $month] = explode('-', $mes);
            return $query->whereYear('created_at', $year)->whereMonth('created_at', $month);
        }

        return $query->whereDate('created_at', $fecha);
    }

    private function buildProductRanking(string $periodo, string $fecha, string $mes, string $direction)
    {
        return DetalleVenta::query()
            ->select('producto_id', DB::raw('SUM(cantidad) AS total_cantidad'), DB::raw('SUM(cantidad * precio) AS total_revenue'))
            ->whereHas('venta', function ($query) use ($periodo, $fecha, $mes) {
                $this->applyPeriodoFilter($query->where('estado', 'completada'), $periodo, $fecha, $mes);
            })
            ->with('producto')
            ->groupBy('producto_id')
            ->when($direction === 'desc', fn ($query) => $query->orderByDesc('total_cantidad'), fn ($query) => $query->orderBy('total_cantidad'))
            ->limit(5)
            ->get();
    }

    private function datePartExpression(string $part): string
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            return match ($part) {
                'hour' => "CAST(strftime('%H', created_at) AS INTEGER)",
                'day' => "CAST(strftime('%d', created_at) AS INTEGER)",
                default => "CAST(strftime('%H', created_at) AS INTEGER)",
            };
        }

        return match ($part) {
            'hour' => 'HOUR(created_at)',
            'day' => 'DAY(created_at)',
            default => 'HOUR(created_at)',
        };
    }
}
