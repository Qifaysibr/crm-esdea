@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Quotation #{{ $quotation->quotation_number }}
            </h2>
            <p class="text-sm text-gray-600 mt-1">
                Created on {{ \Carbon\Carbon::parse($quotation->quotation_date)->format('d F Y') }}
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('quotations.pdf', $quotation->id) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                View PDF
            </a>
            
            @if($quotation->status !== 'converted')
            <form method="POST" action="{{ route('quotations.convert', $quotation->id) }}" onsubmit="return confirm('Convert this quotation to invoice?');">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                    Convert to Invoice
                </button>
            </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Customer Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Customer Information</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Name</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $quotation->customer_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Company</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $quotation->customer_company ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $quotation->customer_email ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Phone</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $quotation->customer_phone ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Items -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Items</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Discount</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($quotation->items as $item)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $item->product_name }}</div>
                                        @if($item->notes)
                                        <div class="text-xs text-gray-500">{{ $item->notes }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ $item->quantity }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right">
                                        @if($item->discount_percentage > 0)
                                            {{ $item->discount_percentage }}%
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="5" class="px-4 py-3 text-sm font-medium text-gray-900 text-right">Subtotal:</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right">Rp {{ number_format($quotation->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @if($quotation->discount_amount > 0)
                                <tr>
                                    <td colspan="5" class="px-4 py-3 text-sm text-gray-900 text-right">Discount ({{ $quotation->discount_percentage }}%):</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right">- Rp {{ number_format($quotation->discount_amount, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                                <tr class="bg-gray-100">
                                    <td colspan="5" class="px-4 py-3 text-base font-bold text-gray-900 text-right">Total:</td>
                                    <td class="px-4 py-3 text-base font-bold text-indigo-600 text-right">Rp {{ number_format($quotation->total, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Notes & Terms -->
            @if($quotation->notes || $quotation->terms)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($quotation->notes)
                    <div class="mb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Notes</h3>
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $quotation->notes }}</p>
                    </div>
                    @endif

                    @if($quotation->terms)
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Terms & Conditions</h3>
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $quotation->terms }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Status Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Status</h3>
                    <span class="px-3 py-2 inline-flex text-sm leading-5 font-semibold rounded-full 
                        {{ $quotation->status == 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                        {{ $quotation->status == 'sent' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $quotation->status == 'approved' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $quotation->status == 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                        {{ $quotation->status == 'converted' ? 'bg-purple-100 text-purple-800' : '' }}">
                        {{ ucfirst($quotation->status) }}
                    </span>
                </div>
            </div>

            <!-- Details Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Details</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Quotation Date</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($quotation->quotation_date)->format('d F Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Valid Until</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($quotation->valid_until)->format('d F Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created By</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $quotation->creator->name ?? 'Unknown' }}</dd>
                        </div>
                        @if($quotation->lead)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Related Lead</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <a href="{{ route('leads.edit', $quotation->lead->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ $quotation->lead->name }}
                                </a>
                            </dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection
