<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'slug' => ['required', 'string', Rule::unique('products')->ignore($this->product?->id)],
            'name' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'discounted_price' => 'nullable|numeric',
        ];
    }
}
