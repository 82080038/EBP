<?php
namespace Kewer\Geo;

/**
 * GPS Tracker Service
 * Handles GPS location capture, validation, and geofencing
 * 
 * @author Kewer Development Team
 * @version 1.0.0
 */

class GPSTracker {
    
    /**
     * Validate GPS coordinates
     */
    public static function validateCoordinates($lat, $lng) {
        // Latitude: -90 to 90
        // Longitude: -180 to 180
        if (!is_numeric($lat) || !is_numeric($lng)) {
            return false;
        }
        
        $lat = (float) $lat;
        $lng = (float) $lng;
        
        return ($lat >= -90 && $lat <= 90) && ($lng >= -180 && $lng <= 180);
    }
    
    /**
     * Calculate distance between two points using Haversine formula
     * Returns distance in meters
     */
    public static function calculateDistance($lat1, $lng1, $lat2, $lng2) {
        $earthRadius = 6371000; // Earth radius in meters
        
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);
        
        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
             cos($lat1Rad) * cos($lat2Rad) *
             sin($deltaLng / 2) * sin($deltaLng / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }
    
    /**
     * Check if a point is within a radius of another point
     */
    public static function isWithinRadius($centerLat, $centerLng, $pointLat, $pointLng, $radiusMeters) {
        $distance = self::calculateDistance($centerLat, $centerLng, $pointLat, $pointLng);
        return $distance <= $radiusMeters;
    }
    
    /**
     * Check if location is within geofence area
     */
    public static function checkGeofence($lat, $lng, $cabangId) {
        // Get cabang location
        global $conn;
        $sql = "SELECT latitude, longitude, geofence_radius FROM cabang WHERE id = ?";
        $result = query($sql, [$cabangId]);
        
        if (!is_array($result) || empty($result)) {
            return ['valid' => false, 'message' => 'Cabang not found'];
        }
        
        $cabang = $result[0];
        
        if (!$cabang['latitude'] || !$cabang['longitude']) {
            return ['valid' => true, 'message' => 'No geofence set for this cabang'];
        }
        
        $radius = $cabang['geofence_radius'] ?? 5000; // Default 5km
        
        if (self::isWithinRadius($cabang['latitude'], $cabang['longitude'], $lat, $lng, $radius)) {
            return ['valid' => true, 'distance' => self::calculateDistance($cabang['latitude'], $cabang['longitude'], $lat, $lng)];
        }
        
        return ['valid' => false, 'message' => 'Location outside geofence', 'distance' => self::calculateDistance($cabang['latitude'], $cabang['longitude'], $lat, $lng)];
    }
    
    /**
     * Get GPS accuracy level
     */
    public static function getAccuracyLevel($accuracy) {
        if ($accuracy < 10) {
            return 'Excellent';
        } elseif ($accuracy < 25) {
            return 'Good';
        } elseif ($accuracy < 50) {
            return 'Fair';
        } elseif ($accuracy < 100) {
            return 'Poor';
        } else {
            return 'Very Poor';
        }
    }
    
    /**
     * Capture and validate GPS location
     */
    public static function captureLocation($lat, $lng, $accuracy = null) {
        // Validate coordinates
        if (!self::validateCoordinates($lat, $lng)) {
            return ['success' => false, 'message' => 'Invalid GPS coordinates'];
        }
        
        $data = [
            'latitude' => (float) $lat,
            'longitude' => (float) $lng,
            'accuracy' => $accuracy ? (float) $accuracy : null,
            'accuracy_level' => $accuracy ? self::getAccuracyLevel($accuracy) : null,
            'captured_at' => date('Y-m-d H:i:s')
        ];
        
        return ['success' => true, 'data' => $data];
    }
    
    /**
     * Reverse geocode (convert lat/lng to address)
     * Note: This requires external API like Google Maps Geocoding API
     */
    public static function reverseGeocode($lat, $lng) {
        // Placeholder for reverse geocoding
        // In production, integrate with Google Maps API or similar
        return [
            'success' => true,
            'address' => 'Reverse geocoding not implemented yet',
            'latitude' => $lat,
            'longitude' => $lng
        ];
    }
}
