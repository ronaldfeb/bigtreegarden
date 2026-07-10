uuid for primary keys and foreign key constraints
soft deletes
created at
updated at
deleted at

Models:

- users
- staff_users # container of users who are staff users 1 to 1
- marketing_adverts # container for generating QR codes with trackable utm codes
- marketing_leads # belongs to staff users
- marketing_lead_notes # 1 marketing_leads may have many
- person_of_interest
- person_of_interest_vault # 1 to 1 with person_of_interest
- person_of_interest_vault_media
- person_of_interest_vault_posts
- person_of_interest_vault_beneficiaries # email and contact details of next of kins. with type such as executor, beneficiary
- user_persons_of_interest (many to many) #represents a relationship of the user who can manage and administer the person of interest page
- memorial_pages # 1 person_of_interest has 1 or many
- memorial_page_images # 1 memorial_pages may have many
- memorial_page_pamplet # 1 to 1 on memorial_pages
- memorial_page_pamplet_styles #1 to 1 memorial_page_pamplet
- memorial_page_pamplet_backgrounds
- memorial_page_pamplet_background_collections
- memorial_pages_sections # 1 memorial_pages may have many, this is the content that goes on the page
- memorial_sites # 1 or many belongs to person_of_interest, these have the gps coordinates of a memorial/burial site
- memorial_page_messages # 1 memorial_pages has 0 or many, these are created by an user(author) and approved by the user_persons_of_interest user
- ambassadors
- ambassador_images # ambassadors may have many
- help_center_categories # 1 may belong to many help_center_topics
- help_center_topics #1 may belong to many help_center_articles
- help_center_articles #1 has many help_center_article_categories
- help_center_article_categories # has many help_center_categories
- policies # for terms_of_service, privacy_policy, about_us
- blogs #images in richtext
- blog_categories
- testimonials # 1 to 1 with users(optional) must have name and photo
- subscription_packages
- subscription_package_features
- transactions
- partners # collection to maintain partner logos and names
- service_providers
- service_provider_users # 1 service_providers may have many
- service_provider_services
- service_provider_social_media
- service_provider_specialities
- service_provider_images
