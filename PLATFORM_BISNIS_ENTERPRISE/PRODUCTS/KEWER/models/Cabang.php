<?php
/**
 * Cabang Model
 * 
 * Handles branch (cabang) related database operations
 * Integrates with db_orang for person data management
 */

require_once __DIR__ . '/../includes/database_class.php';

class Cabang {
    private $db;
    
    public function __construct() {
        $this->db = db();
    }
    
    /**
     * Get cabang by ID with person data from db_orang
     */
    public function getById($id) {
        $cabang = $this->db->selectOne("SELECT c.* FROM cabang c WHERE c.id = ?", [$id]);
        
        if ($cabang && $cabang['db_orang_person_id']) {
            // Get person data from db_orang
            $person = query_orang("SELECT * FROM people WHERE id = ? LIMIT 1", [$cabang['db_orang_person_id']]);
            if (is_array($person) && isset($person[0])) {
                $cabang['person'] = $person[0];
                
                // Get phone numbers
                $cabang['person']['phones'] = getPersonPhones($cabang['db_orang_person_id']);
                
                // Get emails
                $cabang['person']['emails'] = getPersonEmails($cabang['db_orang_person_id']);
                
                // Get documents
                $cabang['person']['documents'] = getPersonDocuments($cabang['db_orang_person_id']);
                
                // Get addresses
                $cabang['person']['addresses'] = getPersonAddresses($cabang['db_orang_person_id']);
            }
        }
        
        // Get location names from db_alamat
        if ($cabang) {
            if ($cabang['province_id']) {
                $province = query_alamat("SELECT name FROM provinces WHERE id = ? LIMIT 1", [$cabang['province_id']]);
                $cabang['province_name'] = (is_array($province) && isset($province[0])) ? $province[0]['name'] : null;
            }
            if ($cabang['regency_id']) {
                $regency = query_alamat("SELECT name FROM regencies WHERE id = ? LIMIT 1", [$cabang['regency_id']]);
                $cabang['regency_name'] = (is_array($regency) && isset($regency[0])) ? $regency[0]['name'] : null;
            }
            if ($cabang['district_id']) {
                $district = query_alamat("SELECT name FROM districts WHERE id = ? LIMIT 1", [$cabang['district_id']]);
                $cabang['district_name'] = (is_array($district) && isset($district[0])) ? $district[0]['name'] : null;
            }
            if ($cabang['village_id']) {
                $village = query_alamat("SELECT name FROM villages WHERE id = ? LIMIT 1", [$cabang['village_id']]);
                $cabang['village_name'] = (is_array($village) && isset($village[0])) ? $village[0]['name'] : null;
            }
        }
        
        return $cabang;
    }
    
    /**
     * Get cabang by kode
     */
    public function getByKode($kode) {
        $cabang = $this->db->selectOne("SELECT c.* FROM cabang c WHERE c.kode_cabang = ?", [$kode]);
        
        if ($cabang && $cabang['db_orang_person_id']) {
            $person = query_orang("SELECT * FROM people WHERE id = ? LIMIT 1", [$cabang['db_orang_person_id']]);
            if (is_array($person) && isset($person[0])) {
                $cabang['person'] = $person[0];
                $cabang['person']['phones'] = getPersonPhones($cabang['db_orang_person_id']);
                $cabang['person']['emails'] = getPersonEmails($cabang['db_orang_person_id']);
            }
        }
        
        return $cabang;
    }
    
