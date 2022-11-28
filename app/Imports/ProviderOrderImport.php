<?php

namespace App\Imports;

use App\Article;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Helpers\ImportHelper;
use App\Http\Controllers\Helpers\ProviderOrderHelper;
use App\Http\Controllers\Helpers\UserHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;

class ProviderOrderImport implements ToCollection
{
    
    public function __construct($columns, $first_row, $provider_order) {
        $this->columns = $columns;
        $this->first_row = $first_row;
        $this->provider_order = $provider_order;
        $this->provider_order->articles()->detach();
        $this->ct = new Controller();
    }

    public function collection(Collection $rows) {
        $articles = [];
        $index = 1;
        foreach ($rows as $row) {
            if ($index >= $this->first_row) {
                if ($this->checkRow($row)) {
                    if (!is_null(ImportHelper::getColumnValue($row, 'codigo_de_barras', $this->columns))) {
                        $article = Article::where('user_id', UserHelper::userId())
                                            ->where('bar_code', ImportHelper::getColumnValue($row, 'codigo_de_barras', $this->columns))
                                            ->where('status', 'active')
                                            ->first();
                        $articles[] = $this->saveArticle($row, $article);
                    } else if (!is_null(ImportHelper::getColumnValue($row, 'codigo_de_proveedor', $this->columns))) {
                        $article = Article::where('user_id', UserHelper::userId())
                                            ->where('provider_code', ImportHelper::getColumnValue($row, 'codigo_de_proveedor', $this->columns))
                                            ->where('status', 'active')
                                            ->first();
                        $articles[] = $this->saveArticle($row, $article);
                    } else {
                        $article = Article::where('user_id', UserHelper::userId())
                                            // ->whereNull('bar_code')
                                            // ->whereNull('provider_code')
                                            ->where('name', ImportHelper::getColumnValue($row, 'nombre', $this->columns))
                                            ->where('status', 'active')
                                            ->first();
                        $articles[] = $this->saveArticle($row, $article);
                    }
                }
            }
            $index++;
        }
        ProviderOrderHelper::attachArticles($articles, $this->provider_order);
    }

    function saveArticle($row, $article) {
        if (is_null($article)) {
            $data = [
                'bar_code'          => ImportHelper::getColumnValue($row, 'codigo_de_barras', $this->columns),
                'provider_code'     => ImportHelper::getColumnValue($row, 'codigo_de_proveedor', $this->columns),
                'name'              => ImportHelper::getColumnValue($row, 'nombre', $this->columns),
                'user_id'           => UserHelper::userId(),
                'status'            => 'inactive',
                'num'               => $this->ct->num('articles'),
            ];
            $article = Article::create($data);
        } 
        $recibidas = ImportHelper::getColumnValue($row, 'recibidas', $this->columns);
        if (is_null($recibidas)) {
            $recibidas = 0;
        }
        $saved_article = [
            'status'        => $article->status,
            'id'            => $article->id,
            'name'          => $article->name,
            'bar_code'      => $article->bar_code,
            'provider_code' => $article->provider_code,
            'pivot'         => [
                'amount'            => ImportHelper::getColumnValue($row, 'cantidad', $this->columns),
                'notes'             => ImportHelper::getColumnValue($row, 'notas', $this->columns),
                'received'          => $recibidas,
                'cost'              => ImportHelper::getColumnValue($row, 'costo', $this->columns),
                'received_cost'     => ImportHelper::getColumnValue($row, 'costo_recibido', $this->columns),
                'iva_id'            => ImportHelper::getIvaId(ImportHelper::getColumnValue($row, 'iva', $this->columns), $article),
            ],
        ];
        return $saved_article;
    }

    function isFirstRow($row) {
        return ImportHelper::getColumnValue($row, 'nombre', $this->columns) == 'Nombre';
    }

    function checkRow($row) {
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
}
