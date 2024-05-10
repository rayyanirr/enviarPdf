<?php

namespace App\Imports;

use App\Models\Pdf;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;

class PDFImport implements ToModel
{


    public function model(array $rows)
    {
        foreach ($rows as $row)
        {
            return new Pdf([
                'name_file' => $row[0],
                'email' => $row[1],
            ]);
        }
    }
}