    /**
     * Get all cabang
     */
    public function getAll() {
        return $this->db->select("
            SELECT c.*,
                   p.name as province_name, r.name as regency_name,
                   d.name as district_name, v.name as village_name
            FROM cabang c 
            LEFT JOIN db_alamat.provinces p ON c.province_id = p.id
            LEFT JOIN db_alamat.regencies r ON c.regency_id = r.id
            LEFT JOIN db_alamat.districts d ON c.district_id = d.id
            LEFT JOIN db_alamat.villages v ON c.village_id = v.id
            ORDER BY c.created_at DESC");
    }
    
    /**
     * Get active cabang
     */
    public function getActive() {
        return $this->db->select("
            SELECT c.*,
                   p.name as province_name, r.name as regency_name,
                   d.name as district_name, v.name as village_name
            FROM cabang c 
            LEFT JOIN db_alamat.provinces p ON c.province_id = p.id
            LEFT JOIN db_alamat.regencies r ON c.regency_id = r.id
            LEFT JOIN db_alamat.districts d ON c.district_id = d.id
            LEFT JOIN db_alamat.villages v ON c.village_id = v.id
            WHERE c.status = 'aktif'
            ORDER BY c.nama_cabang ASC");
    }
    
    /**
     * Create new cabang with person data in db_orang
     */
    public function create($data) {
        // First create person in db_orang
        $person_data = [
            'nama' => $data['nama_cabang'] ?? $data['nama'] ?? '',
            'nama_depan' => $data['nama_depan'] ?? null,
            'nama_tengah' => $data['nama_tengah'] ?? null,
            'nama_belakang' => $data['nama_belakang'] ?? null,
            'telp' => $data['telp'] ?? null,
            'email' => $data['email'] ?? null,
            'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
            'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
            'tempat_lahir' => $data['tempat_lahir'] ?? null,
            'agama' => $data['agama'] ?? null,
            'pekerjaan' => $data['pekerjaan'] ?? null,
            'golongan_darah_id' => $data['golongan_darah_id'] ?? null,
            'suku_id' => $data['suku_id'] ?? null,
            'status_perkawinan_id' => $data['status_perkawinan_id'] ?? null,
            'catatan' => $data['catatan'] ?? null
        ];
        
        $person_id = createPerson($person_data);
        
        if (!$person_id) return false;
        
        // Create address in db_orang if address data provided
        if (!empty($data['alamat']) || !empty($data['province_id'])) {
            $address_data = [
                'label' => 'kantor',
                'street_address' => $data['alamat'] ?? null,
                'province_id' => $data['province_id'] ?? null,
                'regency_id' => $data['regency_id'] ?? null,
                'district_id' => $data['district_id'] ?? null,
                'village_id' => $data['village_id'] ?? null,
                'postal_code' => $data['kode_pos'] ?? null,
                'is_primary' => 1
            ];
            createPersonAddress($person_id, $address_data);
        }
        
        // Then create cabang record in kewer database
        $sql = "INSERT INTO cabang (kode_cabang, nama_cabang, alamat, province_id, regency_id, district_id, village_id, telp, email, status, db_orang_person_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $params = [
            $data['kode_cabang'],
            $data['nama_cabang'],
            $data['alamat'] ?? null,
            $data['province_id'] ?? null,
            $data['regency_id'] ?? null,
            $data['district_id'] ?? null,
            $data['village_id'] ?? null,
            $data['telp'] ?? null,
            $data['email'] ?? null,
            $data['status'] ?? 'aktif',
            $person_id
        ];
        
        return $this->db->insert($sql, $params);
    }
    
    /**
     * Update cabang
     */
    public function update($id, $data) {
        // Update person data in db_orang if db_orang_person_id exists
        if (isset($data['db_orang_person_id']) && $data['db_orang_person_id']) {
            $person_data = [
                'nama' => $data['nama_cabang'] ?? $data['nama'] ?? '',
                'nama_depan' => $data['nama_depan'] ?? null,
                'nama_tengah' => $data['nama_tengah'] ?? null,
                'nama_belakang' => $data['nama_belakang'] ?? null,
                'telp' => $data['telp'] ?? null,
                'email' => $data['email'] ?? null,
                'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
                'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
                'tempat_lahir' => $data['tempat_lahir'] ?? null,
                'agama' => $data['agama'] ?? null,
                'pekerjaan' => $data['pekerjaan'] ?? null,
                'golongan_darah_id' => $data['golongan_darah_id'] ?? null,
                'suku_id' => $data['suku_id'] ?? null,
                'status_perkawinan_id' => $data['status_perkawinan_id'] ?? null,
                'catatan' => $data['catatan'] ?? null
            ];
            
            // Update person
            query_orang("UPDATE people SET 
                nama = ?, nama_depan = ?, nama_tengah = ?, nama_belakang = ?,
                telp = ?, email = ?, jenis_kelamin = ?, tanggal_lahir = ?, tempat_lahir = ?,
                agama = ?, pekerjaan = ?, golongan_darah_id = ?, suku_id = ?, status_perkawinan_id = ?,
                catatan = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?", [
                $person_data['nama'],
                $person_data['nama_depan'],
                $person_data['nama_tengah'],
                $person_data['nama_belakang'],
                $person_data['telp'],
                $person_data['email'],
                $person_data['jenis_kelamin'],
                $person_data['tanggal_lahir'],
                $person_data['tempat_lahir'],
                $person_data['agama'],
                $person_data['pekerjaan'],
                $person_data['golongan_darah_id'],
                $person_data['suku_id'],
                $person_data['status_perkawinan_id'],
                $person_data['catatan'],
                $data['db_orang_person_id']
            ]);
        }
        
        // Update cabang record in kewer database
        $sql = "UPDATE cabang SET nama_cabang = ?, alamat = ?, province_id = ?, regency_id = ?, district_id = ?, village_id = ?, telp = ?, email = ?, status = ? WHERE id = ?";
        $params = [
            $data['nama_cabang'],
            $data['alamat'] ?? null,
            $data['province_id'] ?? null,
            $data['regency_id'] ?? null,
            $data['district_id'] ?? null,
            $data['village_id'] ?? null,
            $data['telp'] ?? null,
            $data['email'] ?? null,
            $data['status'] ?? 'aktif',
            $id
        ];
        
        return $this->db->update($sql, $params);
    }
    
    /**
     * Delete cabang
     */
    public function delete($id) {
        return $this->db->delete("DELETE FROM cabang WHERE id = ?", [$id]);
    }
    
    /**
     * Count active cabang
     */
    public function countActive() {
        return $this->db->count("cabang", "status = 'aktif'");
    }
}
?>
