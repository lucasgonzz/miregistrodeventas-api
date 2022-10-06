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
use App\Provider;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

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
        $this->provider = null;
        $this->initProvider();
    }

    function initProvider() {
        if ($this->provider_id != 0) {
            $this->provider = Provider::find($this->provider_id);
        }
    }

    function checkRow($row) {
        return $row['nombre'] != '';
        return $row['nombre'] != '' && ($row['precio'] != '' || !is_null($this->percentage_for_prices) || $row['utilidad'] != '' || (!is_null($this->provider) && !is_null($this->provider->percentage_gain)));
    }

    public function collection(Collection $rows) {
        foreach ($rows as $row) {
            if ($this->checkRow($row)) {
                if ($row['codigo_de_barras'] != '') {
                    $article = Article::where('user_id', UserHelper::userId())
                                        ->where('bar_code', $row['codigo_de_barras'])
                                        ->where('status', 'active')
                                        ->first();
                    $this->saveArticle($row, $article);
                } else if ($row['codigo_de_proveedor'] != '') {
                    $article = Article::where('user_id', UserHelper::userId())
                                        ->where('provider_code', $row['codigo_de_proveedor'])
                                        ->where('status', 'active')
                                        ->first();
                    $this->saveArticle($row, $article);
                } else {
                    $article = Article::where('user_id', UserHelper::userId())
                                        ->whereNull('bar_code')
                                        ->whereNull('provider_code')
                                        ->where('name', $row['nombre'])
                                        ->where('status', 'active')
                                        ->first();
                    $this->saveArticle($row, $article);
                }
            } else {
                Log::info('No se importo');
            }
        }
        Log::info('Se termino de importar');
    }

    function saveArticle($row, $article) {
        $iva_id = ImportHelper::getIvaId($row);
        ImportHelper::saveProvider($row, $this->ct);
        $data = [
            'name'              => $row['nombre'],
            'bar_code'          => $row['codigo_de_barras'],
            'provider_code'     => $row['codigo_de_proveedor'],
            'stock'             => $row['stock_actual'],
            'stock_min'         => $row['stock_minimo'],
            'iva_id'            => $iva_id,
            'cost'              => $row['costo'],
            'cost_in_dollars'   => $this->getCostInDollars($row),
            'percentage_gain'   => $row['margen_de_ganancia'],
            'price'             => $row['precio'],
        ];
        if (!is_null($article)) {
            Log::info('actulizando '.$article->name);
            $data['slug'] = ArticleHelper::slug($row['nombre'], $article->id);
            $article->update($data);
        } else {
            if (isset($row['codigo']) && $row['codigo'] != '') {
                $data['num'] = $row['codigo'];
            } else {
                $data['num'] = $this->ct->num('articles');
            }
            $data['slug'] = ArticleHelper::slug($row['nombre']);
            $data['user_id'] = UserHelper::userId();
            $article = Article::create($data);
            Log::info('se creo '.$article->name);
        }

        $this->setDiscounts($row, $article);

        $this->setProvider($row, $article);
        // Log::info('Se guardo '.$article->name);
    }

    function getCostInDollars($row) {
        if (isset($row['moneda']) && $row['moneda'] == 'USD') {
            return 1;
        }
        return 0;
    }

    function setDiscounts($row, $article) {
        if ($row['descuentos'] != '') {
            $_discounts = explode('-', $row['descuentos']);
            $discounts = [];
            foreach ($_discounts as $_discount) {
                $discount = new \stdClass;
                $discount->percentage = $_discount;
                $discounts[] = $discount;
            } 
            ArticleHelper::setDiscounts($article, $discounts);
        }
    }

    function setProvider($row, $article) {
        if ($row['proveedor'] != 'Sin especificar' && $row['proveedor'] != '') {
            $article->providers()->attach($this->ct->getModelBy('providers', 'name', $row['proveedor'], true, 'id'), [
                                            'amount' => $row['stock_actual'],
                                            'cost'   => $row['costo'],
                                            'price'  => $row['precio'],
                                        ]);
        }
        // if ($this->provider_id != 0) {
        //     $article->providers()->attach($this->provider_id, [
        //                                     'amount' => $row['stock_actual'],
        //                                     'cost' => $row['costo'],
        //                                     'price' => $row['price'],
        //                                 ]);
        // }
    }

    function getPrice($row) {
        if (!is_null($this->percentage_for_prices)) {
            return $row['costo'] + ($row['costo'] * $this->percentage_for_prices / 100);
        } else {
            return $row['precio'];
        }
    }
}
