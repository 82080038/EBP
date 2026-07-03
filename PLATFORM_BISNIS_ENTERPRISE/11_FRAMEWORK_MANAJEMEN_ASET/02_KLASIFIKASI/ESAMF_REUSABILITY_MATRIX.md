# ESAMF Reusability Matrix

**Document ID:** ESAMF-CLASSIFICATION-003

**Version:** 1.0

**Purpose:** Define the reusability matrix for software components in ESAMF

---

# Overview

The Reusability Matrix provides a systematic approach to assessing and scoring the reusability of software components across the EBP ecosystem. This matrix guides classification decisions and migration priorities.

---

# Reusability Dimensions

## 1. Usage Dimension

### Definition

How widely the component is currently used across products.

### Scoring

```
Score 5 (Universal): Used by ALL products
Score 4 (Widespread): Used by MANY products (5+)
Score 3 (Common): Used by SOME products (3-4)
Score 2 (Limited): Used by FEW products (1-2)
Score 1 (Single): Used by ONE product
```

### Examples

```
Authentication: Score 5 (Universal)
- Used by: All products

Notification System: Score 4 (Widespread)
- Used by: Restaurant, Hotel, Parking, Tourism, Education

Inventory System: Score 3 (Common)
- Used by: Restaurant, Hotel, Farming

Kitchen Display: Score 1 (Single)
- Used by: Restaurant only
```

---

## 2. Genericity Dimension

### Definition

How generic the component's logic is, independent of industry-specific requirements.

### Scoring

```
Score 5 (Pure Generic): No industry-specific logic
Score 4 (Mostly Generic): Minimal industry-specific logic
Score 3 (Partially Generic): Some industry-specific logic
Score 2 (Mostly Specific): Significant industry-specific logic
Score 1 (Fully Specific): Entirely industry-specific logic
```

### Examples

```
Authentication: Score 5 (Pure Generic)
- Logic: Universal authentication, no industry specifics

Notification System: Score 4 (Mostly Generic)
- Logic: Generic notification delivery, minimal industry specifics

Inventory System: Score 3 (Partially Generic)
- Logic: Generic inventory with some industry-specific rules

Kitchen Display: Score 1 (Fully Specific)
- Logic: Entirely restaurant-specific kitchen workflow
```

---

## 3. Configurability Dimension

### Definition

How easily the component can be configured for different contexts without code changes.

### Scoring

```
Score 5 (No Config Needed): Works universally without configuration
Score 4 (Highly Configurable): Extensive configuration options
Score 3 (Moderately Configurable): Some configuration options
Score 2 (Limited Configurability): Few configuration options
Score 1 (Not Configurable): Requires code changes for different contexts
```

### Examples

```
Authentication: Score 5 (No Config Needed)
- Configuration: Universal, no configuration needed

Notification System: Score 4 (Highly Configurable)
- Configuration: Templates, providers, channels configurable

Inventory System: Score 3 (Moderately Configurable)
- Configuration: Inventory rules configurable

Kitchen Display: Score 1 (Not Configurable)
- Configuration: Tied to restaurant workflow, not configurable
```

---

## 4. Complexity Dimension

### Definition

The complexity of the component's logic and implementation.

### Scoring

```
Score 5 (Simple): Simple logic, easy to understand
Score 4 (Moderate): Moderate complexity, well-structured
Score 3 (Complex): Complex logic, but manageable
Score 2 (Very Complex): Very complex, difficult to understand
Score 1 (Extremely Complex): Extremely complex, very difficult
```

### Examples

```
Audit Trail: Score 5 (Simple)
- Complexity: Simple logging logic

Authentication: Score 4 (Moderate)
- Complexity: Moderate authentication logic

Notification System: Score 3 (Complex)
- Complexity: Complex notification delivery logic

Reporting System: Score 2 (Very Complex)
- Complexity: Very complex report generation logic
```

---

## 5. Stability Dimension

### Definition

How stable the component is, how frequently it changes, and how mature it is.

### Scoring

```
Score 5 (Very Stable): Very stable, rarely changes
Score 4 (Stable): Stable, infrequent changes
Score 3 (Moderately Stable): Moderately stable, occasional changes
Score 2 (Unstable): Unstable, frequent changes
Score 1 (Very Unstable): Very unstable, constant changes
```

### Examples

```
Authentication: Score 5 (Very Stable)
- Stability: Very stable, rarely changes

Audit Trail: Score 5 (Very Stable)
- Stability: Very stable, rarely changes

Menu Management: Score 3 (Moderately Stable)
- Stability: Moderately stable, changes with menu updates

Kitchen Display: Score 2 (Unstable)
- Stability: Unstable, changes with workflow updates
```

---

# Reusability Score Calculation

## Formula

```
Reusability Score = (Usage + Genericity + Configurability + Stability) / 4

Note: Complexity is not included in the score calculation but is considered for risk assessment.
```

## Score Interpretation

```
Score 4.5 - 5.0: Core Asset (★★★★★)
Score 3.5 - 4.4: Shared Engine (★★★★☆)
Score 2.5 - 3.4: Domain Shared (★★★☆☆)
Score 1.5 - 2.4: Product Asset (★★☆☆☆)
Score 0.0 - 1.4: Legacy (★☆☆☆☆)
```

---

# Reusability Matrix Examples

## Example 1: Authentication System

```
Usage: 5 (Universal)
Genericity: 5 (Pure Generic)
Configurability: 5 (No Config Needed)
Stability: 5 (Very Stable)
Complexity: 4 (Moderate)

Reusability Score = (5 + 5 + 5 + 5) / 4 = 5.0

Classification: Core Asset (★★★★★)
Destination: 06_CORE_CODE/Authentication
```

