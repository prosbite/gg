<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\TagType;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    private array $categories = [
        [
            'name' => 'Filipiniana Gown',
            'prefix' => 'FLP',
            'tag_types' => [
                'Color', 'Size', 'Fabric', 'Silhouette', 'Neckline', 'Sleeve Type',
                'Length', 'Occasion', 'Design Theme', 'Embellishment', 'Back Style',
                'Garment Type', 'Pattern', 'Gender / Age Group', 'Fit', 'Condition',
                'Closure Type', 'Season',
            ],
        ],
        [
            'name' => 'Bridal Gown',
            'prefix' => 'BRD',
            'tag_types' => [
                'Color', 'Size', 'Fabric', 'Silhouette', 'Neckline', 'Sleeve Type',
                'Length', 'Occasion', 'Design Theme', 'Embellishment', 'Back Style',
                'Garment Type', 'Pattern', 'Gender / Age Group', 'Fit', 'Condition',
                'Closure Type', 'Season',
            ],
        ],
        [
            'name' => 'Evening Gown',
            'prefix' => 'EVN',
            'tag_types' => [
                'Color', 'Size', 'Fabric', 'Silhouette', 'Neckline', 'Sleeve Type',
                'Length', 'Occasion', 'Design Theme', 'Embellishment', 'Back Style',
                'Garment Type', 'Pattern', 'Gender / Age Group', 'Fit', 'Condition',
                'Closure Type', 'Season',
            ],
        ],
        [
            'name' => 'Debut Gown',
            'prefix' => 'DBT',
            'tag_types' => [
                'Color', 'Size', 'Fabric', 'Silhouette', 'Neckline', 'Sleeve Type',
                'Length', 'Occasion', 'Design Theme', 'Embellishment', 'Back Style',
                'Garment Type', 'Pattern', 'Gender / Age Group', 'Fit', 'Condition',
                'Closure Type', 'Season',
            ],
        ],
        [
            'name' => 'Cocktail Dress',
            'prefix' => 'CKT',
            'tag_types' => [
                'Color', 'Size', 'Fabric', 'Silhouette', 'Neckline', 'Sleeve Type',
                'Length', 'Occasion', 'Design Theme', 'Embellishment', 'Back Style',
                'Pattern', 'Gender / Age Group', 'Fit', 'Condition', 'Closure Type',
                'Season',
            ],
        ],
        [
            'name' => 'Prom / Pageant Gown',
            'prefix' => 'PPG',
            'tag_types' => [
                'Color', 'Size', 'Fabric', 'Silhouette', 'Neckline', 'Sleeve Type',
                'Length', 'Occasion', 'Design Theme', 'Embellishment', 'Back Style',
                'Garment Type', 'Pattern', 'Gender / Age Group', 'Fit', 'Condition',
                'Closure Type', 'Season',
            ],
        ],
        [
            'name' => 'Barong Tagalog',
            'prefix' => 'BRN',
            'tag_types' => [
                'Color', 'Size', 'Fabric', 'Pattern', 'Gender / Age Group', 'Fit',
                'Condition', 'Occasion', 'Design Theme', 'Embellishment', 'Season',
            ],
        ],
        [
            'name' => 'Suit & Tuxedo',
            'prefix' => 'SUT',
            'tag_types' => [
                'Color', 'Size', 'Fabric', 'Fit', 'Pattern', 'Gender / Age Group',
                'Condition', 'Occasion', 'Season', 'Closure Type',
            ],
        ],
        [
            'name' => "Men's Filipiniana Set",
            'prefix' => 'MFS',
            'tag_types' => [
                'Color', 'Size', 'Fabric', 'Pattern', 'Gender / Age Group', 'Fit',
                'Condition', 'Occasion', 'Design Theme', 'Season',
            ],
        ],
        [
            'name' => 'Veil & Headpiece',
            'prefix' => 'VEL',
            'tag_types' => [
                'Color', 'Length', 'Fabric', 'Embellishment', 'Design Theme',
                'Occasion', 'Condition', 'Pattern', 'Gender / Age Group', 'Season',
            ],
        ],
        [
            'name' => 'Shawl, Bolero & Cape',
            'prefix' => 'SBC',
            'tag_types' => [
                'Color', 'Size', 'Fabric', 'Length', 'Occasion', 'Design Theme',
                'Embellishment', 'Condition', 'Gender / Age Group', 'Season',
            ],
        ],
        [
            'name' => 'Jewelry & Accessories',
            'prefix' => 'JWL',
            'tag_types' => [
                'Color', 'Design Theme', 'Embellishment', 'Occasion', 'Condition',
                'Gender / Age Group', 'Season',
            ],
        ],
        [
            'name' => 'Costume',
            'prefix' => 'CST',
            'tag_types' => [
                'Color', 'Size', 'Fabric', 'Occasion', 'Design Theme',
                'Gender / Age Group', 'Condition', 'Season', 'Pattern', 'Length',
            ],
        ],
        [
            'name' => 'Flower Girl & Pageant',
            'prefix' => 'FLG',
            'tag_types' => [
                'Color', 'Size', 'Fabric', 'Silhouette', 'Neckline', 'Sleeve Type',
                'Length', 'Occasion', 'Design Theme', 'Embellishment', 'Back Style',
                'Gender / Age Group', 'Condition', 'Season', 'Pattern',
            ],
        ],
        [
            'name' => "Boys' Barong & Suit",
            'prefix' => 'BYB',
            'tag_types' => [
                'Color', 'Size', 'Fabric', 'Fit', 'Pattern', 'Gender / Age Group',
                'Condition', 'Occasion', 'Design Theme', 'Season',
            ],
        ],
    ];

    public function run(): void
    {
        foreach ($this->categories as $categoryData) {
            $tagTypeNames = $categoryData['tag_types'];
            unset($categoryData['tag_types']);

            $category = Category::firstOrCreate($categoryData);

            $tagTypeIds = TagType::whereIn('name', $tagTypeNames)->pluck('id');
            $category->tagTypes()->sync($tagTypeIds);
        }
    }
}
