<?php
/**
 * Address Helper Functions
 * 
 * Helper functions for address management in Kewer application
 * Supports hierarchical address selection: Province -> Regency -> District -> Village
 * INTEGRATED with db_alamat_simple database
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Get all provinces (filtered to Sumatera Utara for this application)
 */
function getProvinces() {
    return query_alamat("SELECT id, code, name FROM provinces ORDER BY name");
}

/**
 * Get province by ID
 */
function getProvinceById($id) {
    $result = query_alamat("SELECT id, code, name FROM provinces WHERE id = ?", [$id]);
    return $result[0] ?? null;
}

/**
 * Get regencies by province ID
 */
function getRegenciesByProvince($provinceId) {
    return query_alamat("SELECT id, code, name FROM regencies WHERE province_id = ? ORDER BY name", [$provinceId]);
}

/**
 * Get regency by ID
 */
function getRegencyById($id) {
    $result = query_alamat("SELECT id, code, name, province_id FROM regencies WHERE id = ?", [$id]);
    return $result[0] ?? null;
}

/**
 * Get districts by regency ID
 */
function getDistrictsByRegency($regencyId) {
    return query_alamat("SELECT id, code, name FROM districts WHERE regency_id = ? ORDER BY name", [$regencyId]);
}

/**
 * Get district by ID
 */
function getDistrictById($id) {
    $result = query_alamat("SELECT id, code, name, regency_id FROM districts WHERE id = ?", [$id]);
    return $result[0] ?? null;
}

/**
 * Get villages by district ID
 */
function getVillagesByDistrict($districtId) {
    return query_alamat("SELECT id, code, name, postal_code FROM villages WHERE district_id = ? ORDER BY name", [$districtId]);
}

/**
 * Get village by ID
 */
function getVillageById($id) {
    $result = query_alamat("SELECT id, code, name, postal_code, district_id FROM villages WHERE id = ?", [$id]);
    return $result[0] ?? null;
}

/**
 * Get full address string from address IDs
 */
function getFullAddressString($provinceId = null, $regencyId = null, $districtId = null, $villageId = null) {
    $parts = [];
    
    if ($villageId) {
        $village = getVillageById($villageId);
        if ($village) {
            $parts[] = "Desa/Kelurahan " . $village['name'];
        }
    }
    
    if ($districtId) {
        $district = getDistrictById($districtId);
        if ($district) {
            $parts[] = "Kecamatan " . $district['name'];
        }
    }
    
    if ($regencyId) {
        $regency = getRegencyById($regencyId);
        if ($regency) {
            $parts[] = $regency['name'];
        }
    }
    
    if ($provinceId) {
        $province = getProvinceById($provinceId);
        if ($province) {
            $parts[] = $province['name'];
        }
    }
    
    return implode(', ', $parts);
}

/**
 * Generate HTML select dropdown for provinces
 */
function provinceDropdown($name = 'province_id', $selected = null, $attrs = '') {
    $provinces = getProvinces();
    $html = '<select name="' . $name . '" id="' . $name . '" class="form-select" ' . $attrs . '>';
    $html .= '<option value="">Pilih Provinsi</option>';
    
    foreach ($provinces as $province) {
        $isSelected = ($selected == $province['id']) ? 'selected' : '';
        $html .= '<option value="' . $province['id'] . '" ' . $isSelected . '>' . htmlspecialchars($province['name']) . '</option>';
    }
    
    $html .= '</select>';
    return $html;
}

/**
 * Generate HTML select dropdown for regencies
 */
function regencyDropdown($name = 'regency_id', $selected = null, $provinceId = null, $attrs = '') {
    $regencies = [];
    if ($provinceId) {
        $regencies = getRegenciesByProvince($provinceId);
    }
    
    $html = '<select name="' . $name . '" id="' . $name . '" class="form-select" ' . $attrs . '>';
    $html .= '<option value="">Pilih Kabupaten/Kota</option>';
    
    foreach ($regencies as $regency) {
        $isSelected = ($selected == $regency['id']) ? 'selected' : '';
        $html .= '<option value="' . $regency['id'] . '" ' . $isSelected . '>' . htmlspecialchars($regency['name']) . '</option>';
    }
    
    $html .= '</select>';
    return $html;
}

/**
 * Generate HTML select dropdown for districts
 */
function districtDropdown($name = 'district_id', $selected = null, $regencyId = null, $attrs = '') {
    $districts = [];
    if ($regencyId) {
        $districts = getDistrictsByRegency($regencyId);
    }
    
    $html = '<select name="' . $name . '" id="' . $name . '" class="form-select" ' . $attrs . '>';
    $html .= '<option value="">Pilih Kecamatan</option>';
    
    foreach ($districts as $district) {
        $isSelected = ($selected == $district['id']) ? 'selected' : '';
        $html .= '<option value="' . $district['id'] . '" ' . $isSelected . '>' . htmlspecialchars($district['name']) . '</option>';
    }
    
    $html .= '</select>';
    return $html;
}

/**
 * Generate HTML select dropdown for villages
 */
function villageDropdown($name = 'village_id', $selected = null, $districtId = null, $attrs = '') {
    $villages = [];
    if ($districtId) {
        $villages = getVillagesByDistrict($districtId);
    }
    
    $html = '<select name="' . $name . '" id="' . $name . '" class="form-select" ' . $attrs . '>';
    $html .= '<option value="">Pilih Desa/Kelurahan</option>';
    
    foreach ($villages as $village) {
        $isSelected = ($selected == $village['id']) ? 'selected' : '';
        $html .= '<option value="' . $village['id'] . '" ' . $isSelected . '>' . htmlspecialchars($village['name']) . '</option>';
    }
    
    $html .= '</select>';
    return $html;
}
