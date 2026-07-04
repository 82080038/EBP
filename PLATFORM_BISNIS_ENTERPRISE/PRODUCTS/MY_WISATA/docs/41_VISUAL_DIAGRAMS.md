# MODUL 41 — VISUAL DIAGRAMS

> **Versi:** 1.0 · **Tanggal:** 2026-06-30

---

## 1. RINGKASAN

Kumpulan diagram visual untuk aplikasi Tour Guide dalam format Mermaid dan deskripsi untuk diagram tools.

---

## 2. ARCHITECTURE DIAGRAM

### 2.1 System Architecture

```mermaid
graph TB
    subgraph "Client Layer"
        UI[Web UI]
        Mobile[Mobile App]
    end
    
    subgraph "Web Server Layer"
        Nginx[Nginx / Apache]
        PHP[PHP 8.1+]
    end
    
    subgraph "Application Layer"
        Controller[Controllers]
        Service[Services]
        Model[Models]
    end
    
    subgraph "Data Layer"
        MySQL[(MySQL 8.0)]
        Redis[(Redis Cache)]
    end
    
    subgraph "External Services"
        Payment[Payment Gateway]
        SMS[SMS Gateway]
        Email[Email Service]
        Maps[OpenStreetMap]
    end
    
    UI --> Nginx
    Mobile --> Nginx
    Nginx --> PHP
    PHP --> Controller
    Controller --> Service
    Service --> Model
    Model --> MySQL
    Service --> Redis
    Service --> Payment
    Service --> SMS
    Service --> Email
    Service --> Maps
```

---

## 3. DATABASE ERD

### 3.1 Core Tables ERD

```mermaid
erDiagram
    users ||--o{ bookings : "makes"
    users ||--o{ reviews : "writes"
    users ||--o{ notifications : "receives"
    users ||--o{ ticket_orders : "purchases"
    
    tour_guides ||--o{ bookings : "receives"
    tour_guides ||--o{ reviews : "receives"
    tour_guides ||--o{ guide_schedules : "has"
    
    destinations ||--o{ reviews : "receives"
    destinations ||--o{ tickets : "has"
    destinations ||--o{ audio_guides : "has"
    
    bookings ||--|| transactions : "has"
    ticket_orders ||--|| transactions : "has"
    
    users {
        int id PK
        string name
        string email UK
        string password
        string role
        string status
        datetime created_at
    }
    
    tour_guides {
        int id PK
        int user_id FK
        string name
        string location
        string languages
        string specialization
        decimal price_per_hour
        string status
        float rating
    }
    
    destinations {
        int id PK
        string name
        string description
        int category_id FK
        string location
        decimal ticket_price
        string status
        float rating
    }
    
    bookings {
        int id PK
        int user_id FK
        int guide_id FK
        date booking_date
        int guests
        string status
        string booking_code
        datetime created_at
    }
    
    transactions {
        int id PK
        string transaction_code
        string type
        decimal amount
        string status
        string payment_method
        datetime created_at
    }
    
    reviews {
        int id PK
        int user_id FK
        int target_id
        string target_type
        int rating
        text comment
        datetime created_at
    }
    
    notifications {
        int id PK
        int user_id FK
        string type
        string title
        text message
        boolean is_read
        datetime created_at
    }
```

---

## 4. FLOW DIAGRAMS

### 4.1 Booking Flow

```mermaid
flowchart TD
    A[User Login] --> B[Search Guide]
    B --> C[View Guide Profile]
    C --> D{Select Date & Guests}
    D --> E[Create Booking]
    E --> F{Payment Required?}
    F -->|Yes| G[Process Payment]
    F -->|No| H[Booking Pending]
    G --> I{Payment Success?}
    I -->|Yes| H
    I -->|No| J[Booking Failed]
    H --> K[Guide Notified]
    K --> L{Guide Accepts?}
    L -->|Yes| M[Booking Confirmed]
    L -->|No| N[Booking Rejected]
    M --> O[Tour Completed]
    O --> P[User Reviews Guide]
```

