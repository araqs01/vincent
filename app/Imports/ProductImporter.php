<?php

namespace App\Imports;

use App\Models\{
    Product,
    Category,
    Region,
    Supplier,
    Attribute,
    AttributeValue,
    Brand
};
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use function Laravel\Prompts\clear;

class ProductImporter implements ToCollection, WithHeadingRow, WithCalculatedFormulas, WithChunkReading
{
  
}
