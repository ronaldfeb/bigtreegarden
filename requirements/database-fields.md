# Proposed Database Fields

Proposed columns for every table in `database.md`. Review and edit freely — migrations will be generated from this document.

Global conventions (applied to every table, not repeated below):

- `id` — uuid primary key
- `created_at` / `updated_at` timestamps
- `deleted_at` soft deletes on all domain tables (excluded on pure pivot/lookup tables where noted)
- All foreign keys are `foreignUuid` with constraints

Naming note: the existing code spells it `pamphlet` (correct English). I suggest keeping `pamphlet` in table/model names rather than `pamplet`. Table names below use Laravel plural conventions (e.g. `persons_of_interest`); flagged where they differ from `database.md`.

---

## Identity & staff

### users (exists — additions only)
| Field | Type | Notes |
|---|---|---|
| phone | string, nullable | contact number for receipts/notifications |
| deleted_at | timestamp, nullable | add soft deletes |

### staff_users
1:1 with `users`.
| Field | Type | Notes |
|---|---|---|
| user_id | FK users, unique | |
| role | string enum | `admin`, `marketing`, `content`, `support` |
| job_title | string, nullable | |
| is_active | boolean, default true | quick disable without deleting |
| invited_by_staff_user_id | FK staff_users, nullable | audit trail |
| last_active_at | timestamp, nullable | |

---

## Marketing

### marketing_adverts
QR codes with trackable UTM codes for physical ad placements.
| Field | Type | Notes |
|---|---|---|
| staff_user_id | FK staff_users | creator |
| client_name | string | where the physical ad is placed |
| campaign_name | string, nullable | |
| code | string, unique | short code used in the tracked URL, e.g. `/a/{code}` |
| destination_url | string | where the visitor is redirected after tracking |
| utm_source | string | |
| utm_medium | string, default `qr` | |
| utm_campaign | string | |
| utm_content | string, nullable | |
| qr_code_path | string, nullable | generated QR image |
| status | string enum, default `active` | `active`, `paused`, `archived` |
| starts_at / ends_at | timestamp, nullable | optional campaign window |

### marketing_advert_visits (suggested addition — not in database.md)
Needed for the "unique visits" analytics requirement.
| Field | Type | Notes |
|---|---|---|
| marketing_advert_id | FK | |
| visitor_hash | string, indexed | hash of IP + user agent for unique-visit counting |
| ip_address | string, nullable | |
| user_agent | string, nullable | |
| referrer | string, nullable | |
| visited_at | timestamp | |

No soft deletes (append-only log).

### marketing_leads
| Field | Type | Notes |
|---|---|---|
| staff_user_id | FK staff_users, nullable | assigned staff member |
| marketing_advert_id | FK, nullable | source advert if applicable |
| name | string | |
| email | string, nullable | |
| phone | string, nullable | |
| organisation | string, nullable | e.g. funeral home name |
| source | string enum | `advert`, `website`, `referral`, `walk_in`, `other` |
| status | string enum, default `new` | `new`, `contacted`, `qualified`, `converted`, `lost` |

### marketing_lead_notes
| Field | Type | Notes |
|---|---|---|
| marketing_lead_id | FK | |
| staff_user_id | FK staff_users | author |
| body | text | |

---

## Person of interest (core entity)

### persons_of_interest (`person_of_interest` in database.md)
The single QR code lives here — it resolves to the person's public page.
| Field | Type | Notes |
|---|---|---|
| created_by_user_id | FK users, nullable | null while a guest draft |
| guest_token_hash | string, nullable, unique | moved from `pamphlets` — guest draft flow |
| guest_token_expires_at | timestamp, nullable | |
| first_name | string | |
| last_name | string | |
| display_name | string | shown on public page, defaults to full name |
| date_of_birth | date | |
| date_of_passing | date | |
| place_of_birth | string, nullable | |
| place_of_passing | string, nullable | |
| profile_image_path | string, nullable | |
| public_slug | string, unique | the QR target: `/p/{public_slug}` |
| qr_code_path | string, nullable | generated after payment |
| qr_generated_at | timestamp, nullable | |
| status | string enum, default `draft` | `draft`, `active`, `archived` |

### user_persons_of_interest (pivot)
Users who can manage/administer the person's pages.
| Field | Type | Notes |
|---|---|---|
| user_id | FK users | |
| person_of_interest_id | FK | |
| role | string enum, default `manager` | `owner`, `manager` |

Unique on (`user_id`, `person_of_interest_id`). No soft deletes.

