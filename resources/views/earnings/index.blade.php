@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Earnings & Commission Report') }}
        </h2>
    </div>

    <!-- Date Filter -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6">
        <form method="GET" action="{{ route('earnings.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ $startDate }}"
                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ $endDate }}"
                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-indigo-600 text-white font-semibold py-1.5 px-4 border border-transparent rounded shadow hover:bg-indigo-700 transition">
                    Filter
                </button>
            </div>
            <div class="flex items-end">
                <a href="{{ route('earnings.export', request()->all()) }}" class="bg-green-600 text-white font-semibold py-1.5 px-4 border border-transparent rounded shadow hover:bg-green-700 transition">
                    Export
                </a>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium opacity-90">Total Earnings</p>
                    <p class="text-3xl font-bold mt-2">Rp {{ number_format($totalEarned, 0, ',', '.') }}</p>
                    <p class="text-xs opacity-75 mt-1">From {{ $totalTransactions }} transactions</p>
                </div>
                <svg class="w-16 h-16 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium opacity-90">Average Per Transaction</p>
                    <p class="text-3xl font-bold mt-2">Rp {{ $totalTransactions > 0 ? number_format($totalEarned / $totalTransactions, 0, ',', '.') : 0 }}</p>
                    <p class="text-xs opacity-75 mt-1">Period: {{ \Carbon\Carbon::parse($startDate)->format('d M') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
                </div>
                <svg class="w-16 h-16 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Earnings Breakdown -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Commission Breakdown by Invoice</h3>
            
            @if(count($earningsData) > 0)
                <div class="space-y-6">
                    @foreach($earningsData as $data)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <!-- Invoice Header -->
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h4 class="text-base font-semibold text-gray-900">{{ $data['invoice_number'] }}</h4>
                                <p class="text-sm text-gray-600">{{ $data['customer_name'] }}</p>
                                <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($data['invoice_date'])->format('d F Y') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600">Invoice Total</p>
                                <p class="text-lg font-bold text-gray-900">Rp {{ number_format($data['total_amount'], 0, ',', '.') }}</p>
                                <p class="text-sm font-semibold text-green-600">Commission: Rp {{ number_format($data['total_commission'], 0, ',', '.') }}</p>
                            </div>
                        </div>

                        <!-- Items Breakdown -->
                        @if(count($data['items']) > 0)
                        <div class="border-t border-gray-200 pt-4">
                            <h5 class="text-sm font-medium text-gray-700 mb-3">Commission Details per Item</h5>
                            <div class="space-y-3">
                                @foreach($data['items'] as $item)
                                <div class="bg-gray-50 rounded p-3">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-900">{{ $item['product_name'] }}</p>
                                            <p class="text-xs text-gray-600">
                                                Qty: {{ $item['quantity'] }} × Rp {{ number_format($item['unit_price'], 0, ',', '.') }}
                                                = Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                            </p>
                                            @if($item['refund'] && $item['refund'] > 0)
                                            <p class="text-xs text-red-600">Refund: Rp {{ number_format($item['refund'], 0, ',', '.') }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Commission Types -->
                                    @if(count($item['commissions']) > 0)
                                    <div class="mt-2 space-y-1">
                                        @foreach($item['commissions'] as $comm)
                                        <div class="flex justify-between items-center text-xs">
                                            <span class="text-gray-600">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                                                    {{ ucfirst($comm['type']) }}
                                                </span>
                                            </span>
                                            <span class="font-semibold text-green-600">+ Rp {{ number_format($comm['amount'], 0, ',', '.') }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-10">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900">No earnings data found</h3>
                    <p class="mt-1 text-sm text-gray-500">Commissions will appear here once invoices are paid.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
