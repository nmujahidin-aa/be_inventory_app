<?php

namespace App\Http\Requests\Item;

use Illuminate\Foundation\Http\FormRequest;

class UpdateItemRequest extends FormRequest {

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
            'category_id' => 'sometimes|exists:categories,id', 
            'unit_id' => 'sometimes|exists:units,id', 
            'name' => 'sometimes|string|max:150', 
            'description' => 'nullable|string', 
            'min_stock' => 'nullable|integer|min:0'
        ]; 
    }
}