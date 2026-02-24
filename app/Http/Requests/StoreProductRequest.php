<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:products,name',
            'description' => 'required|string|max:1000|min:10',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'type' => 'required|in:signature,normal',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp,avif',
        ];
    }
}
