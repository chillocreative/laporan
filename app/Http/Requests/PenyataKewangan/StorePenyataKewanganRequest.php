<?php

namespace App\Http\Requests\PenyataKewangan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePenyataKewanganRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bulan' => [
                'required',
                'date',
                Rule::unique('penyata_kewangans')
                    ->where('user_id', $this->user()->id),
            ],
            'file' => [
                'required',
                'file',
                'mimes:pdf',
                'max:5120',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'bulan.unique' => 'Penyata kewangan untuk bulan ini telah wujud. Sila kemaskini rekod sedia ada.',
            'file.required' => 'Sila muat naik fail penyata kewangan.',
            'file.mimes' => 'Hanya fail PDF dibenarkan.',
            'file.max' => 'Saiz fail mestilah kurang daripada 5MB.',
        ];
    }
}
