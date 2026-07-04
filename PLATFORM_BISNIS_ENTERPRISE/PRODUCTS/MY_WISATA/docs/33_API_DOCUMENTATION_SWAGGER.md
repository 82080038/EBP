# MODUL 33 — API DOCUMENTATION (SWAGGER)

> **Versi:** 1.0 · **Tanggal:** 2026-06-30

---

## 1. RINGKASAN

Dokumentasi API lengkap dalam format OpenAPI/Swagger untuk semua endpoint aplikasi Tour Guide.

---

## 2. OPENAPI SPECIFICATION

```yaml
openapi: 3.0.0
info:
  title: Tour Guide Application API
  description: API untuk aplikasi Tour Guide berbasis PHP Native
  version: 1.0.0
  contact:
    name: API Support
    email: support@tourguide.com

servers:
  - url: https://api.tourguide.com/v1
    description: Production Server
  - url: https://staging-api.tourguide.com/v1
    description: Staging Server
  - url: http://localhost:8000/v1
    description: Development Server

components:
  securitySchemes:
    bearerAuth:
      type: http
      scheme: bearer
      bearerFormat: JWT

security:
  - bearerAuth: []
```

---

## 3. AUTHENTICATION ENDPOINTS

### 3.1 Login

```yaml
paths:
  /auth/login:
    post:
      summary: User login
      tags:
        - Authentication
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              properties:
                email:
                  type: string
                  format: email
                password:
                  type: string
                  format: password
      responses:
        '200':
          description: Login successful
          content:
            application/json:
              schema:
                type: object
                properties:
                  status:
                    type: string
                    example: success
                  data:
                    type: object
                    properties:
                      token:
                        type: string
                      user:
                        type: object
        '401':
          description: Invalid credentials
```

### 3.2 Register

```yaml
  /auth/register:
    post:
      summary: User registration
      tags:
        - Authentication
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              properties:
                name:
                  type: string
                email:
                  type: string
                  format: email
                password:
                  type: string
                role:
                  type: string
                  enum: [wisatawan, tour_guide]
      responses:
        '201':
          description: Registration successful
        '400':
          description: Validation error
```

---

## 4. BOOKING ENDPOINTS

### 4.1 Create Booking

```yaml
  /bookings:
    post:
      summary: Create new booking
      tags:
        - Bookings
      security:
        - bearerAuth: []
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              properties:
                guide_id:
                  type: integer
                booking_date:
                  type: string
                  format: date
                guests:
                  type: integer
                notes:
                  type: string
      responses:
        '201':
          description: Booking created
          content:
            application/json:
              schema:
                type: object
                properties:
                  status:
                    type: string
                  data:
                    type: object
                    properties:
                      booking_id:
                        type: integer
                      booking_code:
                        type: string
        '400':
          description: Validation error
        '409':
          description: Guide not available
```

### 4.2 Get My Bookings

```yaml
  /bookings/my:
    get:
      summary: Get user's bookings
      tags:
        - Bookings
      security:
        - bearerAuth: []
      parameters:
        - name: status
          in: query
          schema:
            type: string
            enum: [pending, confirmed, completed, cancelled]
      responses:
        '200':
          description: List of bookings
```

---

## 5. DESTINATION ENDPOINTS

### 5.1 List Destinations

```yaml
  /destinations:
    get:
      summary: List all destinations
      tags:
        - Destinations
      parameters:
        - name: category_id
          in: query
          schema:
            type: integer
        - name: search
          in: query
          schema:
            type: string
        - name: page
          in: query
          schema:
            type: integer
            default: 1
        - name: limit
          in: query
          schema:
            type: integer
            default: 20
      responses:
        '200':
          description: List of destinations
          content:
            application/json:
              schema:
                type: object
                properties:
                  status:
                    type: string
                  data:
                    type: array
                    items:
                      type: object
                  pagination:
                    type: object
```

### 5.2 Get Destination Detail

```yaml
  /destinations/{id}:
    get:
      summary: Get destination detail
      tags:
        - Destinations
      parameters:
        - name: id
          in: path
          required: true
          schema:
            type: integer
      responses:
        '200':
          description: Destination detail
        '404':
          description: Destination not found
```

---

## 6. TOUR GUIDE ENDPOINTS

### 6.1 List Tour Guides

```yaml
  /guides:
    get:
      summary: List all tour guides
      tags:
        - Tour Guides
      parameters:
        - name: location
          in: query
          schema:
            type: string
        - name: language
          in: query
          schema:
            type: string
        - name: specialization
          in: query
          schema:
            type: string
      responses:
        '200':
          description: List of tour guides
```

### 6.2 Get Guide Profile

```yaml
  /guides/{id}:
    get:
      summary: Get tour guide profile
      tags:
        - Tour Guides
      parameters:
        - name: id
          in: path
          required: true
          schema:
            type: integer
      responses:
        '200':
          description: Guide profile
        '404':
          description: Guide not found
```

---

## 7. TICKET ENDPOINTS

### 7.1 Buy Ticket

```yaml
  /tickets/buy:
    post:
      summary: Buy destination ticket
      tags:
        - Tickets
      security:
        - bearerAuth: []
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              properties:
                destination_id:
                  type: integer
                ticket_type:
                  type: string
                quantity:
                  type: integer
                visit_date:
                  type: string
                  format: date
      responses:
        '201':
          description: Ticket purchased
        '400':
          description: Validation error
```

### 7.2 Verify Ticket

```yaml
  /tickets/verify:
    post:
      summary: Verify ticket QR code
      tags:
        - Tickets
      security:
        - bearerAuth: []
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              properties:
                qr_code:
                  type: string
      responses:
        '200':
          description: Ticket verified
        '400':
          description: Invalid or expired ticket
```

