<?php
declare(strict_types=1);

// filepath: app/Http/Controllers/Requests/FlightSearchRequest.php

namespace App\Http\Controllers\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FlightSearchRequest extends FormRequest
{
    private const SEAT_CLASS_MAP = [
        'economy' => 'economy',
        'business' => 'business',
        'first class' => 'first_class',
        'first_class' => 'first_class',
    ];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'origin' => strtoupper(trim((string) $this->input('origin'))),
            'destination' => strtoupper(trim((string) $this->input('destination'))),
            'seat_class' => $this->normalizeSeatClass($this->input('seat_class')),
        ]);
    }

    public function rules(): array
    {
        return [
            'origin' => [
                'required',
                'string',
                'size:3',
                Rule::exists('airports', 'airport_code'),
            ],
            'destination' => [
                'required',
                'string',
                'size:3',
                'different:origin',
                Rule::exists('airports', 'airport_code'),
            ],
            'departure_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'passenger_count' => [
                'required',
                'integer',
                'min:1',
                'max:7',
            ],
            'seat_class' => [
                'required',
                'string',
                Rule::in(array_values(self::SEAT_CLASS_MAP)),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'origin.required' => 'Origin is required.',
            'origin.size' => 'Origin must be a 3-letter airport code.',
            'origin.exists' => 'Origin airport code is invalid.',
            'destination.required' => 'Destination is required.',
            'destination.size' => 'Destination must be a 3-letter airport code.',
            'destination.different' => 'Destination must be different from origin.',
            'destination.exists' => 'Destination airport code is invalid.',
            'departure_date.required' => 'Departure date is required.',
            'departure_date.date' => 'Departure date must be a valid date.',
            'departure_date.after_or_equal' => 'Departure date must be today or a future date.',
            'passenger_count.required' => 'Passenger count is required.',
            'passenger_count.integer' => 'Passenger count must be an integer.',
            'passenger_count.min' => 'Passenger count must be at least 1.',
            'passenger_count.max' => 'Passenger count must not be greater than 7.',
            'seat_class.required' => 'Seat class is required.',
            'seat_class.in' => 'Seat class must be Economy, Business, or First Class.',
        ];
    }

    private function normalizeSeatClass(mixed $seatClass): ?string
    {
        if ($seatClass === null) {
            return null;
        }

        $normalized = strtolower(trim((string) $seatClass));
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        return self::SEAT_CLASS_MAP[$normalized] ?? $normalized;
    }
}