### 4.2 Ticket Purchase Flow

```mermaid
flowchart TD
    A[User Login] --> B[Search Destination]
    B --> C[View Destination Detail]
    C --> D[Select Ticket Type]
    D --> E[Enter Quantity & Date]
    E --> F[Create Ticket Order]
    F --> G[Process Payment]
    G --> H{Payment Success?}
    H -->|Yes| I[Generate QR Code]
    H -->|No| J[Order Failed]
    I --> K[Email QR Code]
    K --> L[User Visits Destination]
    L --> M[Scan QR Code]
    M --> N{QR Valid?}
    N -->|Yes| O[Ticket Used]
    N -->|No| P[Invalid Ticket]
```

### 4.3 Authentication Flow

```mermaid
flowchart TD
    A[User Enters Credentials] --> B[Validate Input]
    B --> C{Valid?}
    C -->|No| D[Show Error]
    C -->|Yes| E[Check Database]
    E --> F{User Exists?}
    F -->|No| G[Invalid Credentials]
    F -->|Yes| H{Password Match?}
    H -->|No| G
    H -->|Yes| I{Account Active?}
    I -->|No| J[Account Locked]
    I -->|Yes| K{Account Locked?}
    K -->|Yes| L[Too Many Attempts]
    K -->|No| M[Generate Session]
    M --> N[Create JWT Token]
    N --> O[Redirect to Dashboard]
```

---

## 5. SEQUENCE DIAGRAMS

### 5.1 Booking Sequence

```mermaid
sequenceDiagram
    participant User
    participant UI
    participant Controller
    participant Service
    participant DB
    participant PaymentGateway
    
    User->>UI: Select Guide & Date
    UI->>Controller: POST /api/bookings
    Controller->>Service: createBooking()
    Service->>DB: Check availability
    DB-->>Service: Available
    Service->>DB: Create booking
    DB-->>Service: Booking created
    Service->>PaymentGateway: Initiate payment
    PaymentGateway-->>Service: Payment URL
    Service-->>Controller: Booking pending
    Controller-->>UI: Redirect to payment
    UI->>PaymentGateway: Complete payment
    PaymentGateway->>Service: Payment callback
    Service->>DB: Update booking status
    Service->>DB: Create transaction
    Service-->>User: Payment confirmation
```

### 5.2 Authentication Sequence

```mermaid
sequenceDiagram
    participant User
    participant UI
    participant Controller
    participant Service
    participant DB
    participant JWT
    
    User->>UI: Enter email & password
    UI->>Controller: POST /api/auth/login
    Controller->>Service: login()
    Service->>DB: Find user by email
    DB-->>Service: User data
    Service->>Service: Verify password
    Service->>DB: Update last login
    Service->>JWT: Generate token
    JWT-->>Service: Token
    Service-->>Controller: Auth response
    Controller-->>UI: Token & user data
    UI->>UI: Store token
    UI-->>User: Redirect to dashboard
```

---

## 6. COMPONENT DIAGRAMS

### 6.1 MVC Architecture

```mermaid
graph LR
    subgraph "View Layer"
        V1[Dashboard View]
        V2[Booking View]
        V3[Profile View]
    end
    
    subgraph "Controller Layer"
        C1[Auth Controller]
        C2[Booking Controller]
        C3[User Controller]
    end
    
    subgraph "Model Layer"
        M1[User Model]
        M2[Booking Model]
        M3[Guide Model]
    end
    
    subgraph "Service Layer"
        S1[Auth Service]
        S2[Booking Service]
        S3[Notification Service]
    end
    
    V1 --> C1
    V2 --> C2
    V3 --> C3
    C1 --> S1
    C2 --> S2
    C3 --> M1
    S1 --> M1
    S2 --> M2
    S2 --> M3
    S2 --> S3
```

---

