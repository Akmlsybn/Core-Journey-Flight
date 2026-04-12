<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FlightSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function getRedirectUrl(): string
    {
        return route('flights.search');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'origin' => strtoupper(trim((string) $this->input('origin', ''))),
            'destination' => strtoupper(trim((string) $this->input('destination', ''))),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'origin' => ['required', 'string', 'max:10', 'regex:/^[A-Z0-9]+$/'],
            'destination' => ['required', 'string', 'max:10', 'regex:/^[A-Z0-9]+$/', 'different:origin'],
            'departure_date' => ['required', 'date', 'after_or_equal:today'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'origin.regex' => 'Kode asal hanya boleh huruf dan angka.',
            'destination.regex' => 'Kode tujuan hanya boleh huruf dan angka.',
            'destination.different' => 'Bandara tujuan harus berbeda dari bandara asal.',
            'departure_date.after_or_equal' => 'Tanggal keberangkatan tidak boleh sebelum hari ini.',
        ];
    }
}
