<?php

namespace app\controllers;

use app\core\Controller;
use app\core\Request;
use app\models\Product;
use Illuminate\Pagination\Paginator;

class ProductController extends Controller
{
    public function index(Request $request) {
        $page = 1;
        $orderBy = 'name';
        $order = 'asc';
        $searchQuery = '';
        $body = $request->getBody();
        $searchQuery = (isset($body['search_query'])) ? $body['search_query'] : $searchQuery;
        $searchQuery = str_replace("$", "", $searchQuery);
        $page = (isset($body['page']) && intval($body['page'])) ? (int)$body['page'] : $page;
        $sortBy = (isset($body['sort_by']) &&
                    in_array($body['sort_by'], ['asc', 'desc'])) ? $body['sort_by'] : $order;
        $orderBy = (isset($body['order_by']) &&
                    in_array($body['order_by'], ['name', 'price'])) ? $body['order_by'] : $orderBy;

        Paginator::currentPageResolver(function () use ($page) {
            return $page;
        });

        $products = Product::where('name', 'LIKE', '%'.$searchQuery.'%')
            ->orWhere('price', 'LIKE', '%'.$searchQuery.'%')
            ->orderBy($orderBy, $sortBy)
            ->paginate(12);
        return $this->json([
            "products" => $products,
        ], 200);
    }
}