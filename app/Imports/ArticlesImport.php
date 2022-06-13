<?php

namespace App\Imports;

use App\Article;
use App\Http\Controllers\Helpers\ArticleHelper;
use App\Http\Controllers\Helpers\UserHelper;
use App\Http\Controllers\update;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ArticlesImport implements ToCollection, WithHeadingRow
{
    
    public function __construct($percentage_for_prices, $provider_id) {
        if ($percentage_for_prices != '') {
            $this->percentage_for_prices = $percentage_for_prices;
        } else {
            $this->percentage_for_prices = null;
        }
        $this->provider_id = $provider_id;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if ($row['nombre'] != '' && ($row['precio'] != '' || !is_null($this->percentage_for_prices))) {
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
                    if (!is_null($article)) {
                        if (count($article->sales) >= 1) {
                            $article->update([
                                'status' => 'inactive',
                            ]);
                        } else {
                            $article->delete();
                        }
                    }
                    $this->saveArticle($row);
                }
            }
        }
    }

    function saveArticle($row) {
        $article = Article::create([
            'bar_code'  => $row['codigo_de_barras'],
            'name'      => $row['nombre'],
            'slug'      => ArticleHelper::slug($row['nombre']),
            'cost'      => $row['costo'],
            'price'     => $this->getPrice($row),
            'stock'     => $row['stock'],
            'user_id'   => UserHelper::userId(),
        ]);
        if ($this->provider_id != 0) {
            $article->providers()->attach($this->provider_id, [
                                            'amount' => null,
                                            'cost' => $row['costo'],
                                            'price' => $this->getPrice($row)
                                        ]);
        }
    }

    function getPrice($row) {
        if (!is_null($this->percentage_for_prices)) {
            return $row['costo'] + ($row['costo'] * $this->percentage_for_prices / 100);
        } else {
            return $row['precio'];
        }
    }
}
