<?php

namespace Tests\Unit;

use App\Support\MpkkDun;
use Tests\TestCase;

class MpkkDunTest extends TestCase
{
    /**
     * @dataProvider cases
     */
    public function test_maps_mpkk_name_to_dun(string $name, string $dun): void
    {
        $this->assertSame($dun, MpkkDun::dunFor($name));
    }

    public static function cases(): array
    {
        return [
            ['MPKK BERTAM INDAH', 'Pinang Tunggal'],
            ['MPKK BUMBUNG LIMA', 'Pinang Tunggal'],
            ['MPKK KAMPUNG BAHARU', 'Pinang Tunggal'],
            ['MPKK KAMPUNG SELAMAT SELATAN', 'Pinang Tunggal'],
            ['MPKK KAMPUNG SELAMAT UTARA', 'Pinang Tunggal'],
            ['MPKK KG SELAMAT RANGKAIAN', 'Pinang Tunggal'],
            ['MPKK KUBANG MENERONG', 'Pinang Tunggal'],
            ['MPKK PAYA KELADI', 'Pinang Tunggal'],
            ['MPKK PERMATANG LANGSAT', 'Pinang Tunggal'],
            ['MPKK JALAN KEDAH', 'Bertam'],
            ['MPKK KAMPUNG DATUK', 'Bertam'],
            ['MPKK PADANG BENGGALI', 'Bertam'],
            ['MPKK PERMATANG SINTOK', 'Bertam'],
            ['MPKK PERMATANG RAMBAI', 'Bertam'],
            ['MPKK KUALA MUDA', 'Penaga'],
            ['MPKK PERMATANG 3 RINGGIT', 'Penaga'],
            ['MPKK TIDAK DIKENALI', MpkkDun::UNASSIGNED],
        ];
    }
}