## Example 2: Notification System

```
Usage: 4 (Widespread)
Genericity: 4 (Mostly Generic)
Configurability: 4 (Highly Configurable)
Stability: 4 (Stable)
Complexity: 3 (Complex)

Reusability Score = (4 + 4 + 4 + 4) / 4 = 4.0

Classification: Shared Engine (★★★★☆)
Destination: 07_SHARED_ENGINES/NotificationEngine
```

## Example 3: Inventory System

```
Usage: 3 (Common)
Genericity: 3 (Partially Generic)
Configurability: 3 (Moderately Configurable)
Stability: 3 (Moderately Stable)
Complexity: 3 (Complex)

Reusability Score = (3 + 3 + 3 + 3) / 4 = 3.0

Classification: Domain Shared (★★★☆☆)
Destination: 07_SHARED_ENGINES/InventoryEngine
```

## Example 4: Kitchen Display System

```
Usage: 1 (Single)
Genericity: 1 (Fully Specific)
Configurability: 1 (Not Configurable)
Stability: 2 (Unstable)
Complexity: 3 (Complex)

Reusability Score = (1 + 1 + 1 + 2) / 4 = 1.25

Classification: Product Asset (★★☆☆☆)
Destination: PRODUCTS/RESTAURANT_ERP/
```

---

# Component Reusability Assessment

## Assessment Template

```
Component: [Component Name]
Location: [Component Location]
Assessment Date: [Date]

Dimension Scores:
- Usage: [1-5]
- Genericity: [1-5]
- Configurability: [1-5]
- Stability: [1-5]
- Complexity: [1-5]

Reusability Score: [Calculated Score]
Classification: [Core Asset/Shared Engine/Domain Shared/Product Asset/Legacy]
Destination: [EBP Destination]

Rationale:
- Usage Rationale: [Explanation]
- Genericity Rationale: [Explanation]
- Configurability Rationale: [Explanation]
- Stability Rationale: [Explanation]
- Complexity Rationale: [Explanation]

Migration Priority: [High/Medium/Low]
Migration Effort: [Estimated effort]
Migration Risk: [Low/Medium/High]
```

---

# Reusability Thresholds

## Core Asset Threshold

```
Minimum Requirements:
- Usage: ≥ 4 (Widespread)
- Genericity: ≥ 4 (Mostly Generic)
- Configurability: ≥ 4 (Highly Configurable)
- Stability: ≥ 4 (Stable)
- Reusability Score: ≥ 4.5
```

## Shared Engine Threshold

```
Minimum Requirements:
- Usage: ≥ 3 (Common)
- Genericity: ≥ 3 (Partially Generic)
- Configurability: ≥ 3 (Moderately Configurable)
- Stability: ≥ 3 (Moderately Stable)
- Reusability Score: ≥ 3.5
```

## Domain Shared Threshold

```
Minimum Requirements:
- Usage: ≥ 2 (Limited)
- Genericity: ≥ 2 (Mostly Specific)
- Configurability: ≥ 2 (Limited Configurability)
- Stability: ≥ 2 (Unstable)
- Reusability Score: ≥ 2.5
```

## Product Asset Threshold

```
Minimum Requirements:
- Usage: ≥ 1 (Single)
- Genericity: ≥ 1 (Fully Specific)
- Configurability: ≥ 1 (Not Configurable)
- Stability: ≥ 1 (Very Unstable)
- Reusability Score: ≥ 1.5
```

---

# Reusability Improvement

## Improving Usage

### Strategies
- **Promote Adoption**: Promote component adoption across products
- **Documentation**: Improve documentation to increase adoption
- **Support**: Provide better support to increase adoption
- **Training**: Provide training to increase adoption

### Impact
- Higher usage score
- Higher reusability score
- Potential reclassification

## Improving Genericity

### Strategies
- **Extract Generic Logic**: Extract generic logic from industry-specific code
- **Parameterization**: Parameterize industry-specific logic
- **Configuration**: Move industry-specific logic to configuration
- **Abstraction**: Abstract industry-specific logic

### Impact
- Higher genericity score
- Higher reusability score
- Potential reclassification

## Improving Configurability

### Strategies
- **Configuration Options**: Add configuration options
- **Templates**: Use templates for industry-specific logic
- **Plugins**: Use plugins for extensibility
- **Hooks**: Use hooks for customization

### Impact
- Higher configurability score
- Higher reusability score
- Potential reclassification

## Improving Stability

### Strategies
- **Testing**: Improve testing to reduce changes
- **Documentation**: Improve documentation to reduce misunderstandings
- **Versioning**: Use semantic versioning
- **Deprecation**: Use deprecation for breaking changes

### Impact
- Higher stability score
- Higher reusability score
- Potential reclassification

---

# Reusability Tracking

## Tracking Metrics

### Component-Level Metrics
- Current usage count
- Potential usage count
- Reusability score
- Classification
- Migration status

### Domain-Level Metrics
- Number of Core Assets
- Number of Shared Engines
- Number of Product Assets
- Average reusability score

### Ecosystem-Level Metrics
- Total components
- Total reusable components
- Reuse rate
- Migration progress

## Tracking Frequency

- **Component-Level**: Monthly
- **Domain-Level**: Quarterly
- **Ecosystem-Level**: Annually

---

# Document End

**Document ID:** ESAMF-CLASSIFICATION-003

**Version:** 1.0
