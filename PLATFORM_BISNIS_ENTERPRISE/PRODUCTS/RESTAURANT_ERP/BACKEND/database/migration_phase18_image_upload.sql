/*
========================================================
MIGRATION PHASE 18: IMAGE UPLOAD FOR ONBOARDING
========================================================
*/

USE ebp_restaurant_erp;

-- Add image_url to branches table (logo_url already exists in companies)
ALTER TABLE branches
ADD COLUMN image_url VARCHAR(255) AFTER phone;
