<?php
/**
 * Nasabah Model
 * 
 * Handles customer (nasabah) related database operations
 * Integrates with db_orang for person data management
 */

require_once __DIR__ . '/../includes/database_class.php';

class Nasabah {
    private $db;
    
    public function __construct() {
        $this->db = db();
    }
    
    /**
     * Get nasabah by ID with person data from db_orang
     */
    public function getById($id) {
        $nasabah = $this->db->selectOne("SELECT n.*, c.nama_cabang FROM nasabah n LEFT JOIN cabang c ON n.cabang_id = c.id WHERE n.id = ?", [$id]);
        
        if ($nasabah && $nasabah['db_orang_user_id']) {
            // Get person data from db_orang
            $person = query_orang("SELECT * FROM people WHERE id = ? LIMIT 1", [$nasabah['db_orang_user_id']]);
            if (is_array($person) && isset($person[0])) {
                $nasabah['person'] = $person[0];
                
                // Get phone numbers
                $nasabah['person']['phones'] = getPersonPhones($nasabah['db_orang_user_id']);
                
                // Get emails
                $nasabah['person']['emails'] = getPersonEmails($nasabah['db_orang_user_id']);
                
                // Get documents
                $nasabah['person']['documents'] = getPersonDocuments($nasabah['db_orang_user_id']);
                
                // Get addresses
                $nasabah['person']['addresses'] = getPersonAddresses($nasabah['db_orang_user_id']);
            }
        }
        
        // Get location names from db_alamat if using old structure
        if ($nasabah) {
            if ($nasabah['province_id']) {
                $province = query_alamat("SELECT name FROM provinces WHERE id = ? LIMIT 1", [$nasabah['province_id']]);
                $nasabah['province_name'] = (is_array($province) && isset($province[0])) ? $province[0]['name'] : null;
            }
            if ($nasabah['regency_id']) {
                $regency = query_alamat("SELECT name FROM regencies WHERE id = ? LIMIT 1", [$nasabah['regency_id']]);
                $nasabah['regency_name'] = (is_array($regency) && isset($regency[0])) ? $regency[0]['name'] : null;
            }
            if ($nasabah['district_id']) {
                $district = query_alamat("SELECT name FROM districts WHERE id = ? LIMIT 1", [$nasabah['district_id']]);
                $nasabah['district_name'] = (is_array($district) && isset($district[0])) ? $district[0]['name'] : null;
            }
            if ($nasabah['village_id']) {
                $village = query_alamat("SELECT name FROM villages WHERE id = ? LIMIT 1", [$nasabah['village_id']]);
                $nasabah['village_name'] = (is_array($village) && isset($village[0])) ? $village[0]['name'] : null;
            }
        }
        
        return $nasabah;
    }
    
    /**
     * Get nasabah by kode
     */
    public function getByKode($kode) {
        return $this->db->selectOne("SELECT * FROM nasabah WHERE kode_nasabah = ?", [$kode]);
    }
    
    /**
     * Get nasabah by KTP (search in both nasabah.ktp and db_orang.people.ktp)
     */
    public function getByKTP($ktp) {
        // First try to find in nasabah table (old structure)
        $nasabah = $this->db->selectOne("SELECT * FROM nasabah WHERE ktp = ?", [$ktp]);
        if ($nasabah) return $nasabah;
        
        // If not found, search in db_orang.people
        $person = query_orang("SELECT * FROM people WHERE ktp = ? OR nomor_identitas = ? LIMIT 1", [$ktp, $ktp]);
        if (is_array($person) && isset($person[0])) {
            // Find nasabah by db_orang_user_id
            $nasabah = $this->db->selectOne("SELECT * FROM nasabah WHERE db_orang_user_id = ?", [$person[0]['id']]);
            if ($nasabah) return $nasabah;
        }
        
        return null;
    }
    