---

## Vault (schema only this iteration — no staff/UI work)

### person_of_interest_vaults
| Field | Type | Notes |
|---|---|---|
| person_of_interest_id | FK, unique | 1:1 |
| name | string, nullable | |
| status | string enum, default `sealed` | `sealed`, `released` |
| released_at | timestamp, nullable | when beneficiaries gain access |
| storage_limit_mb | unsignedInteger, default 1024 | |

### person_of_interest_vault_media
| Field | Type | Notes |
|---|---|---|
| vault_id | FK | |
| uploaded_by_user_id | FK users | |
| type | string enum | `image`, `video`, `pdf`, `voice_note` |
| title | string, nullable | |
| file_path | string | |
| mime_type | string | |
| file_size_bytes | unsignedBigInteger | |
| duration_seconds | unsignedInteger, nullable | video/voice notes |

### person_of_interest_vault_posts
| Field | Type | Notes |
|---|---|---|
| vault_id | FK | |
| author_user_id | FK users | |
| title | string, nullable | |
| body | longText | |
| visibility | string enum, default `all` | `all`, `selected` |

### person_of_interest_vault_post_beneficiaries (suggested addition — pivot for `selected` visibility)
| Field | Type | Notes |
|---|---|---|
| vault_post_id | FK | |
| vault_beneficiary_id | FK | |

Unique pair, no soft deletes.

### person_of_interest_vault_beneficiaries
| Field | Type | Notes |
|---|---|---|
| vault_id | FK | |
| type | string enum, default `beneficiary` | `executor`, `beneficiary` — app-enforced max 1 executor per vault |
| full_name | string | |
| email | string | |
| contact_number | string | |
| physical_address | text | |
| access_code_hash | string | unique code, stored hashed |
| access_code_hint | string, nullable | last 4 chars for support |
| first_accessed_at | timestamp, nullable | |

---

## Memorial pages

### memorial_pages
Belongs to a person of interest (1 POI has 1 or many).
| Field | Type | Notes |
|---|---|---|
| person_of_interest_id | FK | |
| title | string | |
| public_slug | string, unique | keeps existing `/memorial/{slug}` URLs |
| status | string enum, default `draft` | `draft`, `published`, `archived` |
| active_day_type | string enum, nullable | `memorial`, `funeral`, `unveiling` |
| active_day_date | date, nullable | enables live commenting on this day |
| obituary | longText, nullable | existing |
| funeral_programme | longText, nullable | existing |
| hymns | longText, nullable | existing |
| gallery_enabled | boolean, default true | existing |
| live_comments_enabled | boolean, default true | |
| published_at | timestamp, nullable | |

### memorial_page_images (rename of existing `memorial_gallery_images`)
| Field | Type | Notes |
|---|---|---|
| memorial_page_id | FK | |
| image_path | string | |
| caption | string, nullable | |
| sort_order | unsignedInteger, default 0 | |

### memorial_page_pamphlets (rename/restructure of existing `pamphlets`)
1:1 with memorial page. Person fields move to `persons_of_interest`.
| Field | Type | Notes |
|---|---|---|
| memorial_page_id | FK, unique | |
| background_id | FK memorial_page_pamphlet_backgrounds, nullable | |
| heading | string | e.g. "In Loving Memory" |
| short_text | text | |
| date_format | string, default `d M Y` | |
| image_shape | string, default `square` | existing |
| image_crop_mode | string, default `cover` | existing |
| uploaded_image_path | string, nullable | existing |
| preview_image_path | string, nullable | existing preview fields carried over |
| status | string enum, default `draft` | `draft`, `paid` |
| paid_at | timestamp, nullable | |

### memorial_page_pamphlet_styles (rename of existing `pamphlet_styles`)
| Field | Type | Notes |
|---|---|---|
| pamphlet_id | FK, unique | |
| font_family | string, nullable | existing |
| is_bold | boolean, default false | existing |
| is_italic | boolean, default false | existing |
| date_format | string, default `d M Y` | existing |
| text_color | string, nullable | suggested addition |

### memorial_page_pamphlet_background_collections (rename of existing `background_collections`)
| Field | Type | Notes |
|---|---|---|
| name | string | |
| slug | string, unique | |
| description | text, nullable | |
| sort_order | unsignedInteger, default 0 | |
| is_active | boolean, default true | staff can hide a collection |

