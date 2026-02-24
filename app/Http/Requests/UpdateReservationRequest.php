<?php

namespace App\Http\Requests;

use App\Models\TableSeat;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $reservation = $this->route('reservation'); // Get the reservation being updated

        if ($this->user()->hasRole('admin')) {
            return true; // Admins can update any reservation
        }

        return $reservation && $reservation->status === 'ongoing' && $reservation->user_id === $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->user()->hasRole('admin')) {
            return [
                'table' => ['required', 'exists:table_seats,id'],
                'datetime' => ['required', 'date', 'after:now'],
                'info' => ['nullable', 'string', 'max:500'],
            ];
        }

        return [
            'table' => [
                'required',
                'exists:table_seats,id',
                function ($attribute, $value, $fail) {
                    $table = TableSeat::find($value);
                    $reservation = $this->route('reservation');

                    // Allow the customer to keep their currently assigned table, otherwise check availability
                    if (!$table || ($table->status !== 'available' && $table->id !== $reservation->table_id)) {
                        $fail('The selected table is not available.');
                    }
                },
            ],
            'datetime' => ['required', 'date', 'after:now'],
            'info' => ['nullable', 'string', 'max:500'],
        ];
    }
}
