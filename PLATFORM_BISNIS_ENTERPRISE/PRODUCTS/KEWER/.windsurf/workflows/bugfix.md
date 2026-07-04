---
description: Workflow perbaikan error untuk aplikasi Kewer v2.5.0
---

# Bugfix Workflow (v2.5.0)

## Identifikasi Error

### 1. Cek Browser Console
- Buka Developer Tools (F12)
- Cek Console tab untuk JavaScript errors
- Cek Network tab untuk failed API requests
- Cek response status dan error messages

### 2. Cek API Response
- Verifikasi API endpoint mengembalikan format yang benar:
  ```json
  {
    "success": true|false,
    "data": { ... },
    "error": "Error message if failed"
  }
  ```
- Cek HTTP status codes (200, 400, 401, 403, 500)

### 3. Cek Server Logs
- Cek PHP error logs
- Cek database connection errors
- Cek API authentication errors

## Perbaikan Error

### Frontend Errors (Client-Side Rendering)
1. **Loading Spinner Stuck**
   - Cek API endpoint response
   - Verifikasi KewerAPI function call
   - Cek network connectivity

2. **Data Not Rendering**
   - Verifikasi render function logic
   - Cek data structure matches template
   - Cek DOM element IDs

3. **Form Submission Failed**
   - Cek form data serialization
   - Verifikasi API endpoint URL
   - Cek CSRF token (if required)

### Backend Errors (API)
1. **API Returns 500 Error**
   - Cek PHP syntax errors
   - Verifikasi database queries
   - Cek authentication logic

2. **Data Not Found**
   - Verifikasi database records exist
   - Cek WHERE clause conditions
   - Verify role-based filters

3. **Permission Denied**
   - Cek user role permissions
   - Verify API authentication token
   - Check role-based access control

## Perbaikan Menyeluruh

### 1. Cek Pola Serupa
- Search for similar error patterns in codebase
- Check if same issue exists in other pages
- Apply fix to all affected pages

### 2. Update API Helper
- If issue is with KewerAPI, update `includes/js/api.js`
- Test with multiple endpoints
- Update documentation

### 3. Perbarui Workflow
- Update `.windsurf/analysis.md` if architecture change needed
- Update relevant workflow documentation
- Update TODO list
