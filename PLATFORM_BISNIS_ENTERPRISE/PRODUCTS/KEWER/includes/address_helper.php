<?php
/**
 * Address Helper Functions
 * Functions to integrate with db_alamat_simple database
 */

/**
 * Get all provinces from db_alamat_simple
 */
function getProvinces() {
    $provinces = query_alamat("SELECT id, code, name FROM provinces ORDER BY name");
    return $provinces ?? [];
}

/**
 * Get regencies by province_id
 */
function getRegenciesByProvince($province_id) {
    if (!$province_id) return [];
    $regencies = query_alamat("SELECT id, code, name FROM regencies WHERE province_id = ? ORDER BY name", [$province_id]);
    return $regencies ?? [];
}

/**
 * Get districts by regency_id
 */
function getDistrictsByRegency($regency_id) {
    if (!$regency_id) return [];
    $districts = query_alamat("SELECT id, code, name FROM districts WHERE regency_id = ? ORDER BY name", [$regency_id]);
    return $districts ?? [];
}

/**
 * Get villages by district_id
 */
function getVillagesByDistrict($district_id) {
    if (!$district_id) return [];
    $villages = query_alamat("SELECT id, code, name FROM villages WHERE district_id = ? ORDER BY name", [$district_id]);
    return $villages ?? [];
}

/**
 * Get complete address by IDs
 */
function getCompleteAddress($province_id, $regency_id, $district_id, $village_id) {
    $address_parts = [];
    
    if ($village_id) {
        $village = query_alamat("SELECT name FROM villages WHERE id = ?", [$village_id]);
        if ($village) $address_parts[] = $village[0]['name'];
    }
    
    if ($district_id) {
        $district = query_alamat("SELECT name FROM districts WHERE id = ?", [$district_id]);
        if ($district) $address_parts[] = 'Kec. ' . $district[0]['name'];
    }
    
    if ($regency_id) {
        $regency = query_alamat("SELECT name FROM regencies WHERE id = ?", [$regency_id]);
        if ($regency) $address_parts[] = 'Kab. ' . $regency[0]['name'];
    }
    
    if ($province_id) {
        $province = query_alamat("SELECT name FROM provinces WHERE id = ?", [$province_id]);
        if ($province) $address_parts[] = $province[0]['name'];
    }
    
    return implode(', ', $address_parts);
}

/**
 * Search provinces by name
 */
function searchProvinces($keyword) {
    if (!$keyword) return [];
    $provinces = query_alamat("SELECT id, code, name FROM provinces WHERE name LIKE ? ORDER BY name", ["%$keyword%"]);
    return $provinces ?? [];
}

/**
 * Search regencies by name
 */
function searchRegencies($keyword) {
    if (!$keyword) return [];
    $regencies = query_alamat("SELECT id, code, name FROM regencies WHERE name LIKE ? ORDER BY name", ["%$keyword%"]);
    return $regencies ?? [];
}
