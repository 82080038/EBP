/*
========================================================
MIGRATION PHASE 21: POST-ONBOARDING SETTINGS
========================================================
This migration ensures the settings table is ready for
post-onboarding configuration storage
*/

USE ebp_restaurant_erp;

-- The settings table already exists in schema.sql
-- No additional migration needed, settings table can store:
-- - payment_methods (JSON)
-- - split_payment (BOOLEAN)
-- - max_discount_percent (NUMBER)
-- - manager_override_password (STRING - should be hashed)
-- - allow_void (BOOLEAN)
-- - allow_refund (BOOLEAN)
-- - receipt_header (STRING)
-- - receipt_footer (STRING)
-- - show_customer_info (BOOLEAN)
-- - receipt_copies (NUMBER)
