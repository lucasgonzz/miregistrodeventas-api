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
    
    public function __construct($columns, $start_row, $finish_row, $provider_id) {
        $this->columns = $columns;
        $this->start_row = $start_row;
        $this->finish_row = $finish_row;
        $this->ct = new Controller();
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
        return !is_null(ImportHelper::getColumnValue($row, 'nombre', $this->columns));
    }

    public function collection(Collection $rows) {
        $num_row = 1;
        if (is_null($this->finish_row) || $this->finish_row == '') {
            $this->finish_row = count($rows);
        } 
        foreach ($rows as $row) {
            if ($num_row >= $this->start_row && $num_row <= $this->finish_row) {
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
            } else if ($num_row > $this->finish_row) {
                break;
            }
            $num_row++;
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
        if (!is_null($article) && $this->isDataUpdated($article, $data)) {
            $data['slug'] = ArticleHelper::slug(ImportHelper::getColumnValue($row, 'nombre', $this->columns), $article->id);
            $article->update($data);
        } else if (is_null($article)) {
            if (!is_null(ImportHelper::getColumnValue($row, 'codigo', $this->columns))) {
                $data['num'] = ImportHelper::getColumnValue($row, 'codigo', $this->columns);
            } else {
                $data['num'] = $this->ct->num('articles');
            }
            $data['slug'] = ArticleHelper::slug(ImportHelper::getColumnValue($row, 'nombre', $this->columns));
            $data['user_id'] = UserHelper::userId();
            $article = Article::create($data);
        } 
        $this->setDiscounts($row, $article);
        $this->setProvider($row, $article);
        ArticleHelper::setFinalPrice($article);
    }

    function isDataUpdated($article, $data) {
        return  $article->name            != $data['name'] ||
                $article->bar_code        != $data['bar_code'] ||
                $article->provider_code   != $data['provider_code'] ||
                $article->stock           != $data['stock'] ||
                $article->stock_min       != $data['stock_min'] ||
                $article->iva_id          != $data['iva_id'] ||
                $article->cost            != $data['cost'] ||
                $article->cost_in_dollars != $data['cost_in_dollars'] ||
                $article->percentage_gain != $data['percentage_gain'] ||
                $article->price           != $data['price'];
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
            $provider_id = $this->ct->getModelBy('providers', 'name', ImportHelper::getColumnValue($row, 'proveedor', $this->columns), true, 'id');
            $article->provider_id = $provider_id;
            $article->save();
            $article->providers()->attach($provider_id, [
                                            'amount' => ImportHelper::getColumnValue($row, 'stock_actual', $this->columns),
                                            'cost'   => ImportHelper::getColumnValue($row, 'costo', $this->columns),
                                            'price'  => ImportHelper::getColumnValue($row, 'precio', $this->columns),
                                        ]);
        }
    }
}
