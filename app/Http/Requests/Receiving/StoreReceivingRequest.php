<?php

namespace App\Http\Requests\Receiving;

use Illuminate\Foundation\Http\FormRequest;

class StoreReceivingRequest extends FormRequest {

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
            'purchase_order_id' => 'required|exists:purchase_orders,id', 
            'notes' => 'nullable|string'
        ]; 
    }
}