<?php
/**
 * People Helper Functions
 * Functions to integrate with db_orang database
 *
 * Schema:
 *   db_orang.people    — data orang (linked to kewer.users or kewer.nasabah)
 *   db_orang.addresses — alamat orang (linked to people.id)
 *   db_orang.people_phones — multiple phone numbers per person
 *   db_orang.people_emails — multiple emails per person
 *   db_orang.people_documents — documents per person
 *   Referensi lokasi   → db_alamat.provinces/regencies/districts/villages
 */

/**
 * Create a person record in db_orang.people
 * Returns the person ID or false on failure
 */
function createPerson($data) {
    // Support both old and new field names for backward compatibility
    $nama_depan = $data['nama_depan'] ?? $data['nama'] ?? '';
    $nama_tengah = $data['nama_tengah'] ?? null;
    $nama_belakang = $data['nama_belakang'] ?? null;
    $nama_lengkap = $data['nama_lengkap'] ?? $data['nama'] ?? '';

    // Map old fields to new structure
    $jenis_kelamin = $data['jenis_kelamin'] ?? null;
    $jenis_kelamin_id = $data['jenis_kelamin_id'] ?? null;
    if ($jenis_kelamin && !$jenis_kelamin_id) {
        // Convert old enum to new foreign key
        $jenis_kelamin_id = ($jenis_kelamin === 'L') ? 1 : 2;
    }

    $agama = $data['agama'] ?? null;
    $agama_id = $data['agama_id'] ?? null;
    if ($agama && !$agama_id) {
        // Convert old string to new foreign key
        $agama_map = query_orang("SELECT id FROM ref_agama WHERE nama = ? OR kode = ? LIMIT 1", [$agama, strtoupper($agama)]);
        $agama_id = (is_array($agama_map) && isset($agama_map[0])) ? $agama_map[0]['id'] : null;
    }

    $pekerjaan = $data['pekerjaan'] ?? null;
    $pekerjaan_id = $data['pekerjaan_id'] ?? null;
    if ($pekerjaan && !$pekerjaan_id) {
        // Convert old string to new foreign key
        $pekerjaan_map = query_orang("SELECT id FROM ref_pekerjaan WHERE nama = ? LIMIT 1", [$pekerjaan]);
        $pekerjaan_id = (is_array($pekerjaan_map) && isset($pekerjaan_map[0])) ? $pekerjaan_map[0]['id'] : null;
    }

    $sql = "INSERT INTO people (
        kewer_user_id, kewer_nasabah_id, nama,
        gelar_id, nama_depan, nama_tengah, nama_belakang, nama_lengkap,
        jenis_identitas_id, nomor_identitas, ktp,
        jenis_kelamin, jenis_kelamin_id,
        tanggal_lahir, tempat_lahir,
        agama, agama_id,
        pekerjaan, pekerjaan_id,
        golongan_darah_id, suku_id, status_perkawinan_id,
        foto_ktp, foto_selfie, catatan
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $result = query_orang($sql, [
        $data['kewer_user_id'] ?? null,
        $data['kewer_nasabah_id'] ?? null,
        $data['nama'] ?? '', // Keep old field for backward compatibility
        $data['gelar_id'] ?? null,
        $nama_depan,
        $nama_tengah,
        $nama_belakang,
        $nama_lengkap,
        $data['jenis_identitas_id'] ?? 1, // Default KTP
        $data['nomor_identitas'] ?? $data['ktp'] ?? null,
        $data['ktp'] ?? null, // Keep old field
        $jenis_kelamin,
        $jenis_kelamin_id,
        $data['tanggal_lahir'] ?? null,
        $data['tempat_lahir'] ?? null,
        $agama,
        $agama_id,
        $pekerjaan,
        $pekerjaan_id,
        $data['golongan_darah_id'] ?? null,
        $data['suku_id'] ?? null,
        $data['status_perkawinan_id'] ?? null,
        $data['foto_ktp'] ?? null,
        $data['foto_selfie'] ?? null,
        $data['catatan'] ?? null
    ]);

    if ($result !== false && $result > 0) {
        $last = query_orang("SELECT LAST_INSERT_ID() as id");
        $person_id = (is_array($last) && isset($last[0])) ? (int)$last[0]['id'] : false;

        // Migrate phone to people_phones if provided
        if ($person_id && isset($data['telp']) && $data['telp']) {
            addPersonPhone($person_id, [
                'phone_number' => $data['telp'],
                'jenis_telepon_id' => 1, // Default Mobile
                'is_primary' => 1
            ]);
        }

        // Migrate email to people_emails if provided
        if ($person_id && isset($data['email']) && $data['email']) {
            addPersonEmail($person_id, [
                'email' => $data['email'],
                'jenis_email_id' => 1, // Default Personal
                'is_primary' => 1
            ]);
        }

        // Migrate document to people_documents if provided
        if ($person_id && isset($data['ktp']) && $data['ktp']) {
            addPersonDocument($person_id, [
                'jenis_identitas_id' => 1, // KTP
                'nomor_dokumen' => $data['ktp'],
                'file_path' => $data['foto_ktp'] ?? null,
                'is_verified' => 1
            ]);
        }

        return $person_id;
    }
    return false;
}

