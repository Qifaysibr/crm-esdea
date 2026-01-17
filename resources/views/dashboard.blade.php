@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <div class="flex items-center space-x-2">
            <input type="date" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" value="{{ now()->format('Y-m-d') }}">
        </div>
    </div>

    <!-- Funnel Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
        @foreach($funnelStats as $stat)
        <div class="bg-white rounded-lg shadow p-6 border-l-4" style="border-color: {{ $stat['color'] }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">{{ $stat['name'] }}</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stat['count'] }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Financial Analytics -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium opacity-90">Potensial Komisi</p>
                    <p class="text-3xl font-bold mt-2">Rp {{ number_format($financialStats['potential'], 0, ',', '.') }}</p>
                    <p class="text-xs opacity-75 mt-1">Dari Proforma/Unpaid</p>
                </div>
                <svg class="w-16 h-16 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium opacity-90">Komisi Diraih</p>
                    <p class="text-3xl font-bold mt-2">Rp {{ number_format($financialStats['earned'], 0, ',', '.') }}</p>
                    <p class="text-xs opacity-75 mt-1">Dari transaksi Paid</p>
                </div>
                <svg class="w-16 h-16 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Target Progress & Chart -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Target Progress -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Target Progress Bulan Ini</h3>
            <div class="space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Target: Rp {{ number_format($targetProgress['target'], 0, ',', '.') }}</span>
                    <span class="text-gray-600">Tercapai: Rp {{ number_format($targetProgress['achieved'], 0, ',', '.') }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full transition-all duration-500" 
                         style="width: {{ min($targetProgress['percentage'], 100) }}%"></div>
                </div>
                <p class="text-right text-lg font-bold text-indigo-600">{{ number_format($targetProgress['percentage'], 1) }}%</p>
            </div>
        </div>

        <!-- Sales Chart -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Tren Penjualan</h3>
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    <!-- Leaderboard & Smart Reminder -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Leaderboard -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">🏆 Leaderboard</h3>
            <div class="space-y-3">
                @foreach($leaderboard as $index => $user)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <span class="flex items-center justify-center w-8 h-8 rounded-full {{ $index === 0 ? 'bg-yellow-400 text-yellow-900' : ($index === 1 ? 'bg-gray-300' : 'bg-orange-300') }} font-bold text-sm">
                            {{ $index + 1 }}
                        </span>
                        <div>
                            <p class="font-medium text-gray-900">{{ $user['name'] }}</p>
                            @if($user['store'])
                            <p class="text-xs text-gray-500">{{ $user['store'] }}</p>
                            @endif
                        </div>
                    </div>
                    <p class="font-bold text-indigo-600">Rp {{ number_format($user['total_sales'], 0, ',', '.') }}</p>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Stagnant Leads Alert -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">⚠️ Smart Reminder</h3>
            @if($stagnantLeads->count() > 0)
            <div class="space-y-3">
                <p class="text-sm text-gray-600 mb-3">Leads yang tidak diupdate lebih dari 3 hari:</p>
                @foreach($stagnantLeads as $lead)
                <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-gray-900">{{ $lead->name }}</p>
                            <p class="text-xs text-gray-600">{{ $lead->company ?? 'No company' }}</p>
                            <p class="text-xs text-gray-500 mt-1">Last update: {{ $lead->last_activity_at?->diffForHumans() }}</p>
                        </div>
                        <a href="{{ route('leads.edit', $lead) }}" class="px-3 py-1 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">
                            Follow Up
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-500 text-center py-8">Semua leads sudah diupdate! 👍</p>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: @json($salesChartData['labels']),
        datasets: [{
            label: 'Penjualan (Rp)',
            data: @json($salesChartData['data']),
            borderColor: 'rgb(99, 102, 241)',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rp ' + value.toLocaleString('id-ID');
                    }
                }
            }
        }
    }
});
</script>
@endpush
@endsection
