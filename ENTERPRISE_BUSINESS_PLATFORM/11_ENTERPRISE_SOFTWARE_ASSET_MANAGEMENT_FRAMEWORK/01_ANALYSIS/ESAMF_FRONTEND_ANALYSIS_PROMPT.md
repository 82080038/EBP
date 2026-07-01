# ESAMF Frontend Analysis Prompt

**Document ID:** ESAMF-FE-001

**Version:** 1.0

**Purpose**

Prompt khusus untuk analisis frontend repository secara mendalam sesuai standar Enterprise Software Asset Management Framework (ESAMF).

---

# OBJECTIVE

Lakukan analisis menyeluruh terhadap frontend repository saat ini.

Jangan melakukan perubahan source code.

Jangan melakukan refactoring.

Tugas Anda hanya:

* membaca file frontend;
* memahami arsitektur;
* menginventarisasi komponen;
* mendokumentasikan;
* mengklasifikasikan.

---

# OUTPUT LOCATION

Hasil analisis ditempatkan pada:

```text
11_ENTERPRISE_SOFTWARE_ASSET_MANAGEMENT_FRAMEWORK/

07_MIGRATION/

<PROJECT_NAME>/

10_FRONTEND_ANALYSIS.md
```

---

# ANALYSIS CHECKLIST

## 1. Frontend Technology Stack

Identifikasi:

- Framework (React, Vue, Angular, Svelte, etc.)
- UI Library (Material UI, Ant Design, Bootstrap, Tailwind, etc.)
- State Management (Redux, Vuex, Pinia, Zustand, Context API, etc.)
- Routing (React Router, Vue Router, Angular Router, etc.)
- HTTP Client (Axios, Fetch, etc.)
- Form Handling (React Hook Form, Formik, VeeValidate, etc.)
- Validation Library (Yup, Zod, Joi, etc.)
- Date Library (Moment.js, Day.js, date-fns, etc.)
- Icon Library (Material Icons, FontAwesome, Lucide, etc.)
- Build Tool (Vite, Webpack, Parcel, etc.)
- Package Manager (npm, yarn, pnpm, etc.)

## 2. Folder Structure Analysis

Analisis struktur folder:

```
src/
public/
assets/
components/
pages/
views/
layouts/
hooks/
utils/
services/
store/
router/
styles/
```

Jelaskan fungsi setiap folder.

## 3. Component Analysis

Identifikasi seluruh komponen:

- Nama komponen
- Tipe komponen (Page, Layout, UI, Business, Utility)
- Props interface
- State management
- Hooks yang digunakan
- Dependency antar komponen
- Reusability score

## 4. Page/View Analysis

Untuk setiap page/view:

- Nama page
- Route path
- Komponen yang digunakan
- State management
- API calls
- Authentication requirement
- Authorization requirement

## 5. State Management Analysis

Analisis:

- State structure
- Actions
- Mutations/Reducers
- Selectors
- Middleware
- Persistence strategy
- State hydration

## 6. Routing Analysis

Identifikasi:

- Route configuration
- Route guards
- Lazy loading
- Nested routes
- Dynamic routes
- Redirect rules

## 7. API Integration Analysis

Petakan seluruh API call:

- Service layer
- Endpoint yang dipanggil
- Request/Response handling
- Error handling
- Loading state
- Caching strategy

## 8. Form Analysis

Untuk setiap form:

- Form validation
- Form submission
- Error handling
- Success handling
- Multi-step forms

## 9. Styling Analysis

Identifikasi:

- CSS framework
- Custom CSS
- CSS Modules
- Styled Components
- Tailwind classes
- Theme configuration
- Responsive design
- Dark mode support

## 10. Performance Analysis

Analisis:

- Bundle size
- Code splitting
- Lazy loading
- Image optimization
- Font optimization
- Rendering performance
- Memory usage

## 11. Accessibility Analysis

Cek:

- ARIA labels
- Keyboard navigation
- Screen reader support
- Color contrast
- Focus management
- Alt text

## 12. Security Analysis

