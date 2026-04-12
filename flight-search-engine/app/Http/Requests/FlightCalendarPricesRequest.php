<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class FlightCalendarPricesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'origin' => strtoupper(trim((string) $this->query('origin', ''))),
            'destination' => strtoupper(trim((string) $this->query('destination', ''))),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $maxYear = (int) now()->addYear()->year;

        return [
            'origin' => ['required', 'string', 'max:10', 'regex:/^[A-Z0-9]+$/'],
            'destination' => ['required', 'string', 'max:10', 'regex:/^[A-Z0-9]+$/', 'different:origin'],
            'year' => ['required', 'integer', 'min:2000', 'max:'.$maxYear],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
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
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
