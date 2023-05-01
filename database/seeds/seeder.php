<?php

namespace app\database\seeds;

use app\models\Brand;
use app\models\Product;
use Illuminate\Database\Capsule\Manager as Capsule;

class Seeder
{
    /**
     * Run the seeds.
     *
     * @return void
     */
    public function up() {
        $rows   = array_map('str_getcsv', file(__DIR__ .'/products.csv'));
        //Get the first row that is the HEADER row.
        $header_row = array_shift($rows);
        //This array holds the final response.
        $products = array();
        foreach($rows as $row) {
            if(!empty($row)){
                $row = array_combine($header_row, $row);

                $brand = Brand::firstOrCreate([
                    'name' => $row['brand']
                ]);

                $product = new Product;
                $product->name = $row['name'];
                $product->brand_id = $brand->id;
                $product->barcode = $row['barcode'];
                $product->price = $row['price'];
                $product->image_url = $row['image_url'];
                $product->date_added = $row['date_added'];
                $product->save();
            }
        }
    }

}