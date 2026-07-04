// Address loader functions - load regencies, districts, and villages
function loadRegencies(provinceId) {
    const regencySelect = document.getElementById('regency_id');
    const districtSelect = document.getElementById('district_id');
    const villageSelect = document.getElementById('village_id');
    
    // Reset dependent dropdowns
    regencySelect.innerHTML = '<option value="">Pilih Kabupaten/Kota</option>';
    districtSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
    villageSelect.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
    
    if (!provinceId) return;
    
    fetch('/kewer/api/alamat.php?action=regencies&province_id=' + provinceId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                data.data.forEach(regency => {
                    const option = document.createElement('option');
                    option.value = regency.id;
                    option.textContent = regency.name;
                    regencySelect.appendChild(option);
                });
            }
        })
        .catch(error => console.error('Error loading regencies:', error));
}

function loadDistricts(regencyId) {
    const districtSelect = document.getElementById('district_id');
    const villageSelect = document.getElementById('village_id');
    
    // Reset dependent dropdown
    districtSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
    villageSelect.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
    
    if (!regencyId) return;
    
    fetch('/kewer/api/alamat.php?action=districts&regency_id=' + regencyId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                data.data.forEach(district => {
                    const option = document.createElement('option');
                    option.value = district.id;
                    option.textContent = district.name;
                    districtSelect.appendChild(option);
                });
            }
        })
        .catch(error => console.error('Error loading districts:', error));
}

function loadVillages(districtId) {
    const villageSelect = document.getElementById('village_id');
    
    // Reset dependent dropdown
    villageSelect.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
    
    if (!districtId) return;
    
    fetch('/kewer/api/alamat.php?action=villages&district_id=' + districtId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                data.data.forEach(village => {
                    const option = document.createElement('option');
                    option.value = village.id;
                    option.textContent = village.name;
                    villageSelect.appendChild(option);
                });
            }
        })
        .catch(error => console.error('Error loading villages:', error));
}
