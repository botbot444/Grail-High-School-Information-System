<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait GeneratesTemporaryPassword
{
    /**
     * Readable one-time password: no I, O or L, so it survives being read
     * down a phone or written on a slip of paper.
     */
    protected function temporaryPassword(): string
    {
        $password = Str::upper(Str::random(3)) . '-' . random_int(1000, 9999) . '-' . Str::upper(Str::random(3));

        return str_replace(['I', 'O', 'L'], ['X', 'Y', 'Z'], $password);
    }
}
