# ESAMF UI Refactoring

**Document ID:** ESAMF-REFACTORING-004

**Version:** 1.0

**Purpose:** Define the UI refactoring standards for ESAMF

---

# Overview

UI Refactoring is the process of improving user interface structure, consistency, and compliance with EBP standards while maintaining user experience. This standard defines the approach for migrating UIs to EBP standards.

---

# Refactoring Principles

## 1. User Experience Preservation

**UI refactoring must preserve user experience.**

- No breaking changes to user workflow
- Maintain familiar patterns
- Preserve user preferences
- Provide transition guidance

## 2. EBP Design System

**Refactored UI must comply with EBP design system.**

- Component library
- Design tokens
- Accessibility standards
- Responsive design

## 3. Consistency

**UI must be consistent across all products.**

- Consistent components
- Consistent patterns
- Consistent terminology
- Consistent behavior

## 4. Accessibility

**UI must be accessible to all users.**

- WCAG 2.1 compliance
- Keyboard navigation
- Screen reader support
- Color contrast

---

# Refactoring Process

## Phase 1: Analysis

### Step 1: Analyze Current UI

```
Component: [Component Name]
Purpose: [What it does]
Current Framework: [React/Vue/Angular/jQuery/etc.]
Current Styling: [CSS/SASS/Tailwind/etc.]
Dependencies: [List dependencies]
```

### Step 2: Identify Refactoring Needs

```
Component Issues:
- [Issue 1: Description]
- [Issue 2: Description]

Styling Issues:
- [Issue 1: Description]
- [Issue 2: Description]

Accessibility Issues:
- [Issue 1: Description]
- [Issue 2: Description]
```

### Step 3: Create Refactoring Plan

```
Refactoring Steps:
1. [Step 1: Description, Breaking Change]
2. [Step 2: Description, Breaking Change]
3. [Step 3: Description, Breaking Change]

Migration Plan:
- [Component migration timeline]
- [User communication plan]
```

---

## Phase 2: Component Refactoring

### Step 1: Use EBP Component Library

**Before:**
```jsx
// Custom button component
function Button({ children, onClick }) {
  return (
    <button 
      onClick={onClick}
      style={{
        padding: '10px 20px',
        backgroundColor: '#007bff',
        color: 'white',
        border: 'none',
        borderRadius: '4px'
      }}
    >
      {children}
    </button>
  );
}
```

**After:**
```jsx
// Use EBP component library
import { Button } from '@ebp/components';

function MyComponent() {
  return (
    <Button variant="primary" onClick={handleClick}>
      Click me
    </Button>
  );
}
```

### Step 2: Apply Design Tokens

**Before:**
```jsx
<div style={{ padding: '20px', margin: '10px' }}>
  Content
</div>
```

**After:**
```jsx
import { spacing } from '@ebp/tokens';

<div style={{ padding: spacing.lg, margin: spacing.md }}>
  Content
</div>
```

### Step 3: Standardize Component Structure

**Before:**
```jsx
function UserCard({ user }) {
  return (
    <div>
      <img src={user.avatar} />
      <div>{user.name}</div>
      <div>{user.email}</div>
    </div>
  );
}
```

**After:**
```jsx
import { Card, Avatar, Typography } from '@ebp/components';

function UserCard({ user }) {
  return (
    <Card>
      <Avatar src={user.avatar} alt={user.name} />
      <Typography variant="h6">{user.name}</Typography>
      <Typography variant="body2">{user.email}</Typography>
    </Card>
  );
}
```

---

## Phase 3: Styling Refactoring

### Step 1: Apply EBP Design System

**Before:**
```css
.button {
  padding: 10px 20px;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
}
```

**After:**
```css
/* Use EBP design tokens */
.button {
  padding: var(--ebp-spacing-md) var(--ebp-spacing-lg);
  background-color: var(--ebp-color-primary);
  color: var(--ebp-color-text-primary);
  border: none;
  border-radius: var(--ebp-border-radius-md);
}
```

### Step 2: Use Utility Classes (Tailwind)

**Before:**
```jsx
<div style={{ padding: '20px', margin: '10px', backgroundColor: '#f5f5f5' }}>
  Content
</div>
```

**After:**
```jsx
<div className="p-5 m-2.5 bg-gray-100">
  Content
</div>
```

### Step 3: Apply Responsive Design

