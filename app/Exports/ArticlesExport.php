<?php

namespace App\Exports;

use App\Article;
use App\Http\Controllers\Helpers\UserHelper;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ArticlesExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $articles = Article::where('user_id', UserHelper::userId())
                        ->select('bar_code', 'name', 'cost', 'price', 'stock', 'created_at', 'updated_at')
                        ->orderBy('created_at', 'DESC')
                        ->get();
        // foreach ($articles as $article) {
        //     $article->created_at = date_format($article->created_at, 'd/m/y');
        //     $article->updated_at = date_format($article->updated_at, 'd/m/y');
        // }
        return $articles;
    }

    public function headings(): array
    {
        return [
            'Codigo',
            'Nombre',
            'Costo',
            'Precio',
            'Stock',
            'Ingresado',
            'Actualizado',
        ];
    }

}
