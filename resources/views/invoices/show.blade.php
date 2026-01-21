@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Invoice #{{ $invoice->invoice_number }}
            </h2>
            <p class="text-sm text-gray-600 mt-1">
                Issued on {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d F Y') }}
            </p>
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
                            <dd class="mt-1 text-sm text-gray-900">{{ $invoice->customer_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Company</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $invoice->customer_company ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $invoice->customer_email ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Phone</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $invoice->customer_phone ?? '-' }}</dd>
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
                                @foreach($invoice->items as $item)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $item->product_name }}</div>
                                        @if($item->description)
                                        <div class="text-xs text-gray-500">{{ $item->description }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ $item->quantity }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right">
                                        @if($item->discount_amount > 0)
                                            Rp {{ number_format($item->discount_amount, 0, ',', '.') }}
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
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @if($invoice->discount_amount > 0)
                                <tr>
                                    <td colspan="5" class="px-4 py-3 text-sm text-gray-900 text-right">Discount:</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right">- Rp {{ number_format($invoice->discount_amount, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                                <tr class="bg-gray-100">
                                    <td colspan="5" class="px-4 py-3 text-base font-bold text-gray-900 text-right">Total:</td>
                                    <td class="px-4 py-3 text-base font-bold text-indigo-600 text-right">Rp {{ number_format($invoice->total, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Commission Details (if paid) -->
            @if($invoice->payment_status === 'paid' && $invoice->commissions->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Commission Distribution</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($invoice->commissions as $commission)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $commission->user->name ?? 'Unknown' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ ucfirst($commission->commission_type) }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right">Rp {{ number_format($commission->amount, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $commission->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $commission->status == 'approved' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $commission->status == 'paid' ? 'bg-blue-100 text-blue-800' : '' }}">
                                            {{ ucfirst($commission->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Payment Status -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Status</h3>
                    <span class="px-3 py-2 inline-flex text-sm leading-5 font-semibold rounded-full 
                        {{ $invoice->payment_status == 'unpaid' ? 'bg-red-100 text-red-800' : '' }}
                        {{ $invoice->payment_status == 'partial' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $invoice->payment_status == 'paid' ? 'bg-green-100 text-green-800' : '' }}">
                        {{ ucfirst($invoice->payment_status) }}
                    </span>

                    @if($invoice->payment_status !== 'paid')
                    <form method="POST" action="{{ route('invoices.updatePayment', $invoice->id) }}" class="mt-6 space-y-4" x-data="{ status: '{{ $invoice->payment_status }}' }">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label for="payment_status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="payment_status" id="payment_status" x-model="status" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="unpaid">Unpaid</option>
                                <option value="partial">Partial</option>
                                <option value="paid">Paid</option>
                            </select>
                        </div>

                        <div>
                            <label for="paid_amount" class="block text-sm font-medium text-gray-700">Paid Amount</label>
                            <input type="number" name="paid_amount" id="paid_amount" step="0.01" min="0" :max="{{ $invoice->total }}" required
                                value="{{ $invoice->paid_amount ?? 0 }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <p class="mt-1 text-xs text-gray-500">Total: Rp {{ number_format($invoice->total, 0, ',', '.') }}</p>
                        </div>

                        <div x-show="status !== 'unpaid'">
                            <label for="paid_date" class="block text-sm font-medium text-gray-700">Payment Date</label>
                            <input type="date" name="paid_date" id="paid_date"
                                value="{{ $invoice->paid_date ?? date('Y-m-d') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="payment_notes" class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea name="payment_notes" id="payment_notes" rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ $invoice->payment_notes }}</textarea>
                        </div>

                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            Update Payment
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            <!-- Details Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Details</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Invoice Date</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d F Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Due Date</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d F Y') }}</dd>
                        </div>
                        @if($invoice->paid_date)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Paid Date</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($invoice->paid_date)->format('d F Y') }}</dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created By</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $invoice->creator->name ?? 'Unknown' }}</dd>
                        </div>
                        @if($invoice->quotation)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">From Quotation</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <a href="{{ route('quotations.show', $invoice->quotation->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ $invoice->quotation->quotation_number }}
                                </a>
                            </dd>
                        </div>
                        @endif
                        @if($invoice->lead)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Related Lead</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <a href="{{ route('leads.edit', $invoice->lead->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ $invoice->lead->name }}
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