**Before:**
```css
.container {
  width: 1000px;
  margin: 0 auto;
}
```

**After:**
```css
.container {
  width: 100%;
  max-width: 1000px;
  margin: 0 auto;
  padding: 0 var(--ebp-spacing-md);
}

@media (min-width: 768px) {
  .container {
    padding: 0 var(--ebp-spacing-lg);
  }
}
```

---

## Phase 4: Accessibility Refactoring

### Step 1: Add ARIA Labels

**Before:**
```jsx
<button onClick={handleClick}>
  <Icon name="close" />
</button>
```

**After:**
```jsx
<button 
  onClick={handleClick}
  aria-label="Close dialog"
>
  <Icon name="close" aria-hidden="true" />
</button>
```

### Step 2: Add Keyboard Navigation

**Before:**
```jsx
<div onClick={handleClick}>
  Clickable content
</div>
```

**After:**
```jsx
<div 
  role="button"
  tabIndex={0}
  onClick={handleClick}
  onKeyDown={(e) => {
    if (e.key === 'Enter' || e.key === ' ') {
      handleClick();
    }
  }}
>
  Clickable content
</div>
```

### Step 3: Ensure Color Contrast

**Before:**
```css
.text {
  color: #cccccc; /* Low contrast */
  background-color: #ffffff;
}
```

**After:**
```css
.text {
  color: var(--ebp-color-text-primary); /* WCAG AA compliant */
  background-color: var(--ebp-color-background);
}
```

### Step 4: Add Form Labels

**Before:**
```jsx
<input type="text" placeholder="Name" />
```

**After:**
```jsx
<label htmlFor="name">Name</label>
<input 
  id="name" 
  type="text" 
  aria-required="true"
  aria-describedby="name-error"
/>
<div id="name-error" role="alert"></div>
```

---

## Phase 5: Form Refactoring

### Step 1: Use EBP Form Components

**Before:**
```jsx
function UserForm() {
  return (
    <form>
      <label>Name</label>
      <input type="text" name="name" />
      <label>Email</label>
      <input type="email" name="email" />
      <button type="submit">Submit</button>
    </form>
  );
}
```

**After:**
```jsx
import { Form, TextField, EmailField, Button } from '@ebp/components';

function UserForm() {
  return (
    <Form onSubmit={handleSubmit}>
      <TextField 
        name="name" 
        label="Name" 
        required 
      />
      <EmailField 
        name="email" 
        label="Email" 
        required 
      />
      <Button type="submit" variant="primary">
        Submit
      </Button>
    </Form>
  );
}
```

### Step 2: Add Validation

**Before:**
```jsx
function UserForm() {
  const handleSubmit = (e) => {
    e.preventDefault();
    // Manual validation
  };
}
```

**After:**
```jsx
import { Form, TextField, EmailField, Button } from '@ebp/components';
import { useForm } from '@ebp/forms';

function UserForm() {
  const { register, handleSubmit, errors } = useForm({
    validationSchema: {
      name: {
        required: 'Name is required',
        minLength: {
          value: 2,
          message: 'Name must be at least 2 characters'
        }
      },
      email: {
        required: 'Email is required',
        pattern: {
          value: /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$/i,
          message: 'Invalid email address'
        }
      }
    }
  });

  return (
    <Form onSubmit={handleSubmit}>
      <TextField 
        name="name" 
        label="Name" 
        error={errors.name?.message}
        {...register('name')}
      />
      <EmailField 
        name="email" 
        label="Email" 
        error={errors.email?.message}
        {...register('email')}
      />
      <Button type="submit" variant="primary">
        Submit
      </Button>
    </Form>
  );
}
```

---

## Phase 6: Navigation Refactoring

### Step 1: Use EBP Navigation Components

**Before:**
```jsx
<nav>
  <a href="/dashboard">Dashboard</a>
  <a href="/users">Users</a>
  <a href="/settings">Settings</a>
</nav>
```

**After:**
```jsx
import { Navigation, NavigationItem } from '@ebp/components';

function AppNavigation() {
  return (
    <Navigation>
      <NavigationItem to="/dashboard" icon="dashboard">
        Dashboard
      </NavigationItem>
      <NavigationItem to="/users" icon="users">
        Users
      </NavigationItem>
      <NavigationItem to="/settings" icon="settings">
        Settings
      </NavigationItem>
    </Navigation>
  );
}
```