### memorial_page_pamphlet_backgrounds (rename of existing `backgrounds`)
| Field | Type | Notes |
|---|---|---|
| collection_id | FK collections | |
| name | string | |
| image_path | string | |
| thumbnail_path | string, nullable | |
| sort_order | unsignedInteger, default 0 | |
| is_active | boolean, default true | |

### memorial_page_sections (`memorial_pages_sections` in database.md; rename of existing `memorial_additional_sections`)
| Field | Type | Notes |
|---|---|---|
| memorial_page_id | FK | |
| title | string | |
| body | longText | |
| sort_order | unsignedInteger, default 0 | |
| is_visible | boolean, default true | |

### memorial_sites
GPS coordinates of memorial/burial sites; 1 or many per person of interest.
| Field | Type | Notes |
|---|---|---|
| person_of_interest_id | FK | |
| name | string | e.g. "Westpark Cemetery, Plot 42" |
| site_type | string enum | `burial`, `memorial`, `scattering`, `other` |
| description | text, nullable | |
| address | text, nullable | |
| latitude | decimal(10,7) | |
| longitude | decimal(10,7) | |
| geofence_radius_m | unsignedInteger, default 5 | radius for paid posting |

### memorial_page_messages
Comments/"flowers" left by authenticated users; approved by a POI manager.
| Field | Type | Notes |
|---|---|---|
| memorial_page_id | FK | |
| author_user_id | FK users | |
| memorial_site_id | FK, nullable | site the user was verified at |
| transaction_id | FK transactions, nullable | the R5 flower payment |
| type | string enum, default `text` | `text`, `image` |
| body | text, nullable | |
| image_path | string, nullable | |
| context | string enum | `live_day` (free, active day) vs `flowers` (paid, GPS-gated) |
| posted_latitude | decimal(10,7), nullable | |
| posted_longitude | decimal(10,7), nullable | |
| is_gps_verified | boolean, default false | within geofence at posting time |
| status | string enum, default `pending` | `pending`, `approved`, `rejected` |
| approved_by_user_id | FK users, nullable | |
| approved_at | timestamp, nullable | |

---

## Payments

### transactions (replaces existing `payments`)
Generic across purchase types via a polymorphic payable.
| Field | Type | Notes |
|---|---|---|
| user_id | FK users, nullable | payer |
| payable_type / payable_id | morph | pamphlet, message (flower), subscription, vault |
| type | string enum | `pamphlet_purchase`, `flower_message`, `subscription`, `vault` |
| provider | string, default `payfast` | |
| merchant_reference | string, unique | our `m_payment_id` sent to PayFast |
| provider_payment_id | string, nullable | PayFast `pf_payment_id` |
| amount_cents | unsignedInteger | |
| currency | string(3), default `ZAR` | |
| status | string enum, default `initiated` | `initiated`, `pending`, `complete`, `failed`, `cancelled`, `refunded` |
| paid_at | timestamp, nullable | |
| raw_payload | json, nullable | last ITN payload |

### subscription_packages
| Field | Type | Notes |
|---|---|---|
| name | string | |
| slug | string, unique | |
| description | text, nullable | |
| price_cents | unsignedInteger | |
| currency | string(3), default `ZAR` | |
| billing_interval | string enum | `once_off`, `monthly`, `annual` |
| is_featured | boolean, default false | highlight on pricing page |
| is_active | boolean, default true | |
| sort_order | unsignedInteger, default 0 | |

### subscription_package_features
| Field | Type | Notes |
|---|---|---|
| subscription_package_id | FK | |
| label | string | e.g. "Unlimited gallery images" |
| description | string, nullable | |
| is_included | boolean, default true | allows shown-but-excluded rows |
| sort_order | unsignedInteger, default 0 | |

---

## Content & marketing site

### policies
One row per document type.
| Field | Type | Notes |
|---|---|---|
| type | string enum, unique | `terms_of_service`, `privacy_policy`, `about_us` |
| title | string | |
| body | longText | rich text |
| version | string, nullable | e.g. "v1.2" |
| published_at | timestamp, nullable | |
| updated_by_staff_user_id | FK staff_users, nullable | |

### blog_categories
| Field | Type | Notes |
|---|---|---|
| name | string | |
| slug | string, unique | |
| description | text, nullable | |
| sort_order | unsignedInteger, default 0 | |
| is_active | boolean, default true | |

### blogs
| Field | Type | Notes |
|---|---|---|
| blog_category_id | FK, nullable | |
| author_staff_user_id | FK staff_users | |
| title | string | |
| slug | string, unique | |
| excerpt | text, nullable | |
| body | longText | rich text with inline images |
| cover_image_path | string, nullable | |
| status | string enum, default `draft` | `draft`, `published` |
| published_at | timestamp, nullable | |
| meta_title / meta_description | string, nullable | SEO |
| view_count | unsignedBigInteger, default 0 | |

### help_center_topics
Top-level groups shown on the help center landing.
| Field | Type | Notes |
|---|---|---|
| name | string | |
| slug | string, unique | |
| description | text, nullable | |
| icon | string, nullable | lucide icon name |
| sort_order | unsignedInteger, default 0 | |
| is_active | boolean, default true | |

### help_center_categories
Cross-cutting tags applied to articles.
| Field | Type | Notes |
|---|---|---|
| name | string | |
| slug | string, unique | |
| sort_order | unsignedInteger, default 0 | |
| is_active | boolean, default true | |

### help_center_articles
| Field | Type | Notes |
|---|---|---|
| help_center_topic_id | FK topics | primary placement |
| author_staff_user_id | FK staff_users | |
| title | string | |
| slug | string, unique | |
| excerpt | text, nullable | |
| body | longText | rich text |
| status | string enum, default `draft` | `draft`, `published` |
| published_at | timestamp, nullable | |
| view_count | unsignedBigInteger, default 0 | |
| sort_order | unsignedInteger, default 0 | |

### help_center_article_categories (pivot)
| Field | Type | Notes |
|---|---|---|
| help_center_article_id | FK | |
| help_center_category_id | FK | |

Unique pair, no soft deletes.

> Note: `database.md` describes topics/categories/articles relationships ambiguously. Proposed model: **Topic → has many Articles; Articles ↔ Categories many-to-many** (pivot above). Adjust here if you intended a different hierarchy.

### testimonials
| Field | Type | Notes |
|---|---|---|
| user_id | FK users, nullable, unique | optional link to a real account |
| name | string | required |
| photo_path | string | required |
| role_or_location | string, nullable | e.g. "Johannesburg" |
| body | text | the testimonial |
| rating | unsignedTinyInteger, nullable | 1–5, optional |
| is_featured | boolean, default false | show on landing page |
| sort_order | unsignedInteger, default 0 | |
| status | string enum, default `published` | `draft`, `published` |

### partners
| Field | Type | Notes |
|---|---|---|
| name | string | |
| logo_path | string | |
| website_url | string, nullable | |
| sort_order | unsignedInteger, default 0 | |
| is_active | boolean, default true | |

### ambassadors (exists — additions only)
| Field | Type | Notes |
|---|---|---|
| deleted_at | timestamp, nullable | add soft deletes |

### ambassador_images (exists — additions only)
| Field | Type | Notes |
|---|---|---|
| deleted_at | timestamp, nullable | add soft deletes |

---

## Service providers

### service_providers
| Field | Type | Notes |
|---|---|---|
| name | string | |
| slug | string, unique | public profile URL |
| registration_number | string, nullable | company reg |
| description | longText, nullable | |
| logo_path | string, nullable | |
| cover_image_path | string, nullable | |
| email | string | |
| phone | string, nullable | |
| website_url | string, nullable | |
| physical_address | text, nullable | |
| city | string, nullable | |
| province | string, nullable | |
| status | string enum, default `pending` | `pending`, `active`, `suspended` |

### service_provider_users
| Field | Type | Notes |
|---|---|---|
| service_provider_id | FK | |
| user_id | FK users | |
| role | string enum, default `staff` | `owner`, `staff` |

Unique on (`service_provider_id`, `user_id`).

### service_provider_services
| Field | Type | Notes |
|---|---|---|
| service_provider_id | FK | |
| name | string | |
| description | text, nullable | |
| price_from_cents | unsignedInteger, nullable | "from R…" display |
| sort_order | unsignedInteger, default 0 | |

### service_provider_specialities
| Field | Type | Notes |
|---|---|---|
| service_provider_id | FK | |
| name | string | e.g. "Cremations", "Traditional burials" |
| sort_order | unsignedInteger, default 0 | |

### service_provider_social_media
| Field | Type | Notes |
|---|---|---|
| service_provider_id | FK | |
| platform | string enum | `facebook`, `instagram`, `tiktok`, `x`, `youtube`, `linkedin`, `whatsapp` |
| url | string | |

### service_provider_images
| Field | Type | Notes |
|---|---|---|
| service_provider_id | FK | |
| image_path | string | |
| caption | string, nullable | |
| sort_order | unsignedInteger, default 0 | |
