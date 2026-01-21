@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Quotation') }}
        </h2>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <form method="POST" action="{{ route('quotations.store') }}" x-data="quotationForm()">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Lead Selection -->
                    <div class="md:col-span-2">
                        <label for="lead_id" class="block text-sm font-medium text-gray-700">Select Lead (Optional)</label>
                        <select name="lead_id" id="lead_id" x-model="leadId" @change="fillFromLead()"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">-- Manual Entry --</option>
                            @foreach($leads as $lead)
                                <option value="{{ $lead->id }}" data-name="{{ $lead->name }}" data-email="{{ $lead->email }}" data-phone="{{ $lead->phone }}" data-company="{{ $lead->company }}">
                                    {{ $lead->name }} - {{ $lead->company ?? 'No company' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Customer Name -->
                    <div>
                        <label for="customer_name" class="block text-sm font-medium text-gray-700">Customer Name *</label>
                        <input type="text" name="customer_name" id="customer_name" x-model="customerName" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            value="{{ $lead->name ?? old('customer_name') }}">
                    </div>

                    <!-- Customer Company -->
                    <div>
                        <label for="customer_company" class="block text-sm font-medium text-gray-700">Company</label>
                        <input type="text" name="customer_company" id="customer_company" x-model="customerCompany"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            value="{{ $lead->company ?? old('customer_company') }}">
                    </div>

                    <!-- Customer Email -->
                    <div>
                        <label for="customer_email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="customer_email" id="customer_email" x-model="customerEmail"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            value="{{ $lead->email ?? old('customer_email') }}">
                    </div>

                    <!-- Customer Phone -->
                    <div>
                        <label for="customer_phone" class="block text-sm font-medium text-gray-700">Phone</label>
                        <input type="text" name="customer_phone" id="customer_phone" x-model="customerPhone"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            value="{{ $lead->phone ?? old('customer_phone') }}">
                    </div>

                    <!-- Quotation Date -->
                    <div>
                        <label for="quotation_date" class="block text-sm font-medium text-gray-700">Quotation Date *</label>
                        <input type="date" name="quotation_date" id="quotation_date" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            value="{{ old('quotation_date', date('Y-m-d')) }}">
                    </div>

                    <!-- Discount -->
                    <div>
                        <label for="discount_percentage" class="block text-sm font-medium text-gray-700">Discount (%)</label>
                        <input type="number" name="discount_percentage" id="discount_percentage" min="0" max="100" step="0.01"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            value="{{ old('discount_percentage', 0) }}">
                    </div>
                </div>

                <!-- Items Section -->
                <div class="mt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Items</h3>
                    
                    <div class="space-y-4">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="p-4 border border-gray-200 rounded-lg">
                                <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                                    <!-- Product -->
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Product</label>
                                        <select :name="`items[${index}][product_id]`" x-model="item.product_id" @change="fillProductDetails(index)" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="">Select Product</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-name="{{ $product->name }}">
                                                    {{ $product->name }} - Rp {{ number_format($product->price, 0, ',', '.') }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Quantity -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Qty</label>
                                        <input type="number" :name="`items[${index}][quantity]`" x-model="item.quantity" min="1" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>

                                    <!-- Unit Price -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Unit Price</label>
                                        <input type="number" :name="`items[${index}][unit_price]`" x-model="item.unit_price" min="0" step="0.01" required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>

                                    <!-- Item Discount -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Disc (%)</label>
                                        <input type="number" :name="`items[${index}][discount_percentage]`" x-model="item.discount_percentage" min="0" max="100" step="0.01"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>

                                    <!-- Remove Button -->
                                    <div class="flex items-end">
                                        <button type="button" @click="removeItem(index)" class="w-full bg-red-600 text-white px-3 py-2 rounded-md hover:bg-red-700 text-sm">
                                            Remove
                                        </button>
                                    </div>
                                </div>

                                <!-- Notes -->
                                <div class="mt-3">
                                    <label class="block text-sm font-medium text-gray-700">Notes (Optional)</label>
                                    <input type="text" :name="`items[${index}][notes]`" x-model="item.notes"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        placeholder="Additional item notes...">
                                </div>
                            </div>
                        </template>
                    </div>

                    <button type="button" @click="addItem()" class="mt-4 inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Item
                    </button>
                </div>

                <!-- Notes & Terms -->
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes" id="notes" rows="4"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('notes') }}</textarea>
                    </div>

                    <div>
                        <label for="terms" class="block text-sm font-medium text-gray-700">Terms & Conditions</label>
                        <textarea name="terms" id="terms" rows="4"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('terms', "1. Pembayaran DP 60% setelah penandatanganan kontrak\n2. Pelunasan H+1 setelah pekerjaan selesai\n3. Pembayaran via transfer ke Bank Mandiri a/n PT Esdea Assistance Management\n4. Quotation ini berlaku selama 14 hari sejak tanggal terbit") }}</textarea>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <a href="{{ route('quotations.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Create Quotation
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function quotationForm() {
    return {
        leadId: '{{ request("lead_id") ?? "" }}',
        customerName: '{{ $lead->name ?? old("customer_name") }}',
        customerEmail: '{{ $lead->email ?? old("customer_email") }}',
        customerPhone: '{{ $lead->phone ?? old("customer_phone") }}',
        customerCompany: '{{ $lead->company ?? old("customer_company") }}',
        items: [
            { product_id: '', quantity: 1, unit_price: 0, discount_percentage: 0, notes: '' }
        ],
        
        fillFromLead() {
            const select = document.getElementById('lead_id');
            const option = select.options[select.selectedIndex];
            if (option.value) {
                this.customerName = option.dataset.name || '';
                this.customerEmail = option.dataset.email || '';
                this.customerPhone = option.dataset.phone || '';
                this.customerCompany = option.dataset.company || '';
            }
        },
        
        fillProductDetails(index) {
            const select = event.target;
            const option = select.options[select.selectedIndex];
            if (option.value) {
                this.items[index].unit_price = parseFloat(option.dataset.price) || 0;
            }
        },
        
        addItem() {
            this.items.push({ product_id: '', quantity: 1, unit_price: 0, discount_percentage: 0, notes: '' });
        },
        
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            } else {
                alert('At least one item is required');
            }
        }
    }
}
</script>
@endpush
