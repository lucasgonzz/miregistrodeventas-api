<?php

namespace App\Exports;

use App\Article;
use App\Http\Controllers\Helpers\ArticleHelper;
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
                        ->select('bar_code', 'provider_code', 'name', 'iva_id', 'cost', 'percentage_gain', 'price', 'stock', 'stock_min', 'created_at', 'updated_at')
                        ->orderBy('created_at', 'DESC')
                        ->with('discounts')
                        ->get();
        $articles = ArticleHelper::setPrices($articles);
        $articles = ArticleHelper::setIva($articles);
        // $articles = ArticleHelper::setDiscount($articles);
        return $articles;
    }

    public function headings(): array
    {
        return [
            'Codigo de barras',
            'Codigo de proveedor',
            'Nombre',
            'IVA',
            'Costo',
            'Utilidad',
            'Precio',
            'Stock actual',
            'Stock minimo',
            'Ingresado',
            'Actualizado',
        ];
    }

}
