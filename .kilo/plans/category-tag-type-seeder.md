# Plan: Categories & Tag Type Seeder for Filipino Gown Rental

## Goal
Create a `CategorySeeder` that seeds the `categories` table with Filipino gown rental business categories and populates the `category_tag_type` pivot table with appropriate tag type associations.

## Schema Context
- **categories**: `id`, `name`, `prefix`, `default_rental_fee`, `default_security_deposit`, `timestamps`
- **category_tag_type** (pivot): `category_id`, `tag_type_id` (unique pair), `timestamps`
- **tag_types** has 18 types from `TagSeeder`: Color, Size, Fabric, Silhouette, Neckline, Sleeve Type, Length, Occasion, Design Theme, Embellishment, Back Style, Garment Type, Pattern, Gender / Age Group, Fit, Condition, Closure Type, Season

## Proposed Categories (15 total)

### Women's Wear (Gowns)

| # | Name | Prefix | Applicable Tag Types |
|---|------|--------|---------------------|
| 1 | Filipiniana Gown | FLP | Color, Size, Fabric, Silhouette, Neckline, Sleeve Type, Length, Occasion, Design Theme, Embellishment, Back Style, Garment Type, Pattern, Gender/Age Group, Fit, Condition, Closure Type, Season |
| 2 | Bridal Gown | BRD | Color, Size, Fabric, Silhouette, Neckline, Sleeve Type, Length, Occasion, Design Theme, Embellishment, Back Style, Garment Type, Pattern, Gender/Age Group, Fit, Condition, Closure Type, Season |
| 3 | Evening Gown | EVN | Color, Size, Fabric, Silhouette, Neckline, Sleeve Type, Length, Occasion, Design Theme, Embellishment, Back Style, Garment Type, Pattern, Gender/Age Group, Fit, Condition, Closure Type, Season |
| 4 | Debut Gown | DBT | Color, Size, Fabric, Silhouette, Neckline, Sleeve Type, Length, Occasion, Design Theme, Embellishment, Back Style, Garment Type, Pattern, Gender/Age Group, Fit, Condition, Closure Type, Season |
| 5 | Cocktail Dress | CKT | Color, Size, Fabric, Silhouette, Neckline, Sleeve Type, Length, Occasion, Design Theme, Embellishment, Back Style, Pattern, Gender/Age Group, Fit, Condition, Closure Type, Season |
| 6 | Prom / Pageant Gown | PPG | Color, Size, Fabric, Silhouette, Neckline, Sleeve Type, Length, Occasion, Design Theme, Embellishment, Back Style, Garment Type, Pattern, Gender/Age Group, Fit, Condition, Closure Type, Season |

### Men's Wear

| # | Name | Prefix | Applicable Tag Types |
|---|------|--------|---------------------|
| 7 | Barong Tagalog | BRN | Color, Size, Fabric, Pattern, Gender/Age Group, Fit, Condition, Occasion, Design Theme, Embellishment, Season |
| 8 | Suit & Tuxedo | SUT | Color, Size, Fabric, Fit, Pattern, Gender/Age Group, Condition, Occasion, Season, Closure Type |
| 9 | Men's Filipiniana Set | MFS | Color, Size, Fabric, Pattern, Gender/Age Group, Fit, Condition, Occasion, Design Theme, Season |

### Accessories

| # | Name | Prefix | Applicable Tag Types |
|---|------|--------|---------------------|
| 10 | Veil & Headpiece | VEL | Color, Length, Fabric, Embellishment, Design Theme, Occasion, Condition, Pattern, Gender/Age Group, Season |
| 11 | Shawl, Bolero & Cape | SBC | Color, Size, Fabric, Length, Occasion, Design Theme, Embellishment, Condition, Gender/Age Group, Season |
| 12 | Jewelry & Accessories | ACC | Color, Design Theme, Embellishment, Occasion, Condition, Gender/Age Group, Season |

### Costumes & Specialty

| # | Name | Prefix | Applicable Tag Types |
|---|------|--------|---------------------|
| 13 | Costume | CST | Color, Size, Fabric, Occasion, Design Theme, Gender/Age Group, Condition, Season, Pattern, Length |

### Kids' Wear

| # | Name | Prefix | Applicable Tag Types |
|---|------|--------|---------------------|
| 14 | Flower Girl & Pageant | FLG | Color, Size, Fabric, Silhouette, Neckline, Sleeve Type, Length, Occasion, Design Theme, Embellishment, Back Style, Gender/Age Group, Condition, Season, Pattern |
| 15 | Boys' Barong & Suit | BYB | Color, Size, Fabric, Fit, Pattern, Gender/Age Group, Condition, Occasion, Design Theme, Season |

## Tag Type Assignment Rationale

- **Gown categories (1-6)**: All 18 tag types apply — gowns have the richest filtering needs (silhouette, neckline, sleeve, back style, etc.)
- **Men's categories (7-9)**: Exclude Silhouette, Neckline, Sleeve Type, Back Style, Garment Type (not applicable to men's wear)
- **Accessories (10-12)**: Exclude Silhouette, Neckline, Sleeve Type, Back Style, Garment Type, Fit, Closure Type; keep only what's relevant (color, fabric, embellishment, occasion, etc.)
- **Costume (13)**: Broad but excludes fine garment details like Silhouette, Neckline, etc.
- **Kids' Wear (14-15)**: Similar to adult equivalents but children-specific

## Implementation Steps

### 1. Create `CategorySeeder.php`
- Define a private array mapping category names/prefixes to their tag type names
- In `run()`: loop through, `firstOrCreate` each category, then sync tag types via the `category_tag_type` pivot

### 2. Register in `DatabaseSeeder.php`
- Add `CategorySeeder::class` to the `$this->call()` array, **after** `TagSeeder::class` (since tag types must exist first)

### 3. Files to create/modify
- **Create**: `database/seeders/CategorySeeder.php`
- **Edit**: `database/seeders/DatabaseSeeder.php` — add `CategorySeeder::class` after `TagSeeder::class`

### 4. Existing Issue to Note (out of scope, but important)
- `Category` model uses pivot table `category_tag_type` (correct - matches the newer migration)
- `CategoryTagType` model uses table `category_tagtypes` (old migration `2026_03_20_062550`)
- This mismatch should be resolved separately — the seeder will use the Category model's `tagTypes()` relationship which points to `category_tag_type`
