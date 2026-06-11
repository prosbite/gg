# Database Schema Scratchpad

## Tables and Columns

### `users`
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint unsigned PK | auto-increment |
| name | varchar(255) | |
| email | varchar(255) | unique, index |
| role | varchar(255) | default 'user' |
| email_verified_at | timestamp | nullable |
| password | varchar(255) | |
| remember_token | varchar(100) | nullable |
| timestamps | | |

### `password_reset_tokens`
| Column | Type | Constraints |
|--------|------|-------------|
| email | varchar(255) | primary |
| token | varchar(255) | |
| created_at | timestamp | nullable |

### `sessions`
| Column | Type | Constraints |
|--------|------|-------------|
| id | varchar(255) | primary |
| user_id | bigint unsigned | nullable, index, FK → users(id) |
| ip_address | varchar(45) | nullable |
| user_agent | text | nullable |
| payload | longText | |
| last_activity | int | index |

### `cache`
| Column | Type | Constraints |
|--------|------|-------------|
| key | varchar(255) | primary |
| value | mediumText | |
| expiration | bigint | index |

### `cache_locks`
| Column | Type | Constraints |
|--------|------|-------------|
| key | varchar(255) | primary |
| owner | varchar(255) | |
| expiration | bigint | index |

### `jobs`
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint unsigned PK | auto-increment |
| queue | varchar(255) | index |
| payload | longText | |
| attempts | unsigned smallint | |
| reserved_at | unsigned int | nullable |
| available_at | unsigned int | |
| created_at | unsigned int | |

### `job_batches`
| Column | Type | Constraints |
|--------|------|-------------|
| id | varchar(255) | primary |
| name | varchar(255) | |
| total_jobs | int | |
| pending_jobs | int | |
| failed_jobs | int | |
| failed_job_ids | longText | |
| options | mediumText | nullable |
| cancelled_at | int | nullable |
| created_at | int | |
| finished_at | int | nullable |

### `failed_jobs`
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint unsigned PK | auto-increment |
| uuid | varchar(255) | unique |
| connection | varchar(255) | |
| queue | varchar(255) | |
| payload | longText | |
| exception | longText | |
| failed_at | timestamp | useCurrent |
| index | (connection, queue, failed_at) | |

### `categories`
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint unsigned PK | auto-increment |
| name | varchar(255) | |
| prefix | varchar(255) | e.g., "GW" |
| default_rental_fee | decimal(10,2) | default 0 |
| default_security_deposit | decimal(10,2) | default 0 |
| timestamps | | |

### `tag_types`
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint unsigned PK | auto-increment |
| name | varchar(255) | unique |
| timestamps | | |

### `products`
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint unsigned PK | auto-increment |
| item_code | varchar(255) | unique, index |
| name | varchar(255) | unique |
| category_id | bigint unsigned | FK → categories(id) |
| custom_rental_fee | decimal(10,2) | nullable |
| custom_security_deposit | decimal(10,2) | nullable |
| status | enum('available','rented','maintenance','retired') | default 'available', index |
| description | text | nullable |
| specifics | json | nullable |
| is_active | boolean | default true |
| timestamps | | |

### `product_images`
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint unsigned PK | auto-increment |
| product_id | bigint unsigned | FK → products(id) onDelete cascade |
| file_path | varchar(255) | |
| thumbnail_path | varchar(255) | nullable |
| is_primary | boolean | default false |
| sort_order | int | default 0 |
| label | varchar(255) | nullable |
| timestamps | | |

### `tags`
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint unsigned PK | auto-increment |
| name | varchar(255) | unique (name only) |
| slug | varchar(255) | |
| type | bigint unsigned | FK → tag_types(id) cascadeOnDelete |
| timestamps | | |

### `product_tag` (pivot)
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint unsigned PK | auto-increment |
| product_id | bigint unsigned | FK → products(id) onDelete cascade |
| tag_id | bigint unsigned | FK → tags(id) onDelete cascade |
| timestamps | | |
| *Unique* | (product_id, tag_id) | automatically enforced via FKs? Not explicit |

### `customers`
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint unsigned PK | auto-increment |
| first_name | varchar(255) | index |
| last_name | varchar(255) | index |
| contact_number | varchar(255) | nullable |
| email | varchar(255) | nullable, unique |
| address | varchar(255) | nullable |
| affiliation | varchar(255) | nullable |
| social_media_link | varchar(255) | nullable |
| identification | varchar(255) | nullable |
| detail_info | json | nullable |
| is_blacklisted | boolean | default false |
| admin_notes | text | nullable |
| timestamps | | |