    /**
     * Get all nasabah with filters
     */
    public function getAll($status = null, $search = null, $cabang_id = null) {
        $sql = "SELECT n.*, c.nama_cabang 
                FROM nasabah n 
                LEFT JOIN cabang c ON n.cabang_id = c.id
                WHERE 1=1";
        $params = [];
        
        if ($cabang_id) {
            $sql .= " AND n.cabang_id = ?";
            $params[] = $cabang_id;
        }
        
        if ($status) {
            $sql .= " AND n.status = ?";
            $params[] = $status;
        }
        
        if ($search) {
            $sql .= " AND (n.nama LIKE ? OR n.kode_nasabah LIKE ? OR n.ktp LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY n.created_at DESC";
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Create new nasabah with person data in db_orang
     */
    public function create($data) {
        // First create person in db_orang
        $person_data = [
            'nama' => $data['nama'],
            'nama_depan' => $data['nama_depan'] ?? null,
            'nama_tengah' => $data['nama_tengah'] ?? null,
            'nama_belakang' => $data['nama_belakang'] ?? null,
            'ktp' => $data['ktp'] ?? null,
            'nomor_identitas' => $data['ktp'] ?? null,
            'telp' => $data['telp'] ?? null,
            'email' => $data['email'] ?? null,
            'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
            'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
            'tempat_lahir' => $data['tempat_lahir'] ?? null,
            'agama' => $data['agama'] ?? null,
            'pekerjaan' => $data['pekerjaan'] ?? $data['jenis_usaha'] ?? null,
            'golongan_darah_id' => $data['golongan_darah_id'] ?? null,
            'suku_id' => $data['suku_id'] ?? null,
            'status_perkawinan_id' => $data['status_perkawinan_id'] ?? null,
            'foto_ktp' => $data['foto_ktp'] ?? null,
            'foto_selfie' => $data['foto_selfie'] ?? null,
            'catatan' => $data['catatan'] ?? null
        ];
        
        $person_id = createPerson($person_data);
        
        if (!$person_id) return false;
        
        // Create address in db_orang if address data provided
        if (!empty($data['alamat']) || !empty($data['province_id'])) {
            $address_data = [
                'label' => 'rumah',
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
        
        // Then create nasabah record in kewer database
        $sql = "INSERT INTO nasabah (kode_nasabah, nama, alamat, province_id, regency_id, district_id, village_id, ktp, telp, email, jenis_usaha, lokasi_pasar, foto_ktp, foto_selfie, status, db_orang_user_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $params = [
            $data['kode_nasabah'],
            $data['nama'],
            $data['alamat'] ?? null,
            $data['province_id'] ?? null,
            $data['regency_id'] ?? null,
            $data['district_id'] ?? null,
            $data['village_id'] ?? null,
            $data['ktp'],
            $data['telp'],
            $data['email'] ?? null,
            $data['jenis_usaha'] ?? null,
            $data['lokasi_pasar'] ?? null,
            $data['foto_ktp'] ?? null,
            $data['foto_selfie'] ?? null,
            $data['status'] ?? 'aktif',
            $person_id
        ];
        
        return $this->db->insert($sql, $params);
    }
    
    /**
     * Update nasabah
     */
    public function update($id, $data) {
        // Update person data in db_orang if db_orang_user_id exists
        if (isset($data['db_orang_user_id']) && $data['db_orang_user_id']) {
            $person_data = [
                'nama' => $data['nama'],
                'nama_depan' => $data['nama_depan'] ?? null,
                'nama_tengah' => $data['nama_tengah'] ?? null,
                'nama_belakang' => $data['nama_belakang'] ?? null,
                'telp' => $data['telp'] ?? null,
                'email' => $data['email'] ?? null,
                'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
                'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
                'tempat_lahir' => $data['tempat_lahir'] ?? null,
                'agama' => $data['agama'] ?? null,
                'pekerjaan' => $data['pekerjaan'] ?? $data['jenis_usaha'] ?? null,
                'golongan_darah_id' => $data['golongan_darah_id'] ?? null,
                'suku_id' => $data['suku_id'] ?? null,
                'status_perkawinan_id' => $data['status_perkawinan_id'] ?? null,
                'foto_ktp' => $data['foto_ktp'] ?? null,
                'foto_selfie' => $data['foto_selfie'] ?? null,
                'catatan' => $data['catatan'] ?? null
            ];
            
            // Update person
            query_orang("UPDATE people SET 
                nama = ?, nama_depan = ?, nama_tengah = ?, nama_belakang = ?,
                telp = ?, email = ?, jenis_kelamin = ?, tanggal_lahir = ?, tempat_lahir = ?,
                agama = ?, pekerjaan = ?, golongan_darah_id = ?, suku_id = ?, status_perkawinan_id = ?,
                foto_ktp = ?, foto_selfie = ?, catatan = ?, updated_at = CURRENT_TIMESTAMP
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
                $person_data['foto_ktp'],
                $person_data['foto_selfie'],
                $person_data['catatan'],
                $data['db_orang_user_id']
            ]);
        }
        
        // Update nasabah record in kewer database
        $sql = "UPDATE nasabah SET nama = ?, alamat = ?, province_id = ?, regency_id = ?, district_id = ?, village_id = ?, telp = ?, email = ?, jenis_usaha = ?, lokasi_pasar = ?, status = ? WHERE id = ?";
        $params = [
            $data['nama'],
            $data['alamat'] ?? null,
            $data['province_id'] ?? null,
            $data['regency_id'] ?? null,
            $data['district_id'] ?? null,
            $data['village_id'] ?? null,
            $data['telp'],
            $data['email'] ?? null,
            $data['jenis_usaha'] ?? null,
            $data['lokasi_pasar'] ?? null,
            $data['status'] ?? 'aktif',
            $id
        ];
        
        return $this->db->update($sql, $params);
    }
    
    /**
     * Delete nasabah
     */
    public function delete($id) {
        return $this->db->delete("DELETE FROM nasabah WHERE id = ?", [$id]);
    }
    
    /**
     * Count active nasabah by cabang
     */
    public function countActive($cabangId) {
        return $this->db->count("nasabah", "cabang_id = ? AND status = 'aktif'", [$cabangId]);
    }
}
?>