/**
 * Create an address for a person in db_orang.addresses
 */
function createPersonAddress($person_id, $data) {
    // Support both old and new field names
    $label = $data['label'] ?? 'rumah';
    $jenis_alamat_id = $data['jenis_alamat_id'] ?? null;
    
    // Map label to jenis_alamat_id if not provided
    if (!$jenis_alamat_id && $label) {
        $jenis_map = query_orang("SELECT id FROM ref_jenis_alamat WHERE nama = ? OR kode = ? LIMIT 1", [$label, strtoupper($label)]);
        $jenis_alamat_id = (is_array($jenis_map) && isset($jenis_map[0])) ? $jenis_map[0]['id'] : null;
    }

    $sql = "INSERT INTO addresses (
        person_id, label, jenis_alamat_id, jenis_properti_id,
        street_address, nama_gedung, house_number, nomor_unit, rt, rw,
        province_id, regency_id, district_id, village_id, postal_code,
        latitude, longitude, is_primary, catatan
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    return query_orang($sql, [
        $person_id,
        $label,
        $jenis_alamat_id,
        $data['jenis_properti_id'] ?? null,
        $data['street_address'] ?? '',
        $data['nama_gedung'] ?? null,
        $data['house_number'] ?? '',
        $data['nomor_unit'] ?? null,
        $data['rt'] ?? null,
        $data['rw'] ?? null,
        $data['province_id'] ?? null,
        $data['regency_id'] ?? null,
        $data['district_id'] ?? null,
        $data['village_id'] ?? null,
        $data['postal_code'] ?? '',
        $data['latitude'] ?? null,
        $data['longitude'] ?? null,
        $data['is_primary'] ?? 1,
        $data['catatan'] ?? null
    ]);
}

/**
 * Get person by kewer_user_id
 */
function getPersonByUserId($kewer_user_id) {
    if (!$kewer_user_id) return null;
    $result = query_orang("SELECT * FROM people WHERE kewer_user_id = ? LIMIT 1", [$kewer_user_id]);
    return (is_array($result) && isset($result[0])) ? $result[0] : null;
}

/**
 * Get person by kewer_nasabah_id
 */
function getPersonByNasabahId($kewer_nasabah_id) {
    if (!$kewer_nasabah_id) return null;
    $result = query_orang("SELECT * FROM people WHERE kewer_nasabah_id = ? LIMIT 1", [$kewer_nasabah_id]);
    return (is_array($result) && isset($result[0])) ? $result[0] : null;
}

/**
 * Get person addresses from db_orang (by person_id)
 */
