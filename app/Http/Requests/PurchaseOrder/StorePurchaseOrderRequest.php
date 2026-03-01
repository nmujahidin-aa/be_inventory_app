<?php

namespace App\Http\Requests\PurchaseOrder;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseOrderRequest extends FormRequest {

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */

    public function rules(): array { 
        return [
            'vendor_id' => 'required|exists:vendors,id', 
            'notes' => 'nullable|string', 
            'items' => 'required|array|min:1', 
            'items.*.item_id' => 'required|exists:items,id', 
            'items.*.quantity_ordered' => 'required|integer|min:1', 
            'items.*.unit_price' => 'nullable|numeric|min:0', 
            'items.*.note' => 'nullable|string'
        ]; 
    }
}