<?php

namespace App\Http\Requests\Receiving;

use Illuminate\Foundation\Http\FormRequest;

class AddReceivingItemRequest extends FormRequest {

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
            'purchase_order_item_id' => 'required|exists:purchase_order_items,id', 
            'item_id' => 'required|exists:items,id', 
            'quantity_received' => 'required|integer|min:1', 
            'quality_status' => 'nullable|in:good,damaged,returned', 
            'note' => 'nullable|string'
        ]; 
    }
}