### `rentals`
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint unsigned PK | auto-increment |
| customer_id | bigint unsigned | FK → customers(id) |
| status | enum('reserved','picked_up','returned','cancelled','completed') | default 'reserved' |
| pickup_date | date | |
| return_date | date | |
| actual_returned_at | timestamp | nullable |
| internal_notes | text | nullable |
| timestamps | | |

### `rental_items`
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint unsigned PK | auto-increment |
| rental_id | bigint unsigned | FK → rentals(id) onDelete cascade |
| product_id | bigint unsigned | FK → products(id) |
| rental_fee | decimal(10,2) | |
| security_deposit | decimal(10,2) | |
| status | enum('reserved','picked_up','returned') | default 'reserved' |
| timestamps | | |

### `payments`
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint unsigned PK | auto-increment |
| rental_id | bigint unsigned | FK → rentals(id) onDelete cascade |
| amount | decimal(10,2) | |
| type | enum('downpayment','balance','security_deposit','penalty') | |
| method | enum('cash','gcash','bank_transfer') | default 'cash' |
| reference_number | varchar(255) | nullable |
| notes | text | nullable |
| timestamps | | |

### `draft_rentals`
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint unsigned PK | auto-increment |
| receipt_number | varchar(255) | nullable, unique |
| customer_id | bigint unsigned | FK → customers(id) onDelete cascade |
| pickup_date | date | |
| return_date | date | |
| rental_items | json | nullable |
| payments | json | nullable |
| total_amount | decimal(10,2) | default 0 |
| notes | text | nullable |
| status | varchar(255) | default 'unprocessed' |
| rental_status | varchar(255) | default 'for_pickup' |
| timestamps | | |

### `draft_images`
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint unsigned PK | auto-increment |
| draft_rental_id | bigint unsigned | FK → draft_rentals(id) onDelete cascade |
| file_path | varchar(255) | |
| label | varchar(255) | nullable |
| timestamps | | |

### `rental_requests`
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint unsigned PK | auto-increment |
| first_name | varchar(255) | |
| last_name | varchar(255) | |
| contact_number | varchar(255) | nullable |
| fb_profile_link | varchar(255) | nullable |
| item_code_requested | varchar(255) | nullable |
| requested_pickup_date | date | nullable |
| images | json | nullable |
| notes | text | nullable |
| status | varchar(255) | default 'pending' |
| timestamps | | |

### `category_tagtypes` (pivot)
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint unsigned PK | auto-increment |
| category_id | bigint unsigned | FK → categories(id) cascadeOnDelete |
| tag_type_id | bigint unsigned | FK → tag_types(id) cascadeOnDelete |
| timestamps | | |
| *Unique* | (category_id, tag_type_id) | not explicitly defined (potential duplicate) |

### `category_tag_type` (pivot)
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint unsigned PK | auto-increment |
| category_id | bigint unsigned | FK → categories(id) cascadeOnDelete |
| tag_type_id | bigint unsigned | FK → tag_types(id) cascadeOnDelete |
| timestamps | | |
| *Unique* | (category_id, tag_type_id) | explicitly defined |

## Relationships Summary

- **Category** has many **Products**
- **Product** belongs to **Category**; has many **ProductImages**; belongs to many **Tags** via `product_tag` pivot
- **Tag** belongs to **TagType** (FK `type`)
- **Customer** has many **Rentals**, **DraftRentals**, **RentalRequests** (no direct FK on requests)
- **Rental** belongs to **Customer**; has many **RentalItems** and **Payments**
- **RentalItem** belongs to **Rental** and **Product**
- **Payment** belongs to **Rental**
- **DraftRental** belongs to **Customer**; has many **DraftImages**
- **DraftImage** belongs to **DraftRental**
- **Category** belongs to many **TagTypes** via `category_tagtypes` and `category_tag_type` (two pivot tables — potential duplicate migration)
- **TagType** belongs to many **Categories** (inverse)

## Enums Used
- `products.status`: available, rented, maintenance, retired
- `rentals.status`: reserved, picked_up, returned, cancelled, completed
- `rental_items.status`: reserved, picked_up, returned
- `payments.type`: downpayment, balance, security_deposit, penalty
- `payments.method`: cash, gcash, bank_transfer

## Notes
- Two pivot tables exist for category-tag_type association: `category_tagtypes` (2026_03_20) and `category_tag_type` (2026_04_16). The latter has a unique constraint; the former does not. Likely the first migration is superseded; consider removing or aligning.
- `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs` are Laravel defaults (not part of app domain).
- All domain models now have corresponding Eloquent Models in `app/Models/`.