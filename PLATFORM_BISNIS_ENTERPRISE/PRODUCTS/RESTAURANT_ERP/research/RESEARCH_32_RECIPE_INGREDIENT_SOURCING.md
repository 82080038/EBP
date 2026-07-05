# Research 32: Recipe & Ingredient Sourcing Management

## Overview

This research examines how restaurant ERP systems should manage recipes and ingredients, specifically distinguishing between items that are self-produced, outsourced, or sourced from suppliers. The goal is to ensure RESTAURANT_ERP can handle all sourcing models flexibly.

## Current Application Analysis

### Existing Implementation
The RESTAURANT_ERP backend already includes:
- **MenuRecipe Model** (`modules/Menu/Models/MenuRecipe.php`): Basic recipe management with yield quantity, ingredients linking to inventory items, and cost calculation
- **Recipe Model** (`modules/Menu/Models/Recipe.php`): Simple DTO with recipe_code, yield_quantity, instructions
- **MenuItem Model** (`modules/Menu/Models/MenuItem.php`): Menu items with dietary flags (vegetarian, vegan, gluten_free, spicy), preparation time, and station assignment
- **InventoryItem Model**: Stock tracking with units, PAR levels, and waste logging

### Gaps Identified
1. **No sourcing type classification**: No field to distinguish self-produced vs outsourced vs supplier-sourced
2. **No semi-finished goods tracking**: No intermediate product management (e.g., dough made in-house, sauce from supplier)
3. **No production cost tracking**: No labor time, equipment usage, or overhead allocation for self-produced items
4. **No supplier-to-recipe mapping**: No direct link between supplier contracts and recipe ingredient costs
5. **No allergen cross-contamination tracking**: Only dietary flags exist, no allergen handling per ingredient source
6. **No batch/lot tracking for ingredients**: No traceability from supplier batch to finished dish

## Sourcing Classification Framework

### 1. Self-Produced (In-House Production)
**Definition**: Items prepared entirely from raw ingredients within the restaurant kitchen.

**Sub-categories**:
- **From Scratch**: Raw ingredients transformed into finished dish (e.g., pasta made from flour and eggs)
- **Semi-Finished**: Intermediate products prepared in-house (e.g., stock, dough, sauces)
- **Assembly**: Pre-prepared components assembled in-house (e.g., burger assembly)

**Data Requirements**:
- Production recipe with step-by-step instructions
- Labor time and cost allocation
- Equipment usage tracking
- Yield calculations with waste factors
- Quality checkpoints and testing

**Cost Components**:
- Raw material costs (from inventory)
- Labor costs (time × hourly rate)
- Equipment costs (depreciation, energy)
- Overhead allocation (rent, utilities)
- Waste and spoilage costs

### 2. Outsourced (Third-Party Production)
**Definition**: Items produced by external vendors but branded or customized for the restaurant.

**Sub-categories**:
- **Contract Manufacturing**: Vendor produces to restaurant specifications
- **Co-Packing**: Restaurant provides recipe, vendor produces
- **Private Label**: Vendor produces branded product for restaurant
- **White Label**: Standard vendor product with restaurant branding

**Data Requirements**:
- Vendor contract terms and pricing
- Quality specifications and SLAs
- Delivery schedules and lead times
- Minimum order quantities
- Customization tracking

**Cost Components**:
- Purchase price per unit
- Shipping and handling
- Import duties (if international)
- Quality control costs
- Inventory holding costs

### 3. Supplier-Sourced (Direct Purchase)
**Definition**: Items purchased from suppliers with minimal or no modification.

**Sub-categories**:
- **Raw Ingredients**: Basic ingredients (flour, oil, spices)
- **Pre-Processed**: Pre-cut, pre-cooked, or partially prepared items
- **Finished Products**: Ready-to-serve items (beverages, packaged goods)
- **Non-Food Items**: Consumables, packaging, supplies

**Data Requirements**:
- Supplier information and contracts
- Purchase orders and pricing
- Delivery tracking
- Quality certifications
- Batch/lot numbers for traceability

**Cost Components**:
- Unit purchase price
- Volume discounts
- Shipping and logistics
- Payment terms (early payment discounts)
- Inventory carrying costs

### 4. Mixed Sourcing (Hybrid Model)
**Definition**: Items that combine multiple sourcing methods.

**Examples**:
- **Semi-Finished from Supplier + In-House Assembly**: Pre-made dough, baked in-house
- **Raw Ingredients + Outsourced Components**: House-made sauce with purchased pasta
- **Multiple Suppliers**: Same ingredient from different suppliers for redundancy

**Data Requirements**:
- Component-level sourcing tracking
- Assembly recipe with sourced components
- Cost allocation by sourcing method
- Supplier performance comparison

## Implementation Requirements for RESTAURANT_ERP

### Database Schema Enhancements

#### 1. Inventory Items Table
Add fields:
```sql
ALTER TABLE inventory_items ADD COLUMN sourcing_type ENUM('self_produced', 'outsourced', 'supplier_sourced', 'mixed') DEFAULT 'supplier_sourced';
ALTER TABLE inventory_items ADD COLUMN production_cost DECIMAL(10,2) DEFAULT 0;
ALTER TABLE inventory_items ADD COLUMN labor_cost DECIMAL(10,2) DEFAULT 0;
ALTER TABLE inventory_items ADD COLUMN equipment_cost DECIMAL(10,2) DEFAULT 0;
ALTER TABLE inventory_items ADD COLUMN overhead_cost DECIMAL(10,2) DEFAULT 0;
ALTER TABLE inventory_items ADD COLUMN supplier_id INT NULL;
ALTER TABLE inventory_items ADD COLUMN contract_id INT NULL;
ALTER TABLE inventory_items ADD COLUMN batch_tracking BOOLEAN DEFAULT FALSE;
ALTER TABLE inventory_items ADD COLUMN lot_number VARCHAR(50) NULL;
ALTER TABLE inventory_items ADD COLUMN expiry_date DATE NULL;
ALTER TABLE inventory_items ADD COLUMN allergen_info JSON NULL;
```

#### 2. New Table: Production Recipes
```sql
CREATE TABLE production_recipes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    restaurant_id INT NOT NULL,
    recipe_code VARCHAR(50) UNIQUE NOT NULL,
    recipe_name VARCHAR(255) NOT NULL,
    description TEXT,
    sourcing_type ENUM('self_produced', 'outsourced', 'supplier_sourced', 'mixed') NOT NULL,
    yield_quantity DECIMAL(10,2) NOT NULL,
    yield_unit_id INT NOT NULL,
    preparation_time_minutes INT,
    labor_cost_per_batch DECIMAL(10,2),
    equipment_cost_per_batch DECIMAL(10,2),
    overhead_cost_per_batch DECIMAL(10,2),
    instructions TEXT,
    quality_checkpoints JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id),
    FOREIGN KEY (yield_unit_id) REFERENCES inventory_units(id)
);
```

#### 3. New Table: Production Recipe Ingredients
```sql
CREATE TABLE production_recipe_ingredients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    production_recipe_id INT NOT NULL,
    inventory_item_id INT NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit_id INT NOT NULL,
    cost_per_unit DECIMAL(10,2),
    is_optional BOOLEAN DEFAULT FALSE,
    preparation_notes TEXT,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (production_recipe_id) REFERENCES production_recipes(id),
    FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id),
    FOREIGN KEY (unit_id) REFERENCES inventory_units(id)
);
```

#### 4. New Table: Supplier Contracts
```sql
CREATE TABLE supplier_contracts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    restaurant_id INT NOT NULL,
    supplier_id INT NOT NULL,
    contract_number VARCHAR(50) UNIQUE NOT NULL,
    contract_name VARCHAR(255),
    start_date DATE NOT NULL,
    end_date DATE,
    payment_terms VARCHAR(100),
    minimum_order_quantity DECIMAL(10,2),
    volume_discount_tiers JSON,
    delivery_terms VARCHAR(255),
    quality_requirements TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
);
```

#### 5. New Table: Ingredient Batches
```sql
CREATE TABLE ingredient_batches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    inventory_item_id INT NOT NULL,
    supplier_id INT,
    batch_number VARCHAR(50) NOT NULL,
    lot_number VARCHAR(50),
    production_date DATE,
    expiry_date DATE,
    quantity_received DECIMAL(10,2),
    unit_id INT NOT NULL,
    cost_per_unit DECIMAL(10,2),
    quality_certificate VARCHAR(255),
    allergen_info JSON,
    received_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
    FOREIGN KEY (unit_id) REFERENCES inventory_units(id)
);
```

### API Endpoints

#### Production Recipe Management
- `POST /api/production-recipes` - Create production recipe
- `GET /api/production-recipes` - List production recipes
- `GET /api/production-recipes/{id}` - Get production recipe details
- `PUT /api/production-recipes/{id}` - Update production recipe
- `DELETE /api/production-recipes/{id}` - Deactivate production recipe
- `POST /api/production-recipes/{id}/ingredients` - Add ingredient to recipe
- `PUT /api/production-recipes/{id}/ingredients/{ingredientId}` - Update ingredient
- `DELETE /api/production-recipes/{id}/ingredients/{ingredientId}` - Remove ingredient
- `POST /api/production-recipes/{id}/calculate-cost` - Calculate total production cost

#### Sourcing Management
- `GET /api/inventory-items?sourcing_type={type}` - Filter by sourcing type
- `PUT /api/inventory-items/{id}/sourcing` - Update sourcing information
- `GET /api/supplier-contracts` - List supplier contracts
- `POST /api/supplier-contracts` - Create supplier contract
- `GET /api/ingredient-batches` - Track ingredient batches
- `POST /api/ingredient-batches` - Record new batch
- `GET /api/ingredient-batches/{id}/traceability` - Trace batch to menu items

### Cost Calculation Logic

#### Self-Produced Item Cost
```
Total Cost = (Raw Material Costs) + (Labor Costs) + (Equipment Costs) + (Overhead Costs)
Cost Per Unit = Total Cost / Yield Quantity
```

#### Outsourced Item Cost
```
Total Cost = (Purchase Price) + (Shipping) + (Duties) + (Quality Control)
Cost Per Unit = Total Cost / Quantity
```

#### Supplier-Sourced Item Cost
```
Total Cost = (Unit Price × Quantity) - (Volume Discounts) + (Shipping)
Cost Per Unit = Total Cost / Quantity
```

#### Mixed Sourcing Cost
```
Component Cost = Σ(Component Unit Cost × Component Quantity)
Assembly Cost = Labor + Equipment + Overhead
Total Cost = Component Cost + Assembly Cost
Cost Per Unit = Total Cost / Yield Quantity
```

### User Interface Requirements

#### 1. Recipe Sourcing Configuration
- Dropdown to select sourcing type
- Dynamic form fields based on sourcing type
- Cost breakdown visualization
- Supplier/contract selection for outsourced items

#### 2. Production Recipe Builder
- Drag-and-drop ingredient selection
- Real-time cost calculation
- Yield quantity calculator
- Labor time input with cost estimation
- Equipment selection with cost allocation

#### 3. Batch Tracking Interface
- Batch number scanning
- Expiry date alerts
- Supplier information display
- Traceability chain visualization
- Quality certificate upload

#### 4. Cost Analysis Dashboard
- Cost comparison by sourcing type
- Supplier performance metrics
- Production cost trends
- ROI analysis for in-house vs outsourced

### Integration Points

#### 1. Inventory Module
- Automatic stock depletion based on production recipes
- PAR level calculation considering production schedules
- Waste logging for production batches

#### 2. Procurement Module
- Automatic purchase order generation based on production needs
- Supplier contract pricing integration
- Minimum order quantity optimization

#### 3. Menu Module
- Menu item cost calculation using production recipe costs
- Margin analysis by sourcing type
- Menu engineering based on cost structures

#### 4. Kitchen Module
- Production schedule integration
- Equipment usage tracking
- Labor allocation for production

### Halal Compliance Requirements

#### 1. Ingredient Sourcing
- Halal certification tracking per supplier
- Halal status per ingredient batch
- Halal compliance flag in inventory items
- Non-halal ingredient segregation

#### 2. Production Process
- Halal production workflow tracking
- Equipment halal status (cleaning protocols)
- Cross-contamination prevention
- Halal audit trail

