<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FetchOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone_number' => ['required', 'regex:/^[0-9]{10,15}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone_number.required' => 'Nomor handphone wajib diisi.',
            'phone_number.regex' => 'Nomor handphone harus berupa angka 10 sampai 15 digit.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $phoneNumber = preg_replace('/\D+/', '', (string) $this->phone_number);

        $this->merge([
            'phone_number' => $phoneNumber,
        ]);
    }
}