## 7. DEPLOYMENT DIAGRAM

### 7.1 Production Deployment

```mermaid
graph TB
    subgraph "Load Balancer"
        LB[Nginx Load Balancer]
    end
    
    subgraph "Web Servers"
        WS1[Web Server 1]
        WS2[Web Server 2]
        WS3[Web Server 3]
    end
    
    subgraph "Database Cluster"
        DB1[(MySQL Master)]
        DB2[(MySQL Slave 1)]
        DB3[(MySQL Slave 2)]
    end
    
    subgraph "Cache Layer"
        Redis[(Redis Cluster)]
    end
    
    subgraph "Storage"
        S3[Cloud Storage S3]
        CDN[CDN]
    end
    
    subgraph "Monitoring"
        Monitor[Prometheus]
        Grafana[Grafana]
    end
    
    LB --> WS1
    LB --> WS2
    LB --> WS3
    
    WS1 --> DB1
    WS2 --> DB2
    WS3 --> DB3
    
    WS1 --> Redis
    WS2 --> Redis
    WS3 --> Redis
    
    WS1 --> S3
    WS2 --> S3
    WS3 --> S3
    
    S3 --> CDN
    
    WS1 --> Monitor
    WS2 --> Monitor
    WS3 --> Monitor
    
    Monitor --> Grafana
```

---

## 8. STATE DIAGRAMS

### 8.1 Booking State Machine

```mermaid
stateDiagram-v2
    [*] --> Pending: Create Booking
    Pending --> Confirmed: Guide Accepts
    Pending --> Rejected: Guide Rejects
    Pending --> Cancelled: User Cancels
    Pending --> Cancelled: Timeout (24h)
    
    Confirmed --> InProgress: Tour Starts
    Confirmed --> Cancelled: User Cancels
    
    InProgress --> Completed: Tour Ends
    InProgress --> Cancelled: Emergency Cancel
    
    Completed --> [*]
    Rejected --> [*]
    Cancelled --> [*]
```

### 8.2 Transaction State Machine

```mermaid
stateDiagram-v2
    [*] --> Pending: Create Transaction
    Pending --> Processing: Payment Initiated
    Pending --> Failed: Payment Failed
    Pending --> Cancelled: User Cancels
    
    Processing --> Paid: Payment Success
    Processing --> Failed: Payment Failed
    Processing --> Refunded: Refund Requested
    
    Paid --> Refunded: Refund Approved
    Paid --> [*]
    
    Failed --> [*]
    Cancelled --> [*]
    Refunded --> [*]
```

---

## 9. CLASS DIAGRAMS

### 9.1 Core Classes

```mermaid
classDiagram
    class Controller {
        +request
        +response
        +view()
        +json()
        +redirect()
    }
    
    class Model {
        +db
        +table
        +find()
        +findAll()
        +create()
        +update()
        +delete()
    }
    
    class Service {
        +db
        +execute()
    }
    
    class Database {
        -connection
        +getInstance()
        +query()
        +beginTransaction()
        +commit()
        +rollback()
    }
    
    Controller --> Service
    Service --> Database
    Model --> Database
```

---

## 10. NETWORK DIAGRAM

### 10.1 Network Topology

```mermaid
graph TB
    subgraph "Internet"
        User[Users]
    end
    
    subgraph "DMZ"
        FW[Firewall]
        LB[Load Balancer]
        WAF[Web Application Firewall]
    end
    
    subgraph "Application Zone"
        WS1[Web Server 1]
        WS2[Web Server 2]
    end
    
    subgraph "Database Zone"
        DB[(MySQL)]
        Redis[(Redis)]
    end
    
    subgraph "Backup Zone"
        Backup[Backup Server]
    end
    
    User --> FW
    FW --> WAF
    WAF --> LB
    LB --> WS1
    LB --> WS2
    WS1 --> DB
    WS2 --> DB
    WS1 --> Redis
    WS2 --> Redis
    DB --> Backup
```

