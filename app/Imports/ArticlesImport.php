<?php

namespace App\Imports;

use App\Article;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\ArticleHelper;
use App\Http\Controllers\Helpers\ImportHelper;
use App\Http\Controllers\Helpers\IvaHelper;
use App\Http\Controllers\Helpers\UserHelper;
use App\Http\Controllers\Helpers\getIva;
use App\Http\Controllers\update;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class ArticlesImport implements ToCollection, WithHeadingRow
{
    
    public function __construct($percentage_for_prices, $provider_id) {
        $this->ct = new Controller();
        if ($percentage_for_prices != '') {
            $this->percentage_for_prices = $percentage_for_prices;
        } else {
            $this->percentage_for_prices = null;
        }
        $this->provider_id = $provider_id;
    }

    public function collection(Collection $rows) {
        foreach ($rows as $row) {
            if ($row['nombre'] != '' && ($row['precio'] != '' || !is_null($this->percentage_for_prices) || $row['utilidad'] != '')) {
                if ($row['codigo_de_barras'] == '') {
                    $article = Article::where('user_id', UserHelper::userId())
                                        ->whereNull('bar_code')
                                        ->where('name', $row['nombre'])
                                        ->where('status', 'active')
                                        ->first();
                    $this->saveArticle($row, $article);
                } else {
                    $article = Article::where('user_id', UserHelper::userId())
                                        ->where('bar_code', $row['codigo_de_barras'])
                                        ->where('status', 'active')
                                        ->first();
                    $this->saveArticle($row, $article);
                }
            }
        }
    }

    function saveArticle($row, $article) {
        if (!is_null($article)) {
            if (count($article->sales) >= 1) {
                $article->update([
                    'status' => 'inactive',
                ]);
            } else {
                $article->delete();
            }
        }
        ImportHelper::saveProvider($row, $this->ct);
        Log::info('iva1: '.$row['iva']);
        $iva = ImportHelper::getIva($row, $this->ct);
        Log::info('iva id: '.$iva);
        $article = Article::create([
            'num'               => $this->ct->num('articles'),
            'name'              => $row['nombre'],
            'bar_code'          => $row['codigo_de_barras'],
            'provider_code'     => $row['codigo_de_proveedor'],
            'slug'              => ArticleHelper::slug($row['nombre']),
            'stock'             => $row['stock_actual'],
            'stock_min'         => $row['stock_minimo'],
            'iva_id'            => $iva,
            'cost'              => $row['costo'],
            'percentage_gain'   => $row['utilidad'],
            'price'             => $this->getPrice($row),
            'user_id'           => UserHelper::userId(),
        ]);
        if ($this->provider_id != 0) {
            $article->providers()->attach($this->provider_id, [
                                            'amount' => $row['stock_actual'],
                                            'cost' => $row['costo'],
                                            'price' => $this->getPrice($row)
                                        ]);
        }
        if ($row['proveedor'] != 'Sin especificar' && $row['proveedor'] != '') {
            $article->providers()->attach($this->ct->getModelBy('providers', 'name', $row['proveedor'], true, 'id'), [
                                            'amount' => $row['stock_actual'],
                                            'cost'   => $row['costo'],
                                            'price'  => $this->getPrice($row)
                                        ]);
        }
        Log::info('Se guardo '.$article->name);
    }

    function getPrice($row) {
        if (!is_null($this->percentage_for_prices)) {
            return $row['costo'] + ($row['costo'] * $this->percentage_for_prices / 100);
        } else {
            return $row['precio'];
        }
    }
}
