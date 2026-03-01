<?php

namespace App\Http\Requests\Item;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequest extends FormRequest {

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
            'category_id' => 'required|exists:categories,id', 
            'unit_id' => 'required|exists:units,id', 
            'code' => 'required|string|max:50|unique:items,code', 
            'name' => 'required|string|max:150', 
            'description' => 'nullable|string', 
            'min_stock' => 'nullable|integer|min:0'
        ]; 
    }
}