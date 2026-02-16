<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'is_encrypted',
        'group_name',
    ];

    protected function casts(): array
    {
        return [
            'is_encrypted' => 'boolean',
        ];
    }

    public function getValueAttribute(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }

        if ($this->is_encrypted) {
            try {
                return Crypt::decryptString($raw);
            } catch (\Exception) {
                return null;
            }
        }

        return $raw;
    }

    public function setValueAttribute(?string $value): void
    {
        if ($value !== null && $this->is_encrypted) {
            $this->attributes['value'] = Crypt::encryptString($value);
        } else {
            $this->attributes['value'] = $value;
        }
    }
}
