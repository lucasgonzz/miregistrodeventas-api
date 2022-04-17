<?php

namespace App\Imports;

use App\Article;
use App\Http\Controllers\Helpers\ArticleHelper;
use App\Http\Controllers\Helpers\UserHelper;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ArticlesImport implements ToCollection, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if ($row['nombre'] != '' && $row['precio'] != '') {
                if ($row['codigo_de_barras'] == '') {
                    $article = Article::where('user_id', UserHelper::userId())
                                        ->whereNull('bar_code')
                                        ->where('name', $row['nombre'])
                                        ->where('status', 'active')
                                        ->first();
                    if (!is_null($article)) {
                        $this->saveArticle($row);
                    }
                } else {
                    $article = Article::where('user_id', UserHelper::userId())
                                        ->where('bar_code', $row['codigo_de_barras'])
                                        ->where('status', 'active')
                                        ->first();
                    if (is_null($article)) {
                        $this->saveArticle($row);
                    }
                }
            }
        }
    }

    function saveArticle($row) {
        Article::create([
            'bar_code'  => $row['codigo_de_barras'],
            'name'      => $row['nombre'],
            'slug'      => ArticleHelper::slug($row['nombre']),
            'cost'      => $row['costo'],
            'price'     => $row['precio'],
            'stock'     => $row['stock'],
            'user_id'   => UserHelper::userId(),
        ]);
    }
}
