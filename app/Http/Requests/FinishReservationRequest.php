<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FinishReservationRequest extends FormRequest
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
            'send_discount' => 'nullable|in:yes',
            'discount_percentage' => 'nullable|required_if:send_discount,yes|integer|between:1,100',
        ];
    }
}
