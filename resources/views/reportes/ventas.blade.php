@extends('layout.app')

@section('titulo', 'BIG-DACO | Reporte de Ventas')

@section('contenido')

<a class="back-link" href="{{ url('/dashboard') }}">
    <i class="fa-solid fa-arrow-left"></i>
    Volver
</a>

<h2 class="page-title">Reporte de Ventas</h2>
<p class="page-subtitle">Analiza los productos más y menos vendidos, identifica horas pico y revisa alertas de posibles errores o robos.</p>

<section class="app-card" style="padding: 24px; margin-bottom: 20px;">
    <div class="report-panel">
        <div class="report-panel__info">
            <p class="report-panel__label">Periodo seleccionado</p>
            <h3 class="report-panel__value">{{ $periodo === 'mensual' ? 'Mensual' : 'Diario' }}</h3>
            <p class="report-panel__meta">{{ $periodLabel }}</p>
        </div>

        <form method="GET" action="{{ route('reportes.ventas') }}" class="report-filters" id="reportFilters">
            <div class="field-group">
                <label for="periodo">Periodo</label>
                <select id="periodo" name="periodo" class="form-control" onchange="togglePeriodInputs()">
                    <option value="diario" {{ $periodo === 'diario' ? 'selected' : '' }}>Diario</option>
                    <option value="mensual" {{ $periodo === 'mensual' ? 'selected' : '' }}>Mensual</option>
                </select>
            </div>

            <div class="field-group">
                <label for="fecha">Fecha</label>
                <input id="fecha" type="date" name="fecha" value="{{ $fecha }}" class="form-control" {{ $periodo === 'mensual' ? 'disabled' : '' }}>
            </div>

            <div class="field-group">
                <label for="mes">Mes</label>
                <input id="mes" type="month" name="mes" value="{{ $mes }}" class="form-control" {{ $periodo === 'diario' ? 'disabled' : '' }}>
            </div>

            <div class="report-actions">
                <button type="submit" class="btn-app btn-primary">Aplicar</button>
                <a href="{{ route('reportes.ventas.exportar', request()->query()) }}" class="btn-app btn-secondary">Descargar CSV</a>
            </div>
        </form>
    </div>
</section>

<section class="app-grid report-grid" style="margin-bottom: 20px;">
    <article class="app-card report-stat">
        <p class="label">Ventas totales</p>
        <p class="value">{{ $ventasTotales }}</p>
    </article>
    <article class="app-card report-stat report-stat--accent">
        <p class="label">Ingresos totales</p>
        <p class="value">${{ number_format($ingresoTotal, 0, ',', '.') }}</p>
    </article>
    <article class="app-card report-stat">
        <p class="label">Artículos vendidos</p>
        <p class="value">{{ $itemsTotales }}</p>
    </article>
    <article class="app-card report-stat">
        <p class="label">Hora de mayor clientes</p>
        <p class="value">{{ $peakHourLabel }}</p>
    </article>
    <article class="app-card report-stat">
        <p class="label">Ventas anuladas</p>
        <p class="value">{{ $ventasAnuladas }}</p>
    </article>
</section>

<section class="app-card report-section" style="margin-bottom: 20px;">
    <div class="report-section__header">
        <h3>Ventas por hora</h3>
        <span class="badge badge--success">Hora pico</span>
    </div>
    <canvas id="hourSalesChart" style="width:100%; max-height:320px;"></canvas>
</section>

<section class="app-card report-section" style="margin-bottom: 20px;">
    <div class="report-section__header">
        <h3>Top productos</h3>
        <span class="badge badge--success">Top 5</span>
    </div>
    <canvas id="topProductChart" style="width:100%; max-height:320px;"></canvas>
</section>

<section class="app-card report-section" style="margin-bottom: 20px;">
    <div class="report-section__header">
        <h3>Productos menos vendidos</h3>
        <span class="badge badge--warning">Oportunidades</span>
    </div>
    <canvas id="leastProductChart" style="width:100%; max-height:320px;"></canvas>
</section>

<section class="app-card report-section" style="margin-bottom: 20px;">
    <div class="report-section__header">
        <h3>{{ $incomeLabelText }}</h3>
        <span class="badge badge--success">Ingresos</span>
    </div>
    <canvas id="incomeChart" style="width:100%; max-height:320px;"></canvas>
</section>