#### 3. Documentation
- Halal certificate management
- Supplier halal compliance verification
- Internal halal audit reports
- Halal status display on menu

## Business Scenarios

### Scenario 1: Bakery Producing Own Bread
- **Sourcing Type**: Self-produced
- **Ingredients**: Flour, yeast, water, salt (supplier-sourced)
- **Production**: Mixed sourcing (ingredients from supplier, production in-house)
- **Cost Tracking**: Raw material + labor + equipment + overhead
- **Batch Tracking**: Flour batch numbers for traceability

### Scenario 2: Restaurant Using Pre-Made Sauces
- **Sourcing Type**: Supplier-sourced
- **Supplier**: Contract with sauce manufacturer
- **Cost Tracking**: Purchase price + shipping
- **Quality Control**: Certificate verification
- **Batch Tracking**: Lot numbers for recall management

### Scenario 3: Ghost Kitchen with Virtual Brands
- **Sourcing Type**: Mixed (some items produced, some outsourced)
- **Production**: Base ingredients prepared in-house, specialty items outsourced
- **Cost Tracking**: Component-level cost allocation
- **Multi-Brand**: Shared ingredients across brands with cost allocation

### Scenario 4: International Chain
- **Sourcing Type**: Mixed by location
- **Local Sourcing**: Fresh ingredients from local suppliers
- **Central Production**: Key items produced centrally and distributed
- **Import**: Specialty items imported with duty tracking
- **Compliance**: Halal, kosher, allergen tracking per region

## Implementation Priority

### Phase 1: Core Sourcing Classification (Critical)
1. Add sourcing_type field to inventory_items
2. Create production_recipes table
3. Create production_recipe_ingredients table
4. Implement basic cost calculation
5. Update inventory API to support sourcing

### Phase 2: Supplier Integration (High)
1. Create supplier_contracts table
2. Implement contract-based pricing
3. Add supplier selection to inventory items
4. Create supplier performance tracking

### Phase 3: Batch Tracking (Medium)
1. Create ingredient_batches table
2. Implement batch number tracking
3. Add expiry date management
4. Create traceability chain

### Phase 4: Advanced Features (Low)
1. Halal compliance tracking
2. Allergen cross-contamination prevention
3. Advanced cost allocation
4. ROI analysis tools

## Key Insights

1. **Sourcing flexibility is critical**: Restaurants use multiple sourcing models simultaneously
2. **Cost transparency is essential**: Understanding true costs requires tracking all cost components
3. **Traceability is mandatory**: Food safety regulations require batch-to-dish traceability
4. **Halal compliance is non-negotiable**: For halal restaurants, complete halal tracking is required
5. **Mixed sourcing is common**: Most restaurants use hybrid sourcing models
6. **Semi-finished goods need special handling**: Intermediate products require unique tracking
7. **Supplier relationships impact costs**: Contract pricing and volume discounts significantly affect margins
8. **Production costs are often overlooked**: Labor, equipment, and overhead costs are frequently underestimated

## Application to RESTAURANT_ERP

### Immediate Actions Required
1. **Database schema updates**: Add sourcing classification fields and new tables
2. **Model updates**: Extend existing models to support sourcing types
3. **API enhancements**: Add endpoints for production recipe and sourcing management
4. **UI development**: Create sourcing configuration interfaces
5. **Cost calculation engine**: Implement comprehensive cost calculation logic

### Long-term Enhancements
1. **AI-powered sourcing optimization**: Recommend optimal sourcing based on cost and quality
2. **Predictive demand for production**: Forecast production needs based on menu sales
3. **Supplier performance analytics**: Track and compare supplier performance
4. **Automated contract management**: Alert for contract renewals and price changes
5. **Blockchain traceability**: Immutable record of ingredient journey from farm to table

## Conclusion

Recipe and ingredient sourcing management is a complex but critical aspect of restaurant operations. RESTAURANT_ERP must support all sourcing models with comprehensive cost tracking, traceability, and compliance management. The proposed implementation provides a flexible framework that can accommodate restaurants of all sizes and types, from home-based operations to international chains.
