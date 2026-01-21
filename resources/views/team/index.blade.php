@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Team Performance Monitor') }}
        </h2>
        <p class="text-sm text-gray-600 mt-1">Current Month: {{ now()->format('F Y') }}</p>
    </div>

    <!-- Team Performance Grid -->
    @if(count($performance) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($performance as $member)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition">
                    <div class="p-6">
                        <!-- Member Info -->
                        <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-200">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                                    <span class="text-xl font-bold text-indigo-600">{{ substr($member['user']->name, 0, 1) }}</span>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-base font-semibold text-gray-900">{{ $member['user']->name }}</h3>
                                    <p class="text-xs text-gray-500">{{ $member['user']->roles->first()->display_name ?? 'User' }}</p>
                                    @if($member['user']->store)
                                    <p class="text-xs text-gray-500">📍 {{ $member['user']->store }}</p>
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('team.show', $member['user']->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>

                        <!-- Metrics -->
                        <div class="space-y-3">
                            <!-- Total Leads -->
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Total Leads</span>
                                <span class="text-sm font-bold text-gray-900">{{ $member['metrics']['total_leads'] }}</span>
                            </div>

                            <!-- Conversion Rate -->
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Conversion Rate</span>
                                <span class="text-sm font-bold {{ $member['metrics']['conversion_rate'] >= 20 ? 'text-green-600' : 'text-orange-600' }}">
                                    {{ $member['metrics']['conversion_rate'] }}%
                                </span>
                            </div>

                            <!-- Total Sales -->
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Total Sales</span>
                                <span class="text-sm font-bold text-gray-900">Rp {{ number_format($member['metrics']['total_sales'], 0, ',', '.') }}</span>
                            </div>

                            <!-- Commission Earned -->
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Commission</span>
                                <span class="text-sm font-bold text-green-600">Rp {{ number_format($member['metrics']['total_commission'], 0, ',', '.') }}</span>
                            </div>

                            <!-- Target Achievement -->
                            @if($member['metrics']['target'])
                            <div class="mt-4 pt-3 border-t border-gray-200">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-xs text-gray-600">Target Achievement</span>
                                    <span class="text-xs font-semibold text-gray-900">
                                        {{ $member['metrics']['target']->target_amount > 0 ? number_format(($member['metrics']['total_sales'] / $member['metrics']['target']->target_amount) * 100, 1) : 0 }}%
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                    @php
                                        $percentage = $member['metrics']['target']->target_amount > 0 
                                            ? min(($member['metrics']['total_sales'] / $member['metrics']['target']->target_amount) * 100, 100) 
                                            : 0;
                                    @endphp
                                    <div class="h-full {{ $percentage >= 100 ? 'bg-green-500' : ($percentage >= 75 ? 'bg-blue-500' : 'bg-yellow-500') }} rounded-full transition-all" 
                                         style="width: {{ $percentage }}%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    Target: Rp {{ number_format($member['metrics']['target']->target_amount, 0, ',', '.') }}
                                </p>
                            </div>
                            @else
                            <div class="mt-4 pt-3 border-t border-gray-200">
                                <p class="text-xs text-gray-500 text-center">No target set for this month</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Summary Stats -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg shadow-lg p-6 text-white">
                <p class="text-sm font-medium opacity-90">Total Team Members</p>
                <p class="text-3xl font-bold mt-2">{{ count($performance) }}</p>
            </div>
            <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg shadow-lg p-6 text-white">
                <p class="text-sm font-medium opacity-90">Total Sales</p>
                <p class="text-3xl font-bold mt-2">Rp {{ number_format(collect($performance)->sum('metrics.total_sales'), 0, ',', '.') }}</p>
            </div>
            <div class="bg-gradient-to-br from-purple-500 to-pink-600 rounded-lg shadow-lg p-6 text-white">
                <p class="text-sm font-medium opacity-90">Total Commissions</p>
                <p class="text-3xl font-bold mt-2">Rp {{ number_format(collect($performance)->sum('metrics.total_commission'), 0, ',', '.') }}</p>
            </div>
            <div class="bg-gradient-to-br from-orange-500 to-red-600 rounded-lg shadow-lg p-6 text-white">
                <p class="text-sm font-medium opacity-90">Total Leads</p>
                <p class="text-3xl font-bold mt-2">{{ collect($performance)->sum('metrics.total_leads') }}</p>
            </div>
        </div>
    @else
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-center py-10">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-gray-900">No team members found</h3>
                <p class="mt-1 text-sm text-gray-500">Team members will appear here once they are assigned.</p>
            </div>
        </div>
    @endif
@endsection