<section class="app-card report-section" style="margin-bottom: 20px;">
    <div class="report-section__header">
        <h3>Productos más vendidos</h3>
        <span class="badge badge--success">Top 5</span>
    </div>
    @if ($topProducts->isEmpty())
        <p>No hay productos con ventas registradas en este periodo.</p>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Ingreso</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($topProducts as $item)
                        <tr>
                            <td>{{ $item->producto->nombre ?? 'N/A' }}</td>
                            <td>{{ $item->total_cantidad }}</td>
                            <td>${{ number_format($item->total_revenue, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>

<section class="app-card report-section" style="margin-bottom: 20px;">
    <div class="report-section__header">
        <h3>Productos menos vendidos</h3>
        <span class="badge badge--warning">Oportunidades</span>
    </div>
    @if ($leastProducts->isEmpty())
        <p>No hay productos con ventas registradas en este periodo.</p>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Ingreso</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($leastProducts as $item)
                        <tr>
                            <td>{{ $item->producto->nombre ?? 'N/A' }}</td>
                            <td>{{ $item->total_cantidad }}</td>
                            <td>${{ number_format($item->total_revenue, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>

<section class="app-card report-section report-alerts">
    <div class="report-section__header">
        <h3>Alertas de errores o robos</h3>
        <span class="badge badge--danger">Importante</span>
    </div>
    @if (empty($alertas))
        <p>No se detectaron alertas en este periodo.</p>
    @else
        <ul>
            @foreach ($alertas as $alerta)
                <li>{{ $alerta }}</li>
            @endforeach
        </ul>
    @endif
</section>

<style>
    .report-panel {
        display: grid;
        gap: 18px;
    }

    .report-panel__info {
        padding: 16px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
    }

    .report-panel__label {
        margin: 0 0 6px;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
    }

    .report-panel__value {
        margin: 0;
        font-size: 28px;
        color: var(--text);
    }

    .report-panel__meta {
        margin: 6px 0 0;
        color: #6f859f;
    }

    .report-filters {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 14px;
        align-items: end;
    }

    .field-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 700;
        color: #2f4b68;
        font-size: 14px;
    }

    .report-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
    }

    .report-actions .btn-secondary {
        background: #64748b;
    }

    .report-grid {
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    }

    .report-stat {
        padding: 22px;
        min-height: 140px;
    }

    .report-stat--accent {
        background: #f0fdf4;
        border-color: #d1fae5;
    }

    .report-section__header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 18px;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #fff;
    }

    .badge--success {
        background: #16a34a;
    }

    .badge--warning {
        background: #f59e0b;
    }

    .badge--danger {
        background: #dc2626;
    }

    .report-alerts ul {
        margin: 0;
        padding-left: 18px;
        color: #334155;
        line-height: 1.7;
    }

    .report-alerts li {
        margin-bottom: 10px;
    }

    @media (max-width: 820px) {
        .report-panel,
        .report-filters,
        .report-grid {
            grid-template-columns: 1fr;
        }

        .report-actions {
            justify-content: stretch;
        }

        .report-actions .btn-app {
            width: 100%;
        }
    }
</style>

<script>
    function togglePeriodInputs() {
        const periodo = document.getElementById('periodo').value;
        const fecha = document.getElementById('fecha');
        const mes = document.getElementById('mes');

        if (periodo === 'mensual') {
            fecha.disabled = true;
            mes.disabled = false;
        } else {
            fecha.disabled = false;
            mes.disabled = true;
        }
    }

    document.addEventListener('DOMContentLoaded', togglePeriodInputs);
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const hourLabels = @json($hourLabels);
    const hourCounts = @json($hourCounts);
    const topProductLabels = @json($topProducts->map(fn ($item) => $item->producto->nombre ?? 'N/A'));
    const topProductCounts = @json($topProducts->pluck('total_cantidad'));
    const leastProductLabels = @json($leastProducts->map(fn ($item) => $item->producto->nombre ?? 'N/A'));
    const leastProductCounts = @json($leastProducts->pluck('total_cantidad'));
    const incomeLabels = @json($incomeLabels);
    const incomeValues = @json($incomeValues);

    const hourCtx = document.getElementById('hourSalesChart');
    if (hourCtx) {
        new Chart(hourCtx, {
            type: 'line',
            data: {
                labels: hourLabels,
                datasets: [{
                    label: 'Ventas por hora',
                    data: hourCounts,
                    backgroundColor: 'rgba(37, 99, 235, 0.14)',
                    borderColor: '#2563eb',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                    pointBackgroundColor: '#2563eb',
                }],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { mode: 'index', intersect: false },
                },
                scales: {
                    x: { grid: { color: '#e2e8f0' } },
                    y: { beginAtZero: true, grid: { color: '#e2e8f0' } },
                },
            },
        });
    }

    const productCtx = document.getElementById('topProductChart');
    if (productCtx) {
        new Chart(productCtx, {
            type: 'bar',
            data: {
                labels: topProductLabels,
                datasets: [{
                    label: 'Unidades vendidas',
                    data: topProductCounts,
                    backgroundColor: '#14b8a6',
                    borderRadius: 10,
                }],
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    tooltip: { mode: 'nearest', intersect: true },
                },
                scales: {
                    x: { beginAtZero: true, grid: { color: '#e2e8f0' } },
                    y: { grid: { display: false } },
                },
            },
        });
    }

    const leastCtx = document.getElementById('leastProductChart');
    if (leastCtx) {
        new Chart(leastCtx, {
            type: 'bar',
            data: {
                labels: leastProductLabels,
                datasets: [{
                    label: 'Unidades vendidas',
                    data: leastProductCounts,
                    backgroundColor: '#f59e0b',
                    borderRadius: 10,
                }],
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    tooltip: { mode: 'nearest', intersect: true },
                },
                scales: {
                    x: { beginAtZero: true, grid: { color: '#e2e8f0' } },
                    y: { grid: { display: false } },
                },
            },
        });
    }

    const incomeCtx = document.getElementById('incomeChart');
    if (incomeCtx) {
        new Chart(incomeCtx, {
            type: 'line',
            data: {
                labels: incomeLabels,
                datasets: [{
                    label: @json($incomeLabelText),
                    data: incomeValues,
                    backgroundColor: 'rgba(34, 197, 94, 0.14)',
                    borderColor: '#22c55e',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                    pointBackgroundColor: '#22c55e',
                }],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { mode: 'index', intersect: false },
                },
                scales: {
                    x: { grid: { color: '#e2e8f0' } },
                    y: { beginAtZero: true, grid: { color: '#e2e8f0' } },
                },
            },
        });
    }
</script>

@endsection