---

## 8. NOTIFICATION ENDPOINTS

### 8.1 Get Notifications

```yaml
  /notifications:
    get:
      summary: Get user notifications
      tags:
        - Notifications
      security:
        - bearerAuth: []
      parameters:
        - name: unread_only
          in: query
          schema:
            type: boolean
      responses:
        '200':
          description: List of notifications
```

### 8.2 Mark as Read

```yaml
  /notifications/{id}/read:
    post:
      summary: Mark notification as read
      tags:
        - Notifications
      security:
        - bearerAuth: []
      parameters:
        - name: id
          in: path
          required: true
          schema:
            type: integer
      responses:
        '200':
          description: Notification marked as read
```

---

## 9. REVIEW ENDPOINTS

### 9.1 Submit Review

```yaml
  /reviews:
    post:
      summary: Submit review
      tags:
        - Reviews
      security:
        - bearerAuth: []
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              properties:
                target_type:
                  type: string
                  enum: [guide, destination, hotel, restaurant]
                target_id:
                  type: integer
                rating:
                  type: integer
                  minimum: 1
                  maximum: 5
                comment:
                  type: string
      responses:
        '201':
          description: Review submitted
        '400':
          description: Validation error
```

---

## 10. ERROR RESPONSES

### 10.1 Standard Error Response

```yaml
components:
  schemas:
    Error:
      type: object
      properties:
        status:
          type: string
          example: error
        message:
          type: string
        errors:
          type: array
          items:
            type: object
```

### 10.2 HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 409 | Conflict |
| 422 | Validation Error |
| 429 | Too Many Requests |
| 500 | Internal Server Error |

---

## 11. RATE LIMITING

```yaml
  /rate-limit:
    get:
      summary: Rate limit information
      tags:
        - System
      responses:
        '200':
          description: Rate limit status
          content:
            application/json:
              schema:
                type: object
                properties:
                  limit:
                    type: integer
                    example: 100
                  remaining:
                    type: integer
                    example: 95
                  reset:
                    type: integer
                    example: 1625097600
```

**Rate Limit Rules:**
- Authentication endpoints: 10 requests/minute
- Booking endpoints: 20 requests/minute
- Search endpoints: 50 requests/minute
- Other endpoints: 100 requests/minute

---

## 12. PAGINATION

### 12.1 Standard Pagination Response

```yaml
components:
  schemas:
    Pagination:
      type: object
      properties:
        current_page:
          type: integer
        per_page:
          type: integer
        total:
          type: integer
        last_page:
          type: integer
```

### 12.2 Pagination Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| page | integer | 1 | Page number |
| limit | integer | 20 | Items per page (max: 100) |

---

## 13. FILTERING & SORTING

### 13.1 Common Filter Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| search | string | Full-text search |
| category_id | integer | Filter by category |
| status | string | Filter by status |
| date_from | string | Filter by date range (start) |
| date_to | string | Filter by date range (end) |

### 13.2 Sorting Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| sort_by | string | created_at | Field to sort by |
| sort_order | string | desc | Sort direction (asc/desc) |

---

## 14. WEBHOOKS

### 14.1 Booking Webhook

```yaml
  /webhooks/booking:
    post:
      summary: Booking status webhook
      tags:
        - Webhooks
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              properties:
                event:
                  type: string
                  enum: [booking_created, booking_confirmed, booking_cancelled]
                data:
                  type: object
      responses:
        '200':
          description: Webhook received
```

---

## 15. HEALTH CHECK

```yaml
  /health:
    get:
      summary: Health check endpoint
      tags:
        - System
      responses:
        '200':
          description: System healthy
          content:
            application/json:
              schema:
                type: object
                properties:
                  status:
                    type: string
                    example: healthy
                  timestamp:
                    type: string
                    format: date-time
                  services:
                    type: object
                    properties:
                      database:
                        type: string
                      cache:
                        type: string
                      storage:
                        type: string
```

---

## 16. TESTING WITH SWAGGER UI

### 16.1 Setup Swagger UI

```bash
# Install Swagger UI
npm install -g swagger-ui

# Or use Docker
docker run -p 8080:8080 -e SWAGGER_JSON=/swagger.json \
  -v $(pwd)/swagger.json:/swagger.json \
  swaggerapi/swagger-ui
```

### 16.2 Access Swagger UI

- Development: http://localhost:8080
- Staging: https://staging-api.tourguide.com/docs
- Production: https://api.tourguide.com/docs

---

## 17. API CLIENT EXAMPLES

### 17.1 JavaScript (Fetch)

```javascript
// Login
const login = async (email, password) => {
  const response = await fetch('https://api.tourguide.com/v1/auth/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ email, password }),
  });
  const data = await response.json();
  return data;
};

// Get bookings
const getBookings = async (token) => {
  const response = await fetch('https://api.tourguide.com/v1/bookings/my', {
    headers: {
      'Authorization': `Bearer ${token}`,
    },
  });
  const data = await response.json();
  return data;
};
```

### 17.2 PHP (cURL)

```php
// Login
function login($email, $password) {
    $ch = curl_init('https://api.tourguide.com/v1/auth/login');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'email' => $email,
        'password' => $password
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// Get bookings
function getBookings($token) {
    $ch = curl_init('https://api.tourguide.com/v1/bookings/my');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}
```

---

## 18. API VERSIONING

### 18.1 Version Strategy

- URL-based versioning: `/v1/`, `/v2/`
- Backward compatibility maintained for 1 year after deprecation
- Deprecation notices sent 3 months before removal

### 18.2 Version Changelog

| Version | Date | Changes |
|---------|------|---------|
| 1.0.0 | 2026-06-30 | Initial release |

---

> **Modul Selanjutnya:** `34_USER_MANUAL.md`