Identifikasi:

- XSS protection
- CSRF protection
- Content Security Policy
- Sensitive data in localStorage
- API key exposure
- Authentication token handling

## 13. Testing Coverage

Identifikasi:

- Unit tests
- Integration tests
- E2E tests
- Component tests
- Test coverage percentage

## 14. Responsive Design Analysis

Analisis:

- Breakpoints
- Mobile layout
- Tablet layout
- Desktop layout
- Touch interactions

## 15. EBP UI Standard Compliance

Bandingkan dengan standar EBP:

- Component library
- Design system
- Color palette
- Typography
- Spacing
- Layout patterns
- User experience patterns

---

# OUTPUT FORMAT

## Section 1: Frontend Overview

```markdown
## Frontend Overview

- **Framework**: [React/Vue/Angular/Svelte]
- **UI Library**: [Material UI/Ant Design/Bootstrap/Tailwind]
- **State Management**: [Redux/Vuex/Pinia/Zustand]
- **Build Tool**: [Vite/Webpack/Parcel]
- **Total Components**: [count]
- **Total Pages**: [count]
- **Total Routes**: [count]
- **Bundle Size**: [size]
```

## Section 2: Technology Stack

```markdown
## Technology Stack

### Core Framework
- [component]: [version]

### UI Components
- [component]: [version]

### State Management
- [component]: [version]

### Utilities
- [component]: [version]

### Build Tools
- [component]: [version]
```

## Section 3: Component Analysis

```markdown
## Component: [component_name]

**Type**: [Page/Layout/UI/Business/Utility]
**Location**: [path]

### Props
| Prop | Type | Required | Default | Description |
|------|------|----------|---------|-------------|
| [name]| [type]| [x]      | [val]   | [desc]      |

### State
- [state]: [description]

### Hooks
- [hook]: [purpose]

### Dependencies
- [component]: [purpose]

### Reusability Score
★★★★★ [description]
```

## Section 4: Page Analysis

```markdown
## Page: [page_name]

**Route**: [path]
**Location**: [path]

### Components Used
- [component]: [purpose]

### State Management
- [store]: [state used]

### API Calls
- [endpoint]: [purpose]

### Authentication
- Required: [Yes/No]
- Role: [role]

### Authorization
- Permission: [permission]
```

## Section 5: State Management

```markdown
## State Management

### Store Structure
```javascript
{
  [module]: {
    [state]: [description]
  }
}
```

### Actions
- [action]: [description]

### Selectors
- [selector]: [description]
```

## Section 6: Routing

```markdown
## Routing Configuration

| Path | Component | Auth | Permission | Lazy Load |
|------|-----------|------|------------|-----------|
| [/path] | [Comp] | [x] | [perm] | [x] |
```

## Section 7: API Integration

```markdown
## API Services

### Service: [service_name]
**Location**: [path]

| Method | Endpoint | Purpose |
|--------|----------|---------|
| [GET]  | [/path]  | [desc]  |
```

## Section 8: EBP UI Compliance

```markdown
## EBP UI Standard Compliance

### Compliant
- [pattern]: [description]

### Non-Compliant
- [pattern]: [description] - [recommendation]

### Missing
- [pattern]: [recommendation]
```

## Section 9: Recommendations

```markdown
## Recommendations

### Performance
- [recommendation]

### Accessibility
- [recommendation]

### Security
- [recommendation]

### Maintainability
- [recommendation]
```

---

# IMPORTANT RULES

- Jangan mengubah source code
- Jangan membuat commit Git
- Fokus pada analisis dan dokumentasi
- Gunakan bahasa Indonesia untuk penjelasan
- Sertakan contoh code jika perlu

---

# Definition of Done

Analisis frontend dianggap selesai apabila:

- Seluruh komponen telah teridentifikasi
- Seluruh page telah didokumentasikan
- State management telah dipetakan
- Routing telah didokumentasikan
- API integration telah dipetakan
- EBP UI compliance check telah dilakukan
- Rekomendasi telah disusun
