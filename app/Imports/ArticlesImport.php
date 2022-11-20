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

class ArticlesImport implements ToCollection
{
    
    public function __construct($props, $percentage_for_prices, $provider_id) {
        $this->columns = $props;
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
        Log::info($row);
        if (!is_null(ImportHelper::getColumnValue($row, 'nombre', $this->columns))) {
            Log::info('Tiene algo en el nombre');
            if ($this->isFirstRow($row)) {
                Log::info('Es la columna del titulo');
                return false;
            } else {
                Log::info('NO es la columna del titulo');
                return true;
            }
        } else {
            Log::info('El nombre esta vacio');
            return false;
        }
    }

    public function collection(Collection $rows) {
        foreach ($rows as $row) {
            if ($this->checkRow($row)) {
                if (!is_null(ImportHelper::getColumnValue($row, 'codigo', $this->columns))) {
                    $article = Article::where('user_id', UserHelper::userId())
                                        ->where('num', ImportHelper::getColumnValue($row, 'codigo', $this->columns))
                                        ->where('status', 'active')
                                        ->first();
                    $this->saveArticle($row, $article);
                } else if (!is_null(ImportHelper::getColumnValue($row, 'codigo_de_barras', $this->columns))) {
                    $article = Article::where('user_id', UserHelper::userId())
                                        ->where('bar_code', ImportHelper::getColumnValue($row, 'codigo_de_barras', $this->columns))
                                        ->where('status', 'active')
                                        ->first();
                    $this->saveArticle($row, $article);
                } else if (!is_null(ImportHelper::getColumnValue($row, 'codigo_de_proveedor', $this->columns))) {
                    $article = Article::where('user_id', UserHelper::userId())
                                        ->where('provider_code', ImportHelper::getColumnValue($row, 'codigo_de_proveedor', $this->columns))
                                        ->where('status', 'active')
                                        ->first();
                    $this->saveArticle($row, $article);
                } else {
                    $article = Article::where('user_id', UserHelper::userId())
                                        ->whereNull('bar_code')
                                        ->whereNull('provider_code')
                                        ->where('name', ImportHelper::getColumnValue($row, 'nombre', $this->columns))
                                        ->where('status', 'active')
                                        ->first();
                    $this->saveArticle($row, $article);
                }
            } 
        }
    }

    function saveArticle($row, $article) {
        $iva_id = ImportHelper::getIvaId(ImportHelper::getColumnValue($row, 'iva', $this->columns));
        ImportHelper::saveProvider(ImportHelper::getColumnValue($row, 'proveedor', $this->columns), $this->ct);
        $data = [
            'name'              => ImportHelper::getColumnValue($row, 'nombre', $this->columns),
            'bar_code'          => ImportHelper::getColumnValue($row, 'codigo_de_barras', $this->columns),
            'provider_code'     => ImportHelper::getColumnValue($row, 'codigo_de_proveedor', $this->columns),
            'stock'             => ImportHelper::getColumnValue($row, 'stock_actual', $this->columns),
            'stock_min'         => ImportHelper::getColumnValue($row, 'stock_minimo', $this->columns),
            'iva_id'            => $iva_id,
            'cost'              => ImportHelper::getColumnValue($row, 'costo', $this->columns),
            'cost_in_dollars'   => $this->getCostInDollars($row),
            'percentage_gain'   => ImportHelper::getColumnValue($row, 'margen_de_ganancia', $this->columns),
            'price'             => ImportHelper::getColumnValue($row, 'precio', $this->columns),
            'sub_category_id'   => ImportHelper::getSubcategoryId(ImportHelper::getColumnValue($row, 'categoria', $this->columns), ImportHelper::getColumnValue($row, 'sub_categoria', $this->columns)),
        ];
        if (!is_null($article)) {
            $data['slug'] = ArticleHelper::slug(ImportHelper::getColumnValue($row, 'nombre', $this->columns), $article->id);
            $article->update($data);
            Log::info('se actualizo '.$article->name);
        } else {
            if (!is_null(ImportHelper::getColumnValue($row, 'codigo', $this->columns))) {
                $data['num'] = ImportHelper::getColumnValue($row, 'codigo', $this->columns);
            } else {
                $data['num'] = $this->ct->num('articles');
            }
            $data['slug'] = ArticleHelper::slug(ImportHelper::getColumnValue($row, 'nombre', $this->columns));
            $data['user_id'] = UserHelper::userId();
            $article = Article::create($data);
            Log::info('se creo '.$article->name);
        }

        $this->setDiscounts($row, $article);

        $this->setProvider($row, $article);
        // Log::info('Se guardo '.$article->name);
    }

    function isFirstRow($row) {
        return ImportHelper::getColumnValue($row, 'nombre', $this->columns) == 'Nombre';
    }

    function getCostInDollars($row) {
        if (ImportHelper::getColumnValue($row, 'moneda', $this->columns) == 'USD') {
            return 1;
        }
        return 0;
    }

    function setDiscounts($row, $article) {
        if (!is_null(ImportHelper::getColumnValue($row, 'descuentos', $this->columns))) {
            $_discounts = explode('-', ImportHelper::getColumnValue($row, 'descuentos', $this->columns));
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
        if (!is_null(ImportHelper::getColumnValue($row, 'proveedor', $this->columns))) {
            $article->providers()->attach($this->ct->getModelBy('providers', 'name', ImportHelper::getColumnValue($row, 'proveedor', $this->columns), true, 'id'), [
                                            'amount' => ImportHelper::getColumnValue($row, 'stock_actual', $this->columns),
                                            'cost'   => ImportHelper::getColumnValue($row, 'costo', $this->columns),
                                            'price'  => ImportHelper::getColumnValue($row, 'precio', $this->columns),
                                        ]);
        }
        // if ($this->provider_id != 0) {
        //     $article->providers()->attach($this->provider_id, [
        //                                     'amount' => ImportHelper::getColumnValue($row, 'stock_actual', $this->columns),
        //                                     'cost' => ImportHelper::getColumnValue($row, 'costo', $this->columns),
        //                                     'price' => ImportHelper::getColumnValue($row, 'price', $this->columns),
        //                                 ]);
        // }
    }

    function getPrice($row) {
        if (!is_null($this->percentage_for_prices)) {
            return ImportHelper::getColumnValue($row, 'costo', $this->columns) + (ImportHelper::getColumnValue($row, 'costo', $this->columns) * $this->percentage_for_prices / 100);
        } else {
            return ImportHelper::getColumnValue($row, 'precio', $this->columns);
        }
    }
}
