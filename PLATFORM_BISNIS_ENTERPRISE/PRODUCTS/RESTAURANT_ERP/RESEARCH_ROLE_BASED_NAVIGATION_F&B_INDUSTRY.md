# EBP Restaurant ERP - Penelitian Navigasi Berbasis Role untuk Industri F&B

**Tanggal**: 2026-07-06  
**Tujuan**: Analisis mendalam navigasi/menu berbasis role untuk aplikasi restoran berdasarkan praktik terbaik industri F&B dan kurasi internet  
**Produk**: EBP Restaurant & Cafe ERP  
**Platform**: Enterprise Business Platform (EBP)

---

## Daftar Isi

1. [Executive Summary](#1-executive-summary)
2. [Analisis Sistem EBP Saat Ini](#2-analisis-sistem-ebp-saat-ini)
3. [Praktik Terbaik Industri F&B](#3-praktik-terbaik-industri-fb)
4. [Insight Operasional Industri](#4-insight-operasional-industri)
5. [Hierarki Role Industri Restoran](#5-hierarki-role-industri-restoran)
6. [Rekomendasi Navigasi per Level dan Role](#6-rekomendasi-navigasi-per-level-dan-role)
7. [Matriks Akses Menu Lengkap](#7-matriks-akses-menu-lengkap)
8. [Fitur POS Berbasis Role](#8-fitur-pos-berbasis-role)
9. [Manajemen Inventory Berbasis Role](#9-manajemen-inventory-berbasis-role)
10. [Manajemen Staff Berbasis Role](#10-manajemen-staff-berbasis-role)
11. [Fleksibilitas Scope Bisnis](#11-fleksibilitas-scope-bisnis)
12. [Segmentasi Industri](#12-segmentasi-industri)
13. [Panduan Implementasi](#13-panduan-implementasi)
14. [Studi Kasus Industri](#14-studi-kasus-industri)
15. [Rekomendasi Masa Depan](#15-rekomendasi-masa-depan)

---

## 1. Executive Summary

Penelitian ini menggabungkan analisis sistem EBP Restaurant ERP yang ada dengan praktik terbaik industri F&B global untuk menghasilkan rekomendasi navigasi berbasis role yang komprehensif. Dokumen ini mencakup:

- **3 Level Hierarki**: Platform Owner, Tenant Owner, Tenant Member
- **7+ Role Utama**: Administrator, Restaurant Manager, Waiter, Kitchen Staff, Cashier, Inventory Manager, Host/Hostess
- **28 Modul**: Dari operasional dasar hingga fitur enterprise canggih
- **8 Tipe Restoran**: Restaurant, Cafe, Bar/Pub, Food Court, Catering, Fast Food, Fine Dining, Coffee Shop
- **Pola Navigasi**: Berdasarkan kebutuhan spesifik setiap role dalam ekosistem F&B

**Temuan Utama**:
1. Sistem EBP saat ini sudah memiliki struktur role yang baik, namun perlu penyempurnaan pada granularitas akses
2. Industri F&B memiliki hierarki yang jelas dengan tanggung jawab yang berbeda antara Front-of-House dan Back-of-House
3. Multi-tenant SaaS membutuhkan pemisahan akses yang ketat antara level platform dan level tenant
4. Navigasi harus disesuaikan dengan tipe restoran (QSR vs Fine Dining vs Cafe)

---

## 2. Analisis Sistem EBP Saat Ini

### 2.1 Arsitektur Multi-Tenant

EBP Restaurant ERP menggunakan arsitektur multi-tenant dengan 3 level:

```
Platform Owner (EBP Company)
    ↓
Tenant Owner (Restaurant Owner)
    ↓
Tenant Member (Restaurant Staff)
```

### 2.2 Role yang Sudah Ada

Berdasarkan `seed_data.php`, saat ini ada 6 role:

| Role Code | Nama Role | Deskripsi | Permissions |
|-----------|-----------|-----------|-------------|
| ADMIN | Administrator | Full system access | 15 permissions (semua modul) |
| KASIR | Kasir | Cashier - handle sales and payments | 5 permissions (sales, order, payment, customer) |
| KOKI | Koki | Chef - kitchen operations | 3 permissions (kitchen, order) |
| WAITER | Waiter | Waiter - table service | 4 permissions (table, reservation, order, sales view) |
| MANAGER | Manager | Manager - oversight and reports | 5 permissions (reports, settings, user, inventory, sales view) |
| STOK | Stok | Inventory Manager | 2 permissions (inventory, menu) |

### 2.3 Modul yang Tersedia (28 Modul)

Auth, Accounting, AI, CRM, Delivery, Enterprise, HR, Integration, Inventory, Kiosk, Kitchen, Location, Maintenance, Menu, Mobile, Offline, Quality, Report, Reservation, Sales, Settings, SupplyChain, Sustainability, Table, Tenant, User, WhatsApp, Loyalty

### 2.4 Gap Analysis

**Kelebihan**:
- ✅ Struktur multi-tenant yang solid
- ✅ Role-based access control dasar sudah ada
- ✅ Modul yang komprehensif (28 modul)
- ✅ Middleware untuk permission checking

**Kekurangan**:
- ❌ Granularitas permission masih kasar (per modul, bukan per action)
- ❌ Tidak ada differentiation antara tipe restoran (QSR vs Fine Dining)
- ❌ Role tambahan industri belum tercover (Bartender, Barista, Sommelier, dll)
- ❌ Tidak ada konsep "read-only" vs "read-write" per modul

---

## 3. Praktik Terbaik Industri F&B

### 3.1 Prinsip Desain Navigasi Restaurant POS

Berdasarkan riset dari Wealthon POS, Selected Group, dan Table ERP:

#### 3.1.1 "Glance-and-Act" Pattern
- Dashboard harus menampilkan KPI penting dalam sekilas pandang
- Quick actions harus mudah diakses (1-2 tap)
- Status indicators harus visual dan jelas

#### 3.1.2 Role-Specific Workflows
- **Manager**: Fokus pada oversight, reports, dan decision-making
- **Server/Waiter**: Fokus pada order taking, table management, customer service
- **Kitchen**: Fokus pada KDS (Kitchen Display System), preparation timing
- **Cashier**: Fokus pada payment processing, bill settlement

### 3.2 Security Best Practices

Berdasarkan riset dari Toast, Fishbowl, dan EatlyPOS:

#### 3.2.1 Role-Based Access Control (RBAC)
- Setiap role hanya memiliki akses ke fitur yang diperlukan
- Principle of least privilege
- Audit trail untuk semua actions
- Segregasi duties (pemisahan tanggung jawab)

---

## 4. Insight Operasional Industri

Berdasarkan riset mendalam tentang operasional industri F&B (RESEARCH_01_INDUSTRY_OVERVIEW.md):

### 4.1 Landscape Keuangan Industri

**Margin Keuntungan**:
- **Restoran Independen**: 3-9% net profit
- **Chain Restaurants**: 5-12% net profit
- **Target Food Cost**: 28-35% dari revenue untuk full-service restaurants
- **Target Labor Cost**: 25-35% dari revenue
- **Prime Cost (Food + Labor)**: Harus di bawah 60%

**Revenue Leaks**:
Rata-rata restoran kehilangan $150,000–$400,000 tahunan dari waste operasional yang dapat dicegah:
- No-shows dan last-minute cancellations
- Food waste (4-10% dari inventory cost)
- Kitchen inefficiency mengurangi table turns
- Manual staff scheduling menciptakan overtime
- Lost customer retention

### 4.2 Operasional Front-of-House

**Reservation Management**:
- **No-show Rates**: 15-30% di casual dining, 8-15% di fine dining
- **Revenue Impact**: 4-top no-show = $80–$200 lost revenue
- **Annual Loss**: 200-seat restaurant dengan 80 reservations/night, 20% no-show rate = $240,000–$600,000 tahunan

**Table Management**:
- **Table Turnover**: Kritikal untuk revenue optimization
- **Floor Plan Optimization**: AI-driven seating algorithms mempertimbangkan 10,000+ kombinasi per detik
- **Waitlist Management**: Predictive waitlists mengurangi abandonment
- **Real-time Status Tracking**: Open, seated, dirty, next-ready tables

### 4.3 Operasional Back-of-House

**Kitchen Workflow**:
- **Order Flow**: POS → Kitchen Display System (KDS) → Preparation → Expo → Service
- **Station Load Balancing**: Mendistribusikan work across kitchen stations
- **Prep Sequencing**: Optimizing ingredient preparation timing
- **Bottleneck Identification**: Real-time monitoring kitchen constraints

**Food Preparation Standards**:
- **Recipe Standardization**: Written specs dengan photos untuk consistency
- **Portion Control**: Menggunakan scales untuk accuracy
- **Timing Protocols**: Standard cook times untuk setiap dish
- **Quality Control**: Plating dan presentation standards

### 4.4 Inventory Management

**Food Cost Control**:
- **Weekly Cycle Counts**: High-value items (proteins, dairy, seafood, alcohol)
- **Monthly Full Counts**: Complete inventory assessment
- **PAR Levels**: Minimum quantity untuk maintain sebelum reordering
- **FIFO Enforcement**: First-in, first-out rotation system

**Waste Reduction**:
- **Spoilage Prevention**: Temperature control dan proper rotation
- **Prep Waste Reduction**: Accurate forecasting dan dynamic prep lists
- **Plate Waste Management**: Portion control dan plating optimization
- **Over-ordering Prevention**: Data-driven purchasing decisions

### 4.5 Staff Management

**Scheduling**:
- **Demand-based Scheduling**: Align staff dengan actual demand patterns
- **Skill Level Matching**: Appropriate staff untuk setiap service period
- **Labor Law Compliance**: Maximum consecutive shifts, minimum rest periods
- **Cost Optimization**: Junior staff pada slower periods, experienced pada peak

**Training**:
- **Menu Knowledge**: Ingredients, preparation methods, pairings
- **Service Standards**: Behavior dan attire matching restaurant ambiance
- **Compliance Training**: Sanitation, food safety, allergen management
- **Technology Training**: POS systems dan digital tools

### 4.6 Key Performance Indicators (KPIs)

- **Food Cost Percentage**: (Cost of Ingredients ÷ Selling Price) × 100
- **Labor Cost Percentage**: Harus di bawah 30%
- **Prime Cost**: Food + Labor, target di bawah 60%
- **Average Order Value (AOV)**: Revenue per customer transaction
- **Table Turn Rate**: Number of seatings per table per service
- **Inventory Turnover**: 4-8 times per month (1-2 weeks of stock)

---

## 5. Hierarki Role Industri Restoran

### 5.1 Struktur Organisasi Restoran Standar

Berdasarkan riset dari Sling dan TheOrgChart:

```
Owner/Proprietor
    ↓
General Manager
    ↓
├── Front-of-House Manager
│   ├── Host/Hostess
│   ├── Server/Waiter
│   ├── Bartender
│   ├── Busser
│   └── Food Runner
└── Back-of-House Manager (Kitchen Manager)
    ├── Executive Chef
    ├── Sous Chef
    ├── Line Cooks (Chef de Partie)
    ├── Prep Cooks
    ├── Pastry Chef
    ├── Dishwasher
    └── Stocker
```

### 5.2 Role Industri dan Tanggung Jawab

#### 5.2.1 Management Level

| Role | Tanggung Jawab Utama | Kebutuhan Sistem |
|------|---------------------|------------------|
| **Owner** | Overall business strategy, financial oversight | Full access, reports, accounting, HR, analytics |
| **General Manager** | Day-to-day operations, staff management | Operations oversight, reports, HR, inventory, settings |
| **Assistant Manager** | Support GM, scheduling, inventory coordination | Limited admin access, scheduling, inventory view |
| **Food & Beverage Manager** | Menu development, pricing, quality control | Menu management, pricing, quality, inventory |
| **Kitchen Manager** | Kitchen operations, staff supervision, food quality | Kitchen management, inventory, staff scheduling |

#### 5.2.2 Back-of-House (Kitchen)

| Role | Tanggung Jawab Utama | Kebutuhan Sistem |
|------|---------------------|------------------|
| **Executive Chef** | Menu development, kitchen leadership, quality standards | Menu management, recipe, inventory (ingredients), kitchen oversight |
| **Sous Chef** | Second-in-command, line supervision, expediting | Kitchen orders, inventory view, staff coordination |
| **Line Cook** | Station-specific food preparation | Kitchen display (KDS), recipe view, inventory (ingredients) |
| **Prep Cook** | Ingredient preparation, mise en place | Recipe view, inventory (ingredients) |
| **Pastry Chef** | Dessert preparation | Menu (dessert section), recipe, inventory |
| **Dishwasher** | Cleaning dishes, kitchen sanitation | - (minimal system access) |
| **Stocker** | Inventory receiving, stocking | Inventory (receiving), purchase orders |

#### 5.2.3 Front-of-House (Service)

| Role | Tanggung Jawab Utama | Kebutuhan Sistem |
|------|---------------------|------------------|
| **Host/Hostess** | Greeting, seating, reservations, phone | Reservations, table management, waitlist |
| **Server/Waiter** | Order taking, customer service, payment | Orders, tables, menu (view), customer profiles |
| **Bartender** | Beverage preparation, bar service | Orders (bar), inventory (beverages), menu (drinks) |
| **Barback** | Bartender assistant, bar stocking | Inventory (bar), supplies |
| **Busser** | Table clearing, resetting | - (minimal system access) |
| **Food Runner** | Food delivery from kitchen to table | Kitchen orders (view), table status |
| **Sommelier** | Wine service, wine selection | Menu (wine), customer preferences |
| **Barista** | Coffee preparation | Menu (coffee), inventory (coffee beans) |

---

## 6. Rekomendasi Navigasi per Level dan Role

### 6.1 Level 1: Platform Owner (EBP Company)

**Scope**: Seluruh platform, semua tenant, enterprise-level decisions

**Recommended Menu Tabs**:

```javascript
PLATFORM_OWNER_MENU = [
    'overview', 'enterprise', 'tenant', 'users', 'settings',
    'reports', 'accounting', 'hr', 'crm', 'ai', 'integration',
    'quality', 'supplychain', 'sustainability', 'location',
    'maintenance', 'whatsapp'
]
```

**Excluded Tabs** (operational, not platform-level):
- ❌ menu, tables, orders, inventory, kitchen, reservation, delivery

---

### 6.2 Level 2: Tenant Owner (Restaurant Owner)

**Scope**: Single tenant (restaurant), full business control

**Recommended Menu Tabs**:

```javascript
TENANT_OWNER_MENU = [
    'overview', 'menu', 'tables', 'orders', 'inventory', 'kitchen',
    'users', 'settings', 'accounting', 'reservation', 'crm', 'reports',
    'hr', 'delivery', 'ai', 'integration', 'quality', 'supplychain',
    'sustainability', 'location', 'maintenance', 'whatsapp', 'loyalty'
]
```

**Excluded Tabs** (platform-level only):
- ❌ enterprise, tenant

---

### 6.3 Level 3: Tenant Member (Restaurant Staff)

#### 6.3.1 Administrator (Tenant Admin)

```javascript
ADMINISTRATOR_MENU = [
    'overview', 'menu', 'tables', 'orders', 'inventory', 'kitchen',
    'users', 'settings', 'accounting', 'reservation', 'crm', 'reports',
    'hr', 'delivery', 'ai', 'integration', 'quality', 'supplychain',
    'sustainability', 'location', 'maintenance', 'whatsapp', 'loyalty'
]
```

#### 6.3.2 Restaurant Manager

```javascript
RESTAURANT_MANAGER_MENU = [
    'overview', 'menu', 'tables', 'orders', 'inventory', 'kitchen',
    'reservation', 'reports', 'hr', 'crm', 'delivery', 'supplychain',
    'quality', 'accounting'
]
```

**Excluded**: enterprise, tenant, settings, ai, integration, sustainability, location, maintenance, whatsapp

#### 6.3.3 Waiter / Server

```javascript
WAITER_MENU = [
    'overview', 'tables', 'orders', 'reservation', 'menu'
]
```

#### 6.3.4 Kitchen Staff / Chef

```javascript
KITCHEN_STAFF_MENU = [
    'overview', 'kitchen', 'orders', 'inventory', 'menu'
]
```

#### 6.3.5 Cashier

```javascript
CASHIER_MENU = [
    'overview', 'orders', 'accounting', 'reports', 'tables', 'menu'
]
```

#### 6.3.6 Inventory Manager

```javascript
INVENTORY_MANAGER_MENU = [
    'overview', 'inventory', 'supplychain', 'quality', 'orders', 'reports', 'menu'
]
```

#### 6.3.7 Host / Hostess

```javascript
HOST_HOSTESS_MENU = [
    'overview', 'tables', 'reservation', 'orders', 'menu'
]
```

#### 6.3.8 Bartender

```javascript
BARTENDER_MENU = [
    'overview', 'orders', 'inventory', 'menu', 'tables'
]
```

#### 6.3.9 Barista

```javascript
BARISTA_MENU = [
    'overview', 'orders', 'inventory', 'menu', 'loyalty'
]
```

#### 6.3.10 Sommelier (Fine Dining Only)

```javascript
SOMMELIER_MENU = [
    'overview', 'menu', 'inventory', 'crm', 'orders'
]
```

---

## 7. Matriks Akses Menu Lengkap

### 7.1 Matriks Akses Tab (Visibility)

| Tab | Platform Owner | Tenant Owner | Admin | Manager | Waiter | Kitchen | Cashier | Inventory | Host | Bartender | Barista | Sommelier |
|-----|---------------|--------------|-------|---------|--------|---------|---------|-----------|------|-----------|---------|-----------|
| **overview** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| **enterprise** | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **tenant** | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **users** | ✅ | ✅ | ✅ | 👁️ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **settings** | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **menu** | ❌ | ✅ | ✅ | 👁️ | 👁️ | 👁️ | 👁️ | 👁️ | 👁️ | 👁️ | 👁️ | 👁️ |
| **tables** | ❌ | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ | ✅ | ✅ | ❌ | ❌ |
| **orders** | ❌ | ✅ | ✅ | ✅ | ✅ | 👁️ | ✅ | 👁️ | 👁️ | ✅ | ✅ | ✅ |
| **inventory** | ❌ | ✅ | ✅ | ✅ | ❌ | 👁️ | ❌ | ✅ | ❌ | ✅ | ✅ | ✅ |
| **kitchen** | ❌ | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **reservation** | ❌ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ |
| **accounting** | ✅ | ✅ | ✅ | 👁️ | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **crm** | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| **reports** | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **hr** | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **delivery** | ❌ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **ai** | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **integration** | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **quality** | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **supplychain** | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **sustainability** | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **location** | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **maintenance** | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **whatsapp** | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **loyalty** | ❌ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ | ❌ |

**Legend**:
- ✅ = Full access (view + create + edit + delete)
- 👁️ = Read-only access
- ❌ = No access (tab hidden)

---

## 8. Fitur POS Berbasis Role

Berdasarkan riset POS Systems Features (RESEARCH_03_POS_SYSTEMS_FEATURES.md):

### 8.1 Order Management per Role

**Order Entry Features**:
- **Menu Navigation**: Intuitive category dan item browsing
- **Modifier Handling**: Complex customization options (rare, medium, well-done; substitutions; add-ons)
- **Special Instructions**: Free-text notes untuk kitchen
- **Combo Meals**: Bundled items dengan automatic pricing
- **Split Checks**: Multiple payment methods per table
- **Course Ordering**: Appetizers, mains, desserts dengan timing control

**Role-Specific Order Access**:
- **Waiter**: Full order entry, table assignment, payment processing
- **Cashier**: Order view, payment processing, refund handling
- **Kitchen**: Order view (KDS), status updates, timing
- **Bartender**: Bar order entry, beverage management
- **Barista**: Coffee order entry, beverage management
- **Sommelier**: Wine order entry, wine pairing suggestions

### 8.2 Payment Processing per Role

**Payment Methods**:
- Credit/Debit Cards (EMV chip, magnetic stripe, contactless NFC)
- Mobile Wallets (Apple Pay, Google Pay, Samsung Pay)
- Digital Payments (Venmo, PayPal, Cash App)
- Gift Cards (House-branded dan third-party)
- Cash Management (Cash drawer tracking dan reconciliation)
- Split Payments (Multiple cards, cash + card, room charges)

**Role-Specific Payment Access**:
- **Cashier**: Full payment processing, tip handling, cash drawer management
- **Waiter**: Payment processing, tip handling, receipt options
- **Manager**: Payment overrides, refund authorization, void approval
- **Other Roles**: No payment access (view only)

### 8.3 Table & Floor Management per Role

**Table Tracking Features**:
- Visual Floor Plan (Digital restaurant layout)
- Table Status (Open, seated, ordering, eating, dirty, reserved)
- Server Sections (Assigned server areas dengan load balancing)
- Turn Time Tracking (Duration dari setiap table seating)
- Table Merging (Combining tables untuk large parties)
- Table Splitting (Separating checks untuk large parties)

**Role-Specific Table Access**:
- **Host/Hostess**: Full table management, reservation assignment, waitlist
- **Waiter**: Table assignment, status updates, seating
- **Manager**: Floor plan editing, section management, override access
- **Bartender**: Bar table management
- **Other Roles**: View only or no access

### 8.4 Menu Management per Role

**Menu Configuration Features**:
- Category Organization (Logical menu structure)
- Item Modifiers (Nested customization options)
- Pricing Tiers (Happy hour, lunch, dinner pricing)
- Availability Control (86'd items, seasonal items)
- Multi-location Menus (Centralized management dengan local overrides)
- Recipe Costing (Ingredient-level cost tracking)

**Role-Specific Menu Access**:
- **Manager**: Full menu management, pricing, availability
- **Chef**: Menu view, recipe management, ingredient tracking
- **Waiter**: Menu view (read-only)
- **Bartender**: Beverage menu view, bar inventory
- **Barista**: Coffee menu view, coffee inventory
- **Sommelier**: Wine menu view, wine inventory
- **Cashier**: Menu view (read-only)

### 8.5 Inventory Management per Role

**Stock Tracking Features**:
- Real-Time Deduction (Automatic inventory updates dari sales)
- Recipe-based Depletion (Ingredient-level tracking)
- PAR Level Alerts (Reorder notifications)
- Vendor Management (Supplier catalog dan pricing)
- Purchase Order Generation (Automated ordering suggestions)
- Waste Tracking (Spoilage, prep waste, plate waste logging)

**Role-Specific Inventory Access**:
- **Inventory Manager**: Full inventory management, ordering, reporting
- **Chef**: Inventory view (ingredients), recipe costing
- **Bartender**: Beverage inventory view, bar stock
- **Barista**: Coffee inventory view
- **Manager**: Inventory reports, variance analysis
- **Other Roles**: No access or view only

### 8.6 Staff Management per Role

**Employee Management Features**:
- Time Clock (Integrated clock-in/clock-out)
- Scheduling (Shift planning dan assignment)
- Role-based Access (Permission levels by position)
- Performance Tracking (Sales per hour, tips, accuracy)
- Training Modules (Onboarding dan certification tracking)
- Communication (Internal messaging dan announcements)

**Role-Specific Staff Access**:
- **Manager**: Full staff management, scheduling, performance tracking
- **HR**: Staff records, payroll, compliance
- **Staff**: Own profile view, schedule view, time clock
- **Other Roles**: No access

---

## 9. Manajemen Inventory Berbasis Role

Berdasarkan riset Inventory Management (RESEARCH_05_INVENTORY_MANAGEMENT.md):

### 9.1 Inventory Counting Procedures per Role

**Count Frequency Strategy**:
- **High-Value Items**: Daily spot-checks (steaks, avocados, expensive proteins)
- **High-Volume Items**: Weekly cycle counts (proteins, dairy, seafood, alcohol)
- **All Items**: Monthly full counts
- **Multi-Location**: Daily spot-checks pada most expensive ingredients

**Role-Specific Counting Access**:
- **Inventory Manager**: Full counting access, variance analysis, PAR management
- **Chef**: Ingredient counting, recipe costing, waste logging
- **Bartender**: Beverage counting, bar inventory
- **Manager**: Count approval, variance review, report access
- **Other Roles**: No access

### 9.2 PAR Level Management per Role

**PAR Level Definition**:
- Purpose: Minimum quantity dari setiap ingredient needed untuk last through ordering cycle
- Function: Safety net preventing stockouts dan costly last-minute reorders
- Trigger: When stock drops ke PAR, automatic reorder initiated

**Role-Specific PAR Access**:
- **Inventory Manager**: PAR level setting, adjustment, automated ordering
- **Manager**: PAR level approval, variance review
- **Chef**: PAR level suggestions based on menu
- **Other Roles**: View only

### 9.3 Waste Tracking per Role

**Waste Categories**:
- Spoilage (Ingredients expiring due ke wrong temperatures atau poor FIFO)
- Plate Waste (Food left pada plates due ke large portion sizes)
- Preparation Waste (Trimmings dan kitchen mistakes)
- Over-Ordering (Stock purchased but left unused)

**Role-Specific Waste Access**:
- **Inventory Manager**: Full waste tracking, analysis, reporting
- **Chef**: Waste logging, pattern identification, corrective action
- **Kitchen Staff**: Waste logging (daily operations)
- **Manager**: Waste reports, cost analysis
- **Other Roles**: No access

### 9.4 Supplier Management per Role

**Multi-Supplier Strategy**:
- Risk Mitigation: 2-3 vendors per critical category
- Competitive Tension: Multiple suppliers create pricing leverage
- Backup Capacity: Secondary sources untuk delivery failures
- Quality Comparison: Ability ke compare quality across suppliers

**Role-Specific Supplier Access**:
- **Inventory Manager**: Full supplier management, pricing negotiation, performance tracking
- **Manager**: Supplier approval, contract review
- **Chef**: Supplier quality feedback, ingredient requests
- **Other Roles**: No access

---

## 10. Manajemen Staff Berbasis Role

Berdasarkan riset Staff Management & Training (RESEARCH_06_STAFF_MANAGEMENT_TRAINING.md):

### 10.1 Staff Scheduling per Role

**Demand-Based Scheduling**:
- Historical Analysis (Review sales by hour dan day-of-week)
- Reservation Forecasting (Integrate booking data untuk staffing needs)
- Walk-in Prediction (Estimate walk-in traffic based pada patterns)
- Event Consideration (Adjust untuk local events, holidays, weather)
- Seasonal Adjustment (Modify schedules based pada seasonal demand)

**Role-Specific Scheduling Access**:
- **Manager**: Full scheduling access, staff assignment, overtime approval
- **HR**: Schedule compliance, labor law monitoring
- **Staff**: Own schedule view, shift swap requests
- **Other Roles**: No access

### 10.2 Staff Training per Role

**Onboarding Process**:
- Orientation (Company culture, values, dan expectations)
- Menu Knowledge (Ingredients, preparation methods, pairings)
- Service Standards (Behavior, attire, dan ambiance alignment)
- System Training (POS, reservation, dan other technology systems)
- Safety Training (Workplace safety dan emergency procedures)
- Shadow Period (Mentored on-the-job training)

**Role-Specific Training Access**:
- **Manager**: Full training management, progress tracking, certification
- **HR**: Training records, compliance monitoring
- **Staff**: Own training modules, progress view
- **Other Roles**: No access

### 10.3 Performance Management per Role

**Key Performance Indicators**:
- Sales per Hour (Revenue generation efficiency)
- Average Check Size (Upselling dan cross-selling effectiveness)
- Table Turn Time (Service speed dan efficiency)
- Customer Satisfaction (Review scores dan feedback)
- Accuracy Rate (Order accuracy dan mistake frequency)
- Attendance dan Punctuality (Reliability metrics)

**Role-Specific Performance Access**:
- **Manager**: Full performance tracking, feedback systems, recognition
- **HR**: Performance records, compensation management
- **Staff**: Own performance metrics, feedback view
- **Other Roles**: No access

---

## 11. Fleksibilitas Scope Bisnis

Berdasarkan riset Business Scope Flexibility (RESEARCH_33_FB_BUSINESS_SCOPE_FLEXIBILITY.md):

### 11.1 Dimension 1: Scale & Corporate Structure

**Business Types**:
- Home-Based Operations (Single operator, no physical storefront)
- Small Independent Restaurants (1-3 locations, 5-50 employees)
- Regional Chains (4-50 locations, 50-500 employees)
- National Corporations (50-500+ locations, 500-5000+ employees)
- International Corporations (Multi-country, multi-currency, multi-language)

**Role Adaptation by Scale**:
- **Home-Based**: Simplified roles (Owner, Staff)
- **Small Independent**: Standard roles (Manager, Waiter, Chef, Cashier)
- **Regional Chains**: Expanded roles (Regional Manager, Inventory Manager)
- **National Corporations**: Enterprise roles (Corporate roles, specialized positions)
- **International Corporations**: Global roles (Country Manager, Regional Director)

### 11.2 Dimension 2: Physical Presence

**Physical Presence Types**:
- No Physical Building (Virtual/Kitchen-Only)
- Small Physical Space (10-50 seats)
- Medium Physical Space (50-150 seats)
- Large Physical Space (150-500+ seats)
- International Standard Facilities (Multiple specialized rooms)

**Role Adaptation by Physical Presence**:
- **No Physical Building**: No table management, no reservation roles
- **Small Physical Space**: Simplified table management, limited host role
- **Medium Physical Space**: Standard table management, full host role
- **Large Physical Space**: Advanced table management, multiple host roles
- **International Standard**: Facility management roles, specialized positions

### 11.3 Dimension 3: Cuisine Type

**Cuisine Types**:
- Traditional/Local Cuisine (Regional specialties, traditional recipes)
- International Cuisine (Global dishes, diverse ingredients)
- Fusion Cuisine (Cross-cultural combinations, innovative approaches)

**Role Adaptation by Cuisine Type**:
- **Traditional**: Standard roles, traditional recipe management
- **International**: Sommelier role, international ingredient tracking
- **Fusion**: Creative roles, experimental workflow support

### 11.4 Dimension 4: Halal/Non-Halal

**Halal Status Types**:
- Halal-Only Operations
- Non-Halal Operations
- Mixed Operations (Halal + Non-Halal)

**Role Adaptation by Halal Status**:
- **Halal-Only**: Halal certification tracking, halal workflow enforcement
- **Non-Halal**: No special requirements
- **Mixed**: Segregated preparation roles, halal compliance roles

### 11.5 Dimension 5: Target Market

**Target Market Types**:
- Mass Market (General consumption, broad appeal)
- Niche Market (Specialized consumption, dietary restrictions)

**Role Adaptation by Target Market**:
- **Mass Market**: Standard roles, volume optimization
- **Niche Market**: Specialized roles (e.g., Vegan Chef, Gluten-Free Specialist)

### 11.6 Dimension 6: Menu Complexity

**Menu Complexity Types**:
- Single Menu Item (One flagship product)
- Limited Menu (5-20 items)
- Extensive Menu (20-100+ items)

**Role Adaptation by Menu Complexity**:
- **Single Menu**: Simplified roles, focused workflow
- **Limited Menu**: Standard roles, efficient operations
- **Extensive Menu**: Specialized roles (e.g., Menu Manager, Category Specialist)

### 11.7 Dimension 7: Product Mix

**Product Mix Types**:
- Food-Only
- Beverage-Only
- Food + Beverage
- Food + Non-Food Products

**Role Adaptation by Product Mix**:
- **Food-Only**: Standard food roles
- **Beverage-Only**: Bartender, Barista, Sommelier roles
- **Food + Beverage**: Combined roles
- **Food + Non-Food**: Retail management roles

---

## 12. Segmentasi Industri

Berdasarkan riset Industry Segments (RESEARCH_31_INDUSTRY_SEGMENTS.md):

### 12.1 Fine Dining

**Segment Characteristics**:
- High-end, full-service restaurants
- Exceptional food quality dan presentation
- Impeccable service standards
- Premium pricing ($100-300+ per person)
- Formal atmosphere dan ambiance

**Service Standards**:
- Tables per server: 3-4
- Back-waiter (commis) required
- Service styles: French, Russian, American classical
- Sommelier presence standard
- Synchronized service mandatory
- Gueridon/tableside service active

**Role-Specific Adaptations**:
- **Sommelier**: Wine service, wine selection, customer preferences
- **Maitre d'**: Guest relations, seating coordination, service oversight
- **Back-waiter**: Table support, service assistance
- **Chef de Rang**: Head waiter, section management
- **Standard roles**: Enhanced permissions, premium features

### 12.2 Quick Service Restaurants (QSR)

**Segment Characteristics**:
- Fast food restaurants
- Speed dan convenience focus
- Limited menu
- Counter service
- Low pricing ($10-20 per person)
- High volume operations

**Operational Characteristics**:
- Leaner operations
- Limited menu enables smaller teams
- Lower labor costs
- Consistent demand
- Scalable model
- Efficient operations

**Role-Specific Adaptations**:
- **Drive-thru Operator**: Drive-thru management, speed tracking
- **Kiosk Manager**: Self-service kiosk oversight
- **Line Cook**: Assembly line production, speed focus
- **Simplified roles**: Fewer specialized positions
- **Efficiency focus**: Time-based KPIs

### 12.3 Casual Dining

**Segment Characteristics**:
- Full-service restaurants
- Moderate pricing ($40-80 per person)
- Family-friendly atmosphere
- Table service
- Diverse menu
- Relaxed ambiance

**Service Standards**:
- Tables per server: 5-6
- No back-waiter typically
- American plated service
- Optional sommelier
- Best effort synchronization
- Branded uniform

**Role-Specific Adaptations**:
- **Standard roles**: Balanced permissions
- **Family focus**: Kid-friendly features
- **Value focus**: Pricing management
- **Flexibility**: Customization support

### 12.4 Segment Comparison

| Criterion | Fine Dining | Casual Dining | QSR |
|-----------|-------------|---------------|-----|
| Tables per server | 3-4 | 5-6 | 8-12+ |
| Back-waiter | Required | Rare | None |
| Service style | French/Russian/American | American plated | Counter/runner |
| Sommelier | Standard | Optional | None |
| Synchronized service | Mandatory | Best effort | N/A |
| Average check (US) | $100-300+ | $40-80 | $10-20 |
| Gueridon/tableside | Active | Rare | None |

---

## 13. Panduan Implementasi

### 13.1 Langkah Implementasi

#### Phase 1: Database Migration
1. Tambah granular permissions (MENU_CREATE, MENU_EDIT, ORDER_CREATE, dll)
2. Update role_permissions table sesuai matriks akses
3. Tambah role baru (Bartender, Barista, Sommelier)

#### Phase 2: Backend Updates
1. Update PermissionMiddleware untuk granular permission checking
2. Tambah helper functions di controller untuk action-level authorization
3. Update API responses untuk menyertakan user permissions

#### Phase 3: Frontend Updates
1. Buat konfigurasi MENU_ACCESS di JavaScript
2. Implementasi helper functions (canCreate, canEdit, canDelete, canView)
3. Tambah data attributes ke UI elements (data-role-min, data-permission)
4. Implementasi hideElementsByRole function

#### Phase 4: Testing
1. Test setiap role dengan akses yang sesuai
2. Verifikasi backend enforcement
3. Test UI hiding/showing
4. Performance testing

### 13.2 Contoh Implementasi JavaScript

```javascript
// config/menu-access.js
const MENU_ACCESS = {
    PLATFORM_OWNER: {
        tabs: ['overview', 'enterprise', 'tenant', 'users', 'settings', 'reports', 'accounting', 'hr', 'crm', 'ai', 'integration', 'quality', 'supplychain', 'sustainability', 'location', 'maintenance', 'whatsapp']
    },
    TENANT_OWNER: {
        tabs: ['overview', 'menu', 'tables', 'orders', 'inventory', 'kitchen', 'users', 'settings', 'accounting', 'reservation', 'crm', 'reports', 'hr', 'delivery', 'ai', 'integration', 'quality', 'supplychain', 'sustainability', 'location', 'maintenance', 'whatsapp', 'loyalty']
    },
    TENANT_MEMBER: {
        Administrator: ['overview', 'menu', 'tables', 'orders', 'inventory', 'kitchen', 'users', 'settings', 'accounting', 'reservation', 'crm', 'reports', 'hr', 'delivery', 'ai', 'integration', 'quality', 'supplychain', 'sustainability', 'location', 'maintenance', 'whatsapp', 'loyalty'],
        'Restaurant Manager': ['overview', 'menu', 'tables', 'orders', 'inventory', 'kitchen', 'reservation', 'reports', 'hr', 'crm', 'delivery', 'supplychain', 'quality', 'accounting'],
        Waiter: ['overview', 'tables', 'orders', 'reservation', 'menu'],
        'Kitchen Staff': ['overview', 'kitchen', 'orders', 'inventory', 'menu'],
        Cashier: ['overview', 'orders', 'accounting', 'reports', 'tables', 'menu'],
        'Inventory Manager': ['overview', 'inventory', 'supplychain', 'quality', 'orders', 'reports', 'menu'],
        'Host/Hostess': ['overview', 'tables', 'reservation', 'orders', 'menu'],
        Bartender: ['overview', 'orders', 'inventory', 'menu', 'tables'],
        Barista: ['overview', 'orders', 'inventory', 'menu', 'loyalty'],
        Sommelier: ['overview', 'menu', 'inventory', 'crm', 'orders']
    }
};

function getMenuForUser(user) {
    if (user.is_platform_owner) return MENU_ACCESS.PLATFORM_OWNER.tabs;
    if (user.is_tenant_owner) return MENU_ACCESS.TENANT_OWNER.tabs;
    const roleConfig = MENU_ACCESS.TENANT_MEMBER[user.role_name];
    return roleConfig || ['overview'];
}
```

---

## 14. Studi Kasus Industri

### 14.1 Quick Service Restaurant (QSR)

**Karakteristik**:
- High volume, fast service
- Minimal table management
- Focus pada speed dan efficiency

**Role yang relevan**:
- Manager, Cashier, Cook, Drive-thru Operator

**Navigasi yang disederhanakan**:
- Hanya tabs yang essential: overview, orders, menu, inventory, reports
- Quick actions untuk common tasks
- Mobile-first design

### 14.2 Fine Dining

**Karakteristik**:
- Premium experience
- Multi-course dining
- Personalized service

**Role tambahan**:
- Sommelier, Maitre d'

**Navigasi yang diperluas**:
- Advanced CRM dengan customer preferences
- Course management
- Wine database
- Table service timing

### 14.3 Cafe / Coffee Shop

**Karakteristik**:
- Loyalty program penting
- Repeat customers
- Quick service

**Role yang relevan**:
- Manager, Barista, Cashier

**Navigasi yang fokus**:
- Loyalty program integration
- Quick order entry
- Customer recognition

---

## 15. Rekomendasi Masa Depan

### 15.1 Short-term (1-3 bulan)

1. **Implement granular permissions**
   - Tambah action-level permissions
   - Update PermissionMiddleware
   - Update role_permissions

2. **Implement role-based menu hiding**
   - Frontend menu configuration
   - UI element hiding based on role
   - Testing untuk semua role

3. **Tambah role tambahan**
   - Bartender, Barista, Sommelier
   - Update seed_data.php

### 15.2 Medium-term (3-6 bulan)

1. **Restaurant type differentiation**
   - Konfigurasi per tipe restoran
   - Navigation yang disesuaikan
   - Feature toggles

2. **Advanced permission system**
   - Attribute-based access control (ABAC)
   - Context-aware permissions
   - Temporary access grants

3. **Mobile optimization**
   - Role-specific mobile apps
   - Offline mode dengan role restrictions
   - Push notifications per role

### 15.3 Long-term (6-12 bulan)

1. **AI-powered navigation**
   - Smart menu berdasarkan usage patterns
   - Predictive shortcuts
   - Adaptive UI

2. **Voice commands**
   - Voice-activated actions
   - Hands-free operation untuk kitchen
   - Natural language queries

3. **Advanced analytics**
   - Role performance tracking
   - Workflow optimization
   - Training recommendations

---

## 10. Kesimpulan

Penelitian ini menunjukkan bahwa EBP Restaurant ERP sudah memiliki fondasi yang baik untuk role-based navigation, namun perlu penyempurnaan pada:

1. **Granularitas permissions**: Dari level modul ke level action
2. **Role coverage**: Menambah role industri standar (Bartender, Barista, Sommelier)
3. **Restaurant type adaptation**: Menyesuaikan navigasi berdasarkan tipe restoran
4. **UI/UX optimization**: Mengimplementasikan "glance-and-act" pattern

Dengan mengimplementasikan rekomendasi ini, EBP Restaurant ERP akan menjadi platform yang lebih user-friendly, secure, dan sesuai dengan standar industri F&B global.

---

**Document End**

**Version**: 1.0  
**Last Updated**: 2026-07-06  
**Author**: EBP Research Team  
**Status**: Ready for Implementation
