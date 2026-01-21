@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <div class="flex items-center">
            <a href="{{ route('team.index') }}" class="mr-4 text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Member Performance') }}: {{ $user->name }}
                </h2>
                <p class="text-sm text-gray-500">{{ $user->email }} | {{ $user->store ?? 'Global' }}</p>
            </div>
        </div>
    </div>

    <!-- Monthly Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-indigo-500">
            <p class="text-xs font-semibold text-gray-500 uppercase">Monthly Sales</p>
            <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($metrics['total_sales'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-green-500">
            <p class="text-xs font-semibold text-gray-500 uppercase">Commission</p>
            <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($metrics['total_commission'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-blue-500">
            <p class="text-xs font-semibold text-gray-500 uppercase">Total Leads</p>
            <p class="text-2xl font-bold text-gray-900">{{ $metrics['total_leads'] }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-purple-500">
            <p class="text-xs font-semibold text-gray-500 uppercase">Conv. Rate</p>
            <p class="text-2xl font-bold text-gray-900">{{ $metrics['conversion_rate'] }}%</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Lead Stats & Distribution -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Lead Status Distribution</h3>
                <div class="space-y-4">
                    @forelse($metrics['leads_by_status'] as $status)
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600">{{ $status->status->display_name }}</span>
                                <span class="font-semibold">{{ $status->count }}</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ $metrics['total_leads'] > 0 ? ($status->count / $metrics['total_leads']) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 italic">No leads active this month.</p>
                    @endforelse
                </div>
            </div>

            @if($metrics['target'])
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Target Progress</h3>
                <div class="text-center">
                    @php
                        $percentage = $metrics['target']->target_amount > 0 
                            ? round(($metrics['total_sales'] / $metrics['target']->target_amount) * 100, 1) 
                            : 0;
                    @endphp
                    <div class="inline-flex items-center justify-center p-4">
                        <span class="text-4xl font-bold {{ $percentage >= 100 ? 'text-green-600' : 'text-indigo-600' }}">{{ $percentage }}%</span>
                    </div>
                    <p class="text-sm text-gray-500">
                        Achieved: Rp {{ number_format($metrics['total_sales'], 0, ',', '.') }}<br>
                        Target: Rp {{ number_format($metrics['target']->target_amount, 0, ',', '.') }}
                    </p>
                </div>
            </div>
            @endif
        </div>

        <!-- Recent Activities -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Recent Leads -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Leads Activity</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Activity</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($recentLeads as $lead)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <a href="{{ route('leads.edit', $lead->id) }}" class="text-indigo-600 hover:text-indigo-900">{{ $lead->name }}</a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full" style="background-color: {{ $lead->status->color ?? '#eee' }}20; color: {{ $lead->status->color ?? '#333' }}">
                                            {{ $lead->status->display_name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $lead->last_activity_at ? $lead->last_activity_at->diffForHumans() : 'No activity' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">No recent leads found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Invoices -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Invoices</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($recentInvoices as $invoice)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <a href="{{ route('invoices.show', $invoice->id) }}" class="text-indigo-600 hover:text-indigo-900">{{ $invoice->invoice_number }}</a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $invoice->customer_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-semibold">Rp {{ number_format($invoice->total, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                            {{ $invoice->payment_status == 'paid' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ ucfirst($invoice->payment_status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No recent invoices found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
