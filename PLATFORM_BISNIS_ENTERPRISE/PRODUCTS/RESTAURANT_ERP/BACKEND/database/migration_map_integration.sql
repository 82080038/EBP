-- Migration for Map Integration
-- This script adds geolocation support to branches table

ALTER TABLE branches 
ADD COLUMN latitude DECIMAL(10, 8) AFTER email,
ADD COLUMN longitude DECIMAL(11, 8) AFTER latitude,
ADD COLUMN delivery_radius_km DECIMAL(5, 2) DEFAULT 5 AFTER longitude,
ADD INDEX idx_branches_location (latitude, longitude);
