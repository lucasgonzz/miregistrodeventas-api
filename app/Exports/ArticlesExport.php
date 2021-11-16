<?php

namespace App\Exports;

use App\Article;
use App\Http\Controllers\Helpers\UserHelper;
use Maatwebsite\Excel\Concerns\FromCollection;

class ArticlesExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Article::where('user_id', UserHelper::userId())
                        ->get();
    }
}
