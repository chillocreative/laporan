<?php

namespace App\Http\Requests\MinitMesyuarat;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreMinitMesyuaratRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function isActingAsAdmin(): bool
    {
        return $this->user()->hasAnyRole(['super-admin', 'admin']);
    }

    public function targetUserId(): int
    {
        if ($this->isActingAsAdmin() && $this->filled('user_id')) {
            return (int) $this->input('user_id');
        }

        return $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                $this->isActingAsAdmin() ? 'required' : 'prohibited',
                'integer',
                Rule::exists('users', 'id'),
            ],
            'bulan' => [
                'required',
                'date',
                Rule::unique('minit_mesyuarats')->where('user_id', $this->targetUserId()),
            ],
            'file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->isActingAsAdmin() && $this->filled('user_id')) {
                $targetUser = User::find($this->input('user_id'));
                if (! $targetUser || ! $targetUser->hasRole('mpkk')) {
                    $validator->errors()->add('user_id', 'Pengguna yang dipilih bukan pengguna MPKK yang sah.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Sila pilih pengguna MPKK.',
            'bulan.unique' => 'Minit mesyuarat untuk bulan ini telah wujud bagi pengguna berkenaan. Sila kemaskini rekod sedia ada.',
            'file.required' => 'Sila muat naik fail minit mesyuarat.',
            'file.mimes' => 'Hanya fail PDF atau Word (DOC/DOCX) dibenarkan.',
            'file.max' => 'Saiz fail mestilah kurang daripada 5MB.',
        ];
    }
}