---

## 11. TIMELINE DIAGRAMS

### 11.1 Development Timeline

```mermaid
gantt
    title Tour Guide Development Timeline
    dateFormat  YYYY-MM-DD
    section Phase 1
    Requirements Analysis      :done, p1, 2026-01-01, 2026-01-15
    Database Design           :done, p2, 2026-01-16, 2026-01-30
    Architecture Design       :done, p3, 2026-02-01, 2026-02-15
    
    section Phase 2
    Core Development         :active, p4, 2026-02-16, 2026-04-30
    Module Development       :p5, 2026-05-01, 2026-06-30
    
    section Phase 3
    Testing                  :p6, 2026-07-01, 2026-07-31
    Deployment               :p7, 2026-08-01, 2026-08-15
    
    section Phase 4
    Launch                   :p8, 2026-08-16, 2026-08-30
```

---

## 12. DIAGRAM TOOLS

### 12.1 Recommended Tools

| Tool | Purpose | Platform |
|------|---------|----------|
| **Mermaid** | Text-to-diagram | Web, VS Code |
| **draw.io** | Professional diagrams | Web, Desktop |
| **Lucidchart** | Enterprise diagrams | Web |
| **PlantUML** | UML diagrams | Desktop |
| **Graphviz** | Graph visualization | Desktop |

### 12.2 Mermaid Editor

**Online Editor:** https://mermaid.live/

**VS Code Extension:**
1. Install "Markdown Preview Mermaid Support" extension
2. Open .md file with Mermaid code
3. Preview with Ctrl+Shift+V

### 12.3 draw.io

**Online:** https://app.diagrams.net/

**Desktop:**
```bash
# Download draw.io desktop
wget https://github.com/jgraph/drawio-desktop/releases/download/v14.6.13/drawio-amd64-14.6.13.deb
sudo dpkg -i drawio-amd64-14.6.13.deb
```

---

## 13. EXPORTING DIAGRAMS

### 13.1 Export from Mermaid

```bash
# Using mermaid-cli
npm install -g @mermaid-js/mermaid-cli
mmdc -i input.mmd -o output.png
mmdc -i input.mmd -o output.svg
mmdc -i input.mmd -o output.pdf
```

### 13.2 Export from draw.io

1. Open diagram in draw.io
2. File → Export As
3. Select format (PNG, SVG, PDF)
4. Set resolution
5. Click Export

---

## 14. DIAGRAM STANDARDS

### 14.1 Naming Conventions

- Use clear, descriptive names
- Follow consistent naming across diagrams
- Use standard abbreviations (DB, API, UI, etc.)
- Include version numbers

### 14.2 Color Coding

| Color | Meaning |
|-------|---------|
| Blue | User/Client |
| Green | Database/Storage |
| Orange | External Services |
| Purple | Security |
| Gray | Infrastructure |

### 14.3 Layout Guidelines

- Left-to-right flow for processes
- Top-to-bottom for hierarchies
- Consistent spacing
- Avoid crossing lines
- Use grouping for related components

---

## 15. MAINTENANCE

### 15.1 Version Control

- Store diagram source files in Git
- Use descriptive commit messages
- Tag major diagram versions
- Document changes in commit history

### 15.2 Review Process

- Review diagrams before publishing
- Get stakeholder approval
- Document review feedback
- Update diagrams based on feedback

### 15.3 Updates

- Update diagrams when architecture changes
- Keep diagrams in sync with code
- Document diagram changes
- Communicate updates to team

---

## 16. RESOURCES

### 16.1 Documentation

- Mermaid Docs: https://mermaid-js.github.io/mermaid/
- draw.io Docs: https://www.diagrams.net/doc/
- UML Standards: https://www.uml.org/

### 16.2 Templates

- System Architecture Template
- Database ERD Template
- Flowchart Template
- Sequence Diagram Template

---

> **End of Documentation**