function getPersonAddresses($person_id) {
    if (!$person_id) return [];
    $addresses = query_orang("
        SELECT a.*,
               p.name as province_name, r.name as regency_name,
               d.name as district_name, v.name as village_name
        FROM addresses a
        LEFT JOIN db_alamat.provinces p ON a.province_id = p.id
        LEFT JOIN db_alamat.regencies r ON a.regency_id = r.id
        LEFT JOIN db_alamat.districts d ON a.district_id = d.id
        LEFT JOIN db_alamat.villages v ON a.village_id = v.id
        WHERE a.person_id = ?
        ORDER BY a.is_primary DESC, a.created_at DESC
    ", [$person_id]);
    return is_array($addresses) ? $addresses : [];
}

/**
 * Get primary address for a person
 */
function getPrimaryAddress($person_id) {
    if (!$person_id) return null;
    $address = query_orang("
        SELECT a.*,
               p.name as province_name, r.name as regency_name,
               d.name as district_name, v.name as village_name
        FROM addresses a
        LEFT JOIN db_alamat.provinces p ON a.province_id = p.id
        LEFT JOIN db_alamat.regencies r ON a.regency_id = r.id
        LEFT JOIN db_alamat.districts d ON a.district_id = d.id
        LEFT JOIN db_alamat.villages v ON a.village_id = v.id
        WHERE a.person_id = ? AND a.is_primary = 1
        LIMIT 1
    ", [$person_id]);
    return (is_array($address) && isset($address[0])) ? $address[0] : null;
}

/**
 * Update person address
 */
function updatePersonAddress($address_id, $data) {
    $sql = "UPDATE addresses SET
        street_address = ?, house_number = ?, rt = ?, rw = ?,
        province_id = ?, regency_id = ?, district_id = ?, village_id = ?,
        postal_code = ?, updated_at = CURRENT_TIMESTAMP
        WHERE id = ?";

    return query_orang($sql, [
        $data['street_address'] ?? '',
        $data['house_number'] ?? '',
        $data['rt'] ?? null,
        $data['rw'] ?? null,
        $data['province_id'] ?? null,
        $data['regency_id'] ?? null,
        $data['district_id'] ?? null,
        $data['village_id'] ?? null,
        $data['postal_code'] ?? '',
        $address_id
    ]);
}

/**
 * Set primary address
 */
function setPrimaryAddress($person_id, $address_id) {
    query_orang("UPDATE addresses SET is_primary = 0 WHERE person_id = ?", [$person_id]);
    return query_orang("UPDATE addresses SET is_primary = 1 WHERE id = ? AND person_id = ?", [$address_id, $person_id]);
}

/**
 * Delete person address
 */
function deletePersonAddress($address_id, $person_id) {
    return query_orang("DELETE FROM addresses WHERE id = ? AND person_id = ?", [$address_id, $person_id]);
}

/**
 * Cari person di db_orang berdasarkan NIK/KTP.
 * Return row people atau null jika tidak ditemukan.
 */
function findPersonByKtp($ktp) {
    if (!$ktp || strlen(trim($ktp)) !== 16) return null;
    $result = query_orang("SELECT * FROM people WHERE ktp = ? LIMIT 1", [trim($ktp)]);
    return (is_array($result) && isset($result[0])) ? $result[0] : null;
}

/**
 * Cek apakah NIK sudah terdaftar sebagai user AKTIF di koperasi (owner_bos_id) tertentu.
 * Digunakan saat pendaftaran petugas baru untuk mencegah duplikat aktif.
 *
 * Return: array user jika sudah aktif, null jika belum/sudah resign.
 */
function isKtpActiveInKoperasi($ktp, $owner_bos_id) {
    if (!$ktp || !$owner_bos_id) return null;

    // Cari person di db_orang dulu
    $person = findPersonByKtp($ktp);
    if (!$person) return null;

    // Cek apakah kewer_user_id-nya aktif di koperasi ini
    if (!$person['kewer_user_id']) return null;

    $user = query(
        "SELECT id, username, nama, role, status FROM users
         WHERE id = ? AND owner_bos_id = ? AND status = 'aktif'",
        [$person['kewer_user_id'], $owner_bos_id]
    );
    return (is_array($user) && isset($user[0])) ? $user[0] : null;
}

/**
 * Ambil riwayat koperasi dari NIK — semua user (aktif maupun nonaktif) yang
 * terhubung ke NIK ini di seluruh koperasi dalam platform.
 * Berguna untuk referensi bos baru saat mendaftarkan petugas yang pernah bekerja di tempat lain.
 *
 * Return: array riwayat [{user_id, username, role, status, nama_koperasi, cabang, tanggal_masuk}]
 */
function getKtpKoperasiHistory($ktp) {
    if (!$ktp) return [];

    // Temukan semua kewer_user_id yang pernah memakai KTP ini
    // KTP bisa muncul di people dengan kewer_user_id berbeda jika pernah resign & daftar ulang
    $persons = query_orang("SELECT kewer_user_id FROM people WHERE ktp = ? AND kewer_user_id IS NOT NULL", [trim($ktp)]);
    if (!is_array($persons) || empty($persons)) return [];

    $user_ids = array_column($persons, 'kewer_user_id');
    $placeholders = implode(',', array_fill(0, count($user_ids), '?'));

    $rows = query(
        "SELECT u.id, u.username, u.nama, u.role, u.status, u.tanggal_masuk,
                c.nama_cabang, b.nama as nama_bos
         FROM users u
         LEFT JOIN cabang c ON u.cabang_id = c.id
         LEFT JOIN users b ON u.owner_bos_id = b.id
         WHERE u.id IN ($placeholders)
         ORDER BY u.created_at DESC",
        $user_ids
    );
    return is_array($rows) ? $rows : [];
}

/**
 * Sinkronisasi: setelah user di-nonaktifkan (resign), update status people di db_orang.
 * Data identitas TIDAK dihapus — hanya status yang diupdate.
 */
function syncPersonStatusOnResign($kewer_user_id) {
    if (!$kewer_user_id) return false;
    return query_orang(
        "UPDATE people SET status = 'nonaktif', updated_at = CURRENT_TIMESTAMP WHERE kewer_user_id = ?",
        [$kewer_user_id]
    );
}

/**
 * Sinkronisasi: saat petugas yang pernah resign daftar ulang di koperasi baru,
 * buat record people baru (dengan kewer_user_id baru) yang tetap memiliki KTP sama.
 * Data lama (kewer_user_id lama) tidak diubah — histori terjaga.
 */
function createPersonForReturningStaff($new_user_id, $ktp, $nama, $extra = []) {
    return createPerson(array_merge([
        'kewer_user_id'  => $new_user_id,
        'nama'           => $nama,
        'ktp'            => $ktp,
        'telp'           => $extra['telp'] ?? null,
        'email'          => $extra['email'] ?? null,
        'jenis_kelamin'  => $extra['jenis_kelamin'] ?? null,
        'tanggal_lahir'  => $extra['tanggal_lahir'] ?? null,
        'tempat_lahir'   => $extra['tempat_lahir'] ?? null,
        'pekerjaan'      => $extra['pekerjaan'] ?? null,
    ], $extra));
}

/**
 * Convenience: Create person + address in one call
 * Used when registering bos or creating nasabah
 */
function createPersonWithAddress($person_data, $address_data) {
    $person_id = createPerson($person_data);
    if (!$person_id) return false;

    if (!empty($address_data['street_address']) || !empty($address_data['province_id'])) {
        createPersonAddress($person_id, $address_data);
    }
    return $person_id;
}

// ============================================
// Phone Number Functions
// ============================================

/**
 * Add phone number for a person
 */
function addPersonPhone($person_id, $data) {
    $sql = "INSERT INTO people_phones (
        person_id, phone_number, jenis_telepon_id, is_primary, is_verified
    ) VALUES (?, ?, ?, ?, ?)";

    return query_orang($sql, [
        $person_id,
        $data['phone_number'],
        $data['jenis_telepon_id'] ?? 1, // Default Mobile
        $data['is_primary'] ?? 0,
        $data['is_verified'] ?? 0
    ]);
}

/**
 * Get phone numbers for a person
 */
function getPersonPhones($person_id) {
    if (!$person_id) return [];
    $phones = query_orang("
        SELECT pp.*, rjt.nama as jenis_nama, rjt.kode as jenis_kode
        FROM people_phones pp
        LEFT JOIN ref_jenis_telepon rjt ON pp.jenis_telepon_id = rjt.id
        WHERE pp.person_id = ?
        ORDER BY pp.is_primary DESC, pp.created_at DESC
    ", [$person_id]);
    return is_array($phones) ? $phones : [];
}

/**
 * Set primary phone
 */
function setPrimaryPhone($person_id, $phone_id) {
    query_orang("UPDATE people_phones SET is_primary = 0 WHERE person_id = ?", [$person_id]);
    return query_orang("UPDATE people_phones SET is_primary = 1 WHERE id = ? AND person_id = ?", [$phone_id, $person_id]);
}

/**
 * Delete phone
 */
function deletePersonPhone($phone_id, $person_id) {
    return query_orang("DELETE FROM people_phones WHERE id = ? AND person_id = ?", [$phone_id, $person_id]);
}

// ============================================
// Email Functions
// ============================================

/**
 * Add email for a person
 */
function addPersonEmail($person_id, $data) {
    $sql = "INSERT INTO people_emails (
        person_id, email, jenis_email_id, is_primary, is_verified
    ) VALUES (?, ?, ?, ?, ?)";

    return query_orang($sql, [
        $person_id,
        $data['email'],
        $data['jenis_email_id'] ?? 1, // Default Personal
        $data['is_primary'] ?? 0,
        $data['is_verified'] ?? 0
    ]);
}

/**
 * Get emails for a person
 */
function getPersonEmails($person_id) {
    if (!$person_id) return [];
    $emails = query_orang("
        SELECT pe.*, rje.nama as jenis_nama, rje.kode as jenis_kode
        FROM people_emails pe
        LEFT JOIN ref_jenis_email rje ON pe.jenis_email_id = rje.id
        WHERE pe.person_id = ?
        ORDER BY pe.is_primary DESC, pe.created_at DESC
    ", [$person_id]);
    return is_array($emails) ? $emails : [];
}

/**
 * Set primary email
 */
function setPrimaryEmail($person_id, $email_id) {
    query_orang("UPDATE people_emails SET is_primary = 0 WHERE person_id = ?", [$person_id]);
    return query_orang("UPDATE people_emails SET is_primary = 1 WHERE id = ? AND person_id = ?", [$email_id, $person_id]);
}

/**
 * Delete email
 */
function deletePersonEmail($email_id, $person_id) {
    return query_orang("DELETE FROM people_emails WHERE id = ? AND person_id = ?", [$email_id, $person_id]);
}

// ============================================
// Document Functions
// ============================================

/**
 * Add document for a person
 */
function addPersonDocument($person_id, $data) {
    $sql = "INSERT INTO people_documents (
        person_id, jenis_identitas_id, nomor_dokumen, file_path,
        tanggal_ekspedisi, tanggal_kadaluarsa, is_verified, catatan
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    return query_orang($sql, [
        $person_id,
        $data['jenis_identitas_id'] ?? 1, // Default KTP
        $data['nomor_dokumen'],
        $data['file_path'] ?? null,
        $data['tanggal_ekspedisi'] ?? null,
        $data['tanggal_kadaluarsa'] ?? null,
        $data['is_verified'] ?? 0,
        $data['catatan'] ?? null
    ]);
}

/**
 * Get documents for a person
 */
function getPersonDocuments($person_id) {
    if (!$person_id) return [];
    $documents = query_orang("
        SELECT pd.*, rji.nama as jenis_nama, rji.kode as jenis_kode
        FROM people_documents pd
        LEFT JOIN ref_jenis_identitas rji ON pd.jenis_identitas_id = rji.id
        WHERE pd.person_id = ?
        ORDER BY pd.created_at DESC
    ", [$person_id]);
    return is_array($documents) ? $documents : [];
}

/**
 * Verify document
 */
function verifyPersonDocument($document_id, $verified_by) {
    return query_orang(
        "UPDATE people_documents SET is_verified = 1, verified_at = CURRENT_TIMESTAMP, verified_by = ? WHERE id = ?",
        [$verified_by, $document_id]
    );
}

/**
 * Delete document
 */
function deletePersonDocument($document_id, $person_id) {
    return query_orang("DELETE FROM people_documents WHERE id = ? AND person_id = ?", [$document_id, $person_id]);
}

// ============================================
// Family Relations Functions
// ============================================

/**
 * Add family relation
 */
function addFamilyRelation($person_id, $data) {
    $sql = "INSERT INTO family_relations (
        person_id, relative_person_id, relationship_type_id, is_primary, catatan
    ) VALUES (?, ?, ?, ?, ?)";

    return query_orang($sql, [
        $person_id,
        $data['relative_person_id'],
        $data['relationship_type_id'],
        $data['is_primary'] ?? 0,
        $data['catatan'] ?? null
    ]);
}

/**
 * Get family relations for a person
 */
function getFamilyRelations($person_id) {
    if (!$person_id) return [];
    $relations = query_orang("
        SELECT fr.*, rjr.nama as relasi_nama, rjr.kode as relasi_kode,
               p.nama as relative_nama, p.nama_lengkap as relative_nama_lengkap
        FROM family_relations fr
        LEFT JOIN ref_jenis_relasi rjr ON fr.relationship_type_id = rjr.id
        LEFT JOIN people p ON fr.relative_person_id = p.id
        WHERE fr.person_id = ?
        ORDER BY fr.is_primary DESC, fr.created_at DESC
    ", [$person_id]);
    return is_array($relations) ? $relations : [];
}

/**
 * Delete family relation
 */
function deleteFamilyRelation($relation_id, $person_id) {
    return query_orang("DELETE FROM family_relations WHERE id = ? AND person_id = ?", [$relation_id, $person_id]);
}
