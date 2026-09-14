<?php

namespace App\Http\Requests\PenyataKewangan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePenyataKewanganRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bulan' => [
                'sometimes',
                'required',
                'date',
                Rule::unique('penyata_kewangans')
                    ->ignore($this->route('penyata_kewangan'))
                    ->where('user_id', $this->route('penyata_kewangan')->user_id),
            ],
            'file' => [
                'nullable',
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
            'file.mimes' => 'Hanya fail PDF dibenarkan.',
            'file.max' => 'Saiz fail mestilah kurang daripada 5MB.',
        ];
    }
}
