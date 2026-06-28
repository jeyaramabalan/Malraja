<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;

class PurchaseDeletionExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $logData;

    public function __construct(array $logData)
    {
        $this->logData = $logData;
    }

    public function collection()
    {
        return new Collection($this->logData);
    }

    public function headings(): array
    {
        return [
            'Record Type',
            'Bill Number',
            'Product Name',
            'Quantity Change',
            'Reason',
            'Product Stock Before',
            'Product Stock After',
            'Bill Total Before',
            'Bill Total After',
        ];
    }
}