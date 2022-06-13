<?php

namespace App\Http\Controllers;

use App\BudgetProductArticleStock;
use App\Http\Controllers\Helpers\ArticleHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BudgetProductArticleStockController extends Controller
{
    
    function store(Request $request) {
        $article_id = $request->article['id'];
        $amount = $request->amount;
        $budget_product_article_stock = BudgetProductArticleStock::create([
            'amount'            => $amount,
            'note'              => $request->note,
            'article_id'        => $article_id,
            'budget_product_id' => $request->product_id,
            'created_at'        => $request->current_date ? Carbon::now() : $request->created_at,
        ]);
        ArticleHelper::discountStock($article_id, $amount);
        $article = ArticleHelper::getFullArticle($article_id);
        return response()->json(['budget_product_article_stock' => $this->getFullModel($budget_product_article_stock->id), 'article' => $article], 201);
    }

    function delete($id) {
        $budget_product_article_stock = BudgetProductArticleStock::find($id);
        ArticleHelper::resetStock($budget_product_article_stock->article, $budget_product_article_stock->amount);
        $article = ArticleHelper::getFullArticle($budget_product_article_stock->article->id);
        $budget_product_article_stock->delete();
        return response()->json(['article' => $article], 200);
    }

    function getFullModel($id) {
        $budget_product_article_stock = BudgetProductArticleStock::where('id', $id)
                                                                ->with('article')
                                                                ->first();
        return $budget_product_article_stock;

    }

}
