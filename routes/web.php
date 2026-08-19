<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/generate-csv', function () {
    $file = fopen(storage_path('app/import_1000.csv'), 'w');
    fputcsv($file, ['id', 'producer', 'name', 'quantity', 'price']);
    
    $producers = ['TechCorp', 'AudioPlus', 'HomeGoods', 'OfficeStar', 'FitGear', 'GamerZ', 'KitchenPro'];
    $products = ['Wireless Mouse', 'Bluetooth Headphones', 'Ceramic Coffee Mug', 'Ergonomic Chair', 'Mechanical Keyboard', 'Yoga Mat', 'RGB Mousepad', 'Noise Cancelling Earbuds', 'Stainless Steel Spatula', 'USB-C Hub'];
    
    $expectedFails = 0;
    
    for ($i = 1; $i <= 1000; $i++) {
        // 10% chance to generate a corrupted row
        if (rand(1, 10) === 1) {
            $expectedFails++;
            $type = rand(1, 4);
            $row = [
                $i, 
                $producers[array_rand($producers)], 
                $products[array_rand($products)], 
                rand(10, 500), 
                rand(9, 199) + 0.99
            ];
            
            // Introduce a specific error
            if ($type === 1) {
                $row[0] = ''; // Missing ID (Fails required)
            } elseif ($type === 2) {
                $row[1] = ''; // Missing Producer (Fails required)
            } elseif ($type === 3) {
                $row[3] = 'Fifty'; // String instead of Integer (Fails integer)
            } elseif ($type === 4) {
                $row[4] = 'FREE'; // String instead of Numeric (Fails numeric)
            }
            
            fputcsv($file, $row);
        } else {
            // Clean Row
            fputcsv($file, [
                $i,
                $producers[array_rand($producers)],
                $products[array_rand($products)],
                rand(10, 500),
                rand(9, 199) + 0.99
            ]);
        }
    }
    
    fclose($file);
    return "Success! File generated. EXPECTED FAILURES: {$expectedFails}";
});