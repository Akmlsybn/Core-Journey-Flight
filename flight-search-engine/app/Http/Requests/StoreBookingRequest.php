<?php
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $passengerNames = $this->input('passenger_names', []);
        if (!is_array($passengerNames)) {
            $passengerNames = [];
        }

        $passengerNiks = $this->input('passenger_niks', []);
        if (!is_array($passengerNiks)) {
            $passengerNiks = [];
        }

        $passengerDobs = $this->input('passenger_dobs', []);
        if (!is_array($passengerDobs)) {
            $passengerDobs = [];
        }

        $passengerPhones = $this->input('passenger_phones', []);
        if (!is_array($passengerPhones)) {
            $passengerPhones = [];
        }

        $this->merge([
            'passenger_names' => array_values(array_map(
                static fn (mixed $name): string => trim((string) $name),
                $passengerNames
            )),
            'passenger_niks' => array_values(array_map(
                static fn (mixed $nik): string => trim((string) $nik),
                $passengerNiks
            )),
            'passenger_dobs' => array_values(array_map(
                static fn (mixed $dob): string => trim((string) $dob),
                $passengerDobs
            )),
            'passenger_phones' => array_values(array_map(
                static fn (mixed $phone): string => trim((string) $phone),
                $passengerPhones
            )),
            'seat_class' => strtolower(trim((string) $this->input('seat_class'))),
            'booking_email' => strtolower(trim((string) $this->input('booking_email'))),
            'back_to_detail' => trim((string) $this->input('back_to_detail')),
            'back_to_results' => trim((string) $this->input('back_to_results')),
            'back_to_form' => trim((string) $this->input('back_to_form')),
        ]);
    }

    public function rules(): array
    {
        $expectedPassengerCount = max(1, (int) $this->input('passenger_count', 1));

        return [
            'seat_class' => ['required', Rule::in(['economy', 'business', 'first_class'])],
            'passenger_count' => ['required', 'integer', 'min:1', 'max:7'],
            'passenger_names' => ['required', 'array', 'size:' . $expectedPassengerCount],
            'passenger_names.*' => ['required', 'string', 'min:3', 'max:120'],
            'passenger_niks' => ['required', 'array', 'size:' . $expectedPassengerCount],
            'passenger_niks.*' => ['required', 'digits:16'],
            'passenger_dobs' => ['required', 'array', 'size:' . $expectedPassengerCount],
            'passenger_dobs.*' => ['required', 'date', 'before:today'],
            'passenger_phones' => ['required', 'array', 'size:' . $expectedPassengerCount],
            'passenger_phones.*' => ['required', 'string', 'min:10', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'booking_email' => ['required', 'email:rfc'],
            'departure_slots' => ['nullable', 'array'],
            'departure_slots.*' => [Rule::in(['dawn', 'morning', 'afternoon', 'evening'])],
            'arrival_slots' => ['nullable', 'array'],
            'arrival_slots.*' => [Rule::in(['dawn', 'morning', 'afternoon', 'evening'])],
            'ancillary_services' => ['nullable', 'array'],
            'ancillary_services.*' => [Rule::in(['travel_insurance', 'extra_baggage'])],
            'back_to_detail' => ['nullable', 'url'],
            'back_to_results' => ['nullable', 'url'],
            'back_to_form' => ['nullable', 'url'],
        ];
    }

    public function messages(): array
    {
        return [
            'seat_class.required' => 'Kelas penerbangan wajib dipilih.',
            'seat_class.in' => 'Kelas penerbangan tidak valid. Pilih Economy, Business, atau First Class.',
            'passenger_count.required' => 'Jumlah penumpang wajib diisi.',
            'passenger_count.integer' => 'Jumlah penumpang harus berupa angka bulat.',
            'passenger_count.min' => 'Jumlah penumpang minimal 1.',
            'passenger_count.max' => 'Jumlah penumpang maksimal 7.',
            'passenger_names.required' => 'Nama penumpang wajib diisi.',
            'passenger_names.array' => 'Format nama penumpang tidak valid.',
            'passenger_names.size' => 'Jumlah nama penumpang harus sesuai jumlah penumpang.',
            'passenger_names.*.required' => 'Nama penumpang wajib diisi.',
            'passenger_names.*.min' => 'Nama penumpang minimal 3 karakter.',
            'passenger_names.*.max' => 'Nama penumpang maksimal 120 karakter.',
            'passenger_niks.required' => 'NIK penumpang wajib diisi.',
            'passenger_niks.array' => 'Format NIK penumpang tidak valid.',
            'passenger_niks.size' => 'Jumlah NIK penumpang harus sesuai jumlah penumpang.',
            'passenger_niks.*.required' => 'NIK penumpang wajib diisi.',
            'passenger_niks.*.digits' => 'NIK penumpang harus tepat 16 digit angka.',
            'passenger_dobs.required' => 'Tanggal lahir penumpang wajib diisi.',
            'passenger_dobs.array' => 'Format tanggal lahir penumpang tidak valid.',
            'passenger_dobs.size' => 'Jumlah tanggal lahir penumpang harus sesuai jumlah penumpang.',
            'passenger_dobs.*.required' => 'Tanggal lahir penumpang wajib diisi.',
            'passenger_dobs.*.date' => 'Tanggal lahir penumpang harus berupa tanggal yang valid.',
            'passenger_dobs.*.before' => 'Tanggal lahir penumpang harus sebelum hari ini.',
            'passenger_phones.required' => 'Nomor telepon penumpang wajib diisi.',
            'passenger_phones.array' => 'Format nomor telepon penumpang tidak valid.',
            'passenger_phones.size' => 'Jumlah nomor telepon penumpang harus sesuai jumlah penumpang.',
            'passenger_phones.*.required' => 'Nomor telepon penumpang wajib diisi.',
            'passenger_phones.*.min' => 'Nomor telepon penumpang minimal 10 karakter.',
            'passenger_phones.*.max' => 'Nomor telepon penumpang maksimal 20 karakter.',
            'passenger_phones.*.regex' => 'Nomor telepon penumpang hanya boleh berisi angka atau simbol telepon yang valid.',
            'booking_email.required' => 'Email pemesan wajib diisi.',
            'booking_email.email' => 'Format email pemesan tidak valid.',
            'departure_slots.array' => 'Format filter waktu keberangkatan tidak valid.',
            'departure_slots.*.in' => 'Pilihan filter waktu keberangkatan tidak valid.',
            'arrival_slots.array' => 'Format filter waktu kedatangan tidak valid.',
            'arrival_slots.*.in' => 'Pilihan filter waktu kedatangan tidak valid.',
            'ancillary_services.array' => 'Format layanan tambahan tidak valid.',
            'ancillary_services.*.in' => 'Pilihan layanan tambahan tidak valid.',
            'back_to_detail.url' => 'URL kembali ke detail tidak valid.',
            'back_to_results.url' => 'URL kembali ke hasil pencarian tidak valid.',
            'back_to_form.url' => 'URL kembali ke formulir tidak valid.',
        ];
    }
}