### Step 2: Apply Breadcrumbs

**Before:**
```jsx
<div>
  <a href="/">Home</a> / 
  <a href="/users">Users</a> / 
  <span>John Doe</span>
</div>
```

**After:**
```jsx
import { Breadcrumbs, BreadcrumbItem } from '@ebp/components';

function UserBreadcrumbs() {
  return (
    <Breadcrumbs>
      <BreadcrumbItem to="/">Home</BreadcrumbItem>
      <BreadcrumbItem to="/users">Users</BreadcrumbItem>
      <BreadcrumbItem>John Doe</BreadcrumbItem>
    </Breadcrumbs>
  );
}
```

---

# Common UI Refactoring Patterns

## Pattern 1: Custom Component to Library Component

**Before:**
```jsx
// Custom modal
function Modal({ isOpen, onClose, children }) {
  if (!isOpen) return null;
  return (
    <div className="modal-overlay">
      <div className="modal-content">
        {children}
        <button onClick={onClose}>Close</button>
      </div>
    </div>
  );
}
```

**After:**
```jsx
import { Modal } from '@ebp/components';

function MyComponent() {
  const [isOpen, setIsOpen] = useState(false);
  
  return (
    <>
      <Button onClick={() => setIsOpen(true)}>Open</Button>
      <Modal isOpen={isOpen} onClose={() => setIsOpen(false)}>
        Content
      </Modal>
    </>
  );
}
```

## Pattern 2: Inline Styles to Design Tokens

**Before:**
```jsx
<div style={{ padding: '20px', margin: '10px', color: '#333' }}>
  Content
</div>
```

**After:**
```jsx
import { spacing, colors } from '@ebp/tokens';

<div style={{ 
  padding: spacing.lg, 
  margin: spacing.md, 
  color: colors.text.primary 
}}>
  Content
</div>
```

## Pattern 3: Hardcoded Text to i18n

**Before:**
```jsx
<button>Submit</button>
```

**After:**
```jsx
import { useTranslation } from '@ebp/i18n';

function MyComponent() {
  const { t } = useTranslation();
  
  return (
    <Button>{t('common.submit')}</Button>
  );
}
```

## Pattern 4: Manual State to State Management

**Before:**
```jsx
function UserList() {
  const [users, setUsers] = useState([]);
  
  useEffect(() => {
    fetch('/api/users')
      .then(res => res.json())
      .then(data => setUsers(data));
  }, []);
  
  return (
    <ul>
      {users.map(user => (
        <li key={user.id}>{user.name}</li>
      ))}
    </ul>
  );
}
```

**After:**
```jsx
import { useQuery } from '@ebp/data';

function UserList() {
  const { data: users, isLoading, error } = useQuery('users', () =>
    fetch('/api/users').then(res => res.json())
  );
  
  if (isLoading) return <Loading />;
  if (error) return <Error message={error.message} />;
  
  return (
    <ul>
      {users.map(user => (
        <li key={user.id}>{user.name}</li>
      ))}
    </ul>
  );
}
```

---

# UI Refactoring Checklist

## Analysis
- [ ] Current UI analyzed
- [ ] Refactoring needs identified
- [ ] Refactoring plan created
- [ ] Migration plan created

## Components
- [ ] EBP component library used
- [ ] Design tokens applied
- [ ] Component structure standardized
- [ ] Custom components deprecated

## Styling
- [ ] EBP design system applied
- [ ] Utility classes used
- [ ] Responsive design applied
- [ ] Custom styles deprecated

## Accessibility
- [ ] ARIA labels added
- [ ] Keyboard navigation added
- [ ] Color contrast ensured
- [ ] Form labels added

## Forms
- [ ] EBP form components used
- [ ] Validation added
- [ ] Error handling added
- [ ] Success feedback added

## Navigation
- [ ] EBP navigation components used
- [ ] Breadcrumbs applied
- [ ] Routing standardized
- [ ] URL structure updated

## Internationalization
- [ ] Hardcoded text replaced
- [ ] i18n keys added
- [ ] Translations provided
- [ ] Date/number formatting applied

## Testing
- [ ] Visual regression tests added
- [ ] Accessibility tests added
- [ ] User acceptance tests added
- [ ] Cross-browser tests added

---

# Document End

**Document ID:** ESAMF-REFACTORING-004

**Version:** 1.0
