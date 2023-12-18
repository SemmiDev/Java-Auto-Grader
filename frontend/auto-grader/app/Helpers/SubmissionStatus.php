<?php

namespace App\Helpers;

class SubmissionStatus
{
    public static function toID(string $enStatus) : string
    {
        $statusMap = [
            "BEING GRADED" => "Sedang Dinilai",
            "SUCCESSFULLY GRADED" => "Berhasil Dinilai",
            "FAILED TO GRADE" => "Gagal Dinilai",
        ];

        return $statusMap[$enStatus];
    }
}
