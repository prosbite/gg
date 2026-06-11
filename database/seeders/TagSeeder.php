<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\TagType;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    private array $types = [
        'Color' => [
            'Black', 'White', 'Ivory', 'Cream', 'Beige', 'Nude', 'Champagne',
            'Gold', 'Silver', 'Rose Gold',
            'Red', 'Maroon', 'Burgundy', 'Wine',
            'Pink', 'Blush Pink', 'Fuchsia', 'Magenta',
            'Lavender', 'Purple',
            'Royal Blue', 'Navy Blue',
            'Emerald Green', 'Sage Green', 'Mint Green', 'Teal', 'Turquoise',
            'Yellow', 'Mustard',
            'Orange', 'Coral', 'Peach',
            'Brown', 'Gray', 'Charcoal', 'Ombre',
        ],
        'Size' => [
            'XXS', 'XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', '4XL',
            'Free Size', 'Custom',
        ],
        'Fabric' => [
            'Satin', 'Silk', 'Chiffon', 'Organza', 'Lace', 'Tulle', 'Velvet',
            'Crepe', 'Georgette', 'Mikado', 'Taffeta',
            'Jusi', 'Piña', 'Jusilyn',
            'Brocade', 'Jersey',
            'Duchess Satin', 'Cotton', 'Polyester',
        ],
        'Silhouette' => [
            'Ball Gown', 'A-Line', 'Mermaid', 'Sheath', 'Empire',
            'Fit and Flare', 'Trumpet', 'Column', 'Drop Waist',
            'Princess Cut', 'Trapeze', 'High-Low',
        ],
        'Neckline' => [
            'V-Neck', 'Sweetheart', 'Scoop Neck', 'Halter',
            'Off-Shoulder', 'Boat Neck', 'Square Neck', 'High Neck',
            'Cowl Neck', 'Plunging', 'Illusion', 'Strapless', 'One-Shoulder',
        ],
        'Sleeve Type' => [
            'Sleeveless', 'Cap Sleeve', 'Short Sleeve', 'Three-Quarter Sleeve',
            'Long Sleeve', 'Butterfly Sleeve', 'Bell Sleeve', 'Puff Sleeve',
            'Bishop Sleeve', 'Kimono Sleeve', 'Flutter Sleeve', 'Cold Shoulder',
        ],
        'Length' => [
            'Mini', 'Above Knee', 'Knee Length', 'Tea Length',
            'Midi', 'Ankle Length', 'Floor Length', 'Cathedral Train',
        ],
        'Occasion' => [
            'Wedding', 'Debut', 'Prom', 'Evening', 'Cocktail', 'Formal',
            'Pageant', 'Photoshoot', 'Graduation', 'Christening',
            'Birthday', 'Gala', 'Costume Party',
        ],
        'Design Theme' => [
            'Modern Filipiniana', 'Traditional Filipiniana', 'Terno',
            "Baro't Saya", 'Maria Clara', 'Mestiza',
            'Modern', 'Bohemian', 'Classic', 'Romantic',
            'Glamorous', 'Fairytale', 'Gothic', 'Avant-Garde',
        ],
        'Embellishment' => [
            'Beaded', 'Sequined', 'Embroidered', 'Appliqued',
            'Ruffled', 'Pleated', 'Draped', 'Ruched', 'Feathered',
            'Pearls', 'Crystals', 'Rhinestones',
            'Lace Applique', 'Floral Applique', 'Bow', 'Belted',
        ],
        'Back Style' => [
            'Open Back', 'Lace-Up Back', 'Zipper Back', 'Button Back',
            'Corset Back', 'Keyhole Back', 'Low Back', 'Illusion Back',
        ],
        'Garment Type' => [
            'Gown', 'Dress', 'Jumpsuit', 'Pantsuit', 'Two-Piece Set',
            'Cocktail Dress', 'Maxi Dress', 'Skirt', 'Blouse', 'Corset',
            'Bolero', 'Shawl', 'Cape', 'Veil',
            'Barong Tagalog', 'Suit', 'Tuxedo', 'Robe',
        ],
        'Pattern' => [
            'Solid', 'Floral', 'Striped', 'Polka Dot', 'Geometric',
            'Abstract', 'Paisley', 'Animal Print', 'Printed',
        ],
        'Gender / Age Group' => [
            "Women's", "Men's", 'Unisex', "Girls'", "Boys'",
            'Toddler', 'Infant', 'Teen',
        ],
        'Fit' => [
            'Slim Fit', 'Regular Fit', 'Loose Fit', 'Oversized',
            'Tailored', 'Stretch',
        ],
        'Condition' => [
            'Brand New', 'Like New', 'Good', 'Fair', 'Vintage',
        ],
        'Closure Type' => [
            'Zipper', 'Buttons', 'Lace-Up', 'Hook and Eye',
            'Slip-On', 'Wrap', 'Corset Lacing',
        ],
        'Season' => [
            'Summer', 'Rainy', 'All-Season', 'Hot Weather',
            'Air-Conditioned Venue',
        ],
    ];

    public function run(): void
    {
        foreach ($this->types as $typeName => $tagNames) {
            $tagType = TagType::firstOrCreate(['name' => $typeName]);

            foreach ($tagNames as $tagName) {
                Tag::firstOrCreate([
                    'name' => $tagName,
                    'type' => $tagType->id,
                ]);
            }
        }
    }
}
