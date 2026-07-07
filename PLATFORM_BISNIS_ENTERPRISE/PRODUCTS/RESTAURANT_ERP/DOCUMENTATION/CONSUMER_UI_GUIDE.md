# Consumer UI Guide

## Overview

The EBP Restaurant ERP includes multiple consumer-facing interfaces for different use cases:

1. **Consumer App** - Full-featured mobile web app for customers
2. **Kiosk App** - Self-service ordering kiosk for in-restaurant use
3. **Mobile Waiter App** - Staff mobile app for order management

## Consumer App (Customer-Facing)

### Location
```
/FRONTEND/frontend/consumer/index.html
```

### Features

#### Guest Access (No Registration Required)
- ✅ **Menu Browsing** - View all menu items without login
- ✅ **Restaurant Discovery** - Browse featured and nearby restaurants
- ✅ **Search & Filter** - Search by cuisine, name, or filters
- ✅ **Restaurant Details** - View restaurant info, ratings, reviews
- ✅ **Multi-language Support** - Indonesian and English

#### Authentication Required
- ❌ **Order Placement** - Requires login
- ❌ **Reservations** - Requires login
- ❌ **Order History** - Requires login
- ❌ **Favorites** - Requires login
- ❌ **Loyalty Points** - Requires login
- ❌ **Reviews** - Requires login

### Quick Login Options

#### 1. Google OAuth (Coming Soon)
```javascript
// Placeholder for Google Sign-In SDK integration
// Will use Google Identity Services
```

#### 2. Phone/OTP Login (Implemented)
- **Flow**:
  1. User enters phone number with country code
  2. Backend sends 6-digit OTP via SMS
  3. User enters OTP in auto-focus inputs
  4. Backend verifies OTP and creates/updates user account
  5. User logged in automatically

- **Supported Countries**:
  - Indonesia (+62)
  - USA (+1)
  - UK (+44)
  - Singapore (+65)
  - Malaysia (+60)

- **API Endpoints**:
  - `POST /api/consumer/auth/send-otp` - Send OTP to phone
  - `POST /api/consumer/auth/verify-otp` - Verify OTP and login

#### 3. Email/Password Login (Traditional)
- Standard email and password authentication
- JWT token-based session management

### Pages & Features

#### Home Page
- Featured restaurants carousel
- Nearby restaurants with distance
- Quick filters (All, Nearby, Top Rated, Halal, Delivery)
- Search bar with filters
- Restaurant cards with ratings, cuisine type, delivery info

#### Restaurant Detail Page
- Restaurant information
- Menu categories and items
- Add to cart functionality
- Reviews and ratings
- Operating hours
- Location map

#### Order Page
- Current orders with status
- Order history
- Order details
- Reorder functionality

#### Reservations Page
- Make new reservations
- View upcoming reservations
- Cancel reservations
- Reservation history

#### Favorites Page
- Saved restaurants
- Saved menu items
- Quick access to favorites

#### Loyalty Page
- Points balance
- Points history
- Rewards redemption
- Tier status

#### Settings Page
- Language selection
- Dark mode toggle
- Notification preferences
- Location services
- Account settings

### Guest Mode vs Logged In

| Feature | Guest | Logged In |
|---------|-------|-----------|
| Browse Menu | ✅ | ✅ |
| View Restaurant Details | ✅ | ✅ |
| Search & Filter | ✅ | ✅ |
| Read Reviews | ✅ | ✅ |
| Place Orders | ❌ | ✅ |
| Make Reservations | ❌ | ✅ |
| View Order History | ❌ | ✅ |
| Save Favorites | ❌ | ✅ |
| Earn Loyalty Points | ❌ | ✅ |
| Write Reviews | ❌ | ✅ |
| Update Profile | ❌ | ✅ |

## Kiosk App (Self-Service)

### Location
```
/FRONTEND/frontend/kiosk/index.html
```

### Features
- **No Authentication Required** - Designed for public use
- **Menu Browsing** - Full menu with categories
- **Order Placement** - Add items to cart
- **Order Summary** - Real-time order total
- **Customer Name Input** - For order identification
- **Order Confirmation** - Review before placing
- **Order Number Display** - Track order status
- **Estimated Time** - Preparation time estimate

### Use Cases
- Restaurant self-service kiosks
- Quick ordering without staff
- Reduce wait times
- Order accuracy improvement

## Mobile Waiter App (Staff)

### Location
```
/FRONTEND/frontend/mobile/index.html
```

### Features
- **Authentication Required** - Staff login
- **Order Management** - Create, view, update orders
- **Table Management** - View and assign tables
- **Menu Access** - View menu for ordering
- **Order Status Updates** - Update order status
- **Offline Support** - Work without internet
- **Profile Management** - Staff profile

### Target Users
- Waiters
- Restaurant staff
- Floor managers

## API Endpoints

### Public Endpoints (No Auth)
```
GET /api/v1/consumer/restaurants/featured - Get featured restaurants
GET /api/v1/consumer/restaurants/nearby - Get nearby restaurants
GET /api/v1/consumer/restaurants - Get all restaurants
GET /api/v1/consumer/restaurants/{id} - Get restaurant details
GET /api/v1/consumer/cuisines - Get cuisine types
GET /api/v1/consumer/menu/{restaurant_id} - Get restaurant menu
GET /api/v1/consumer/faq - Get FAQ
```

### Consumer Auth Endpoints
```
POST /api/v1/consumer/auth/login - Email/password login
POST /api/v1/consumer/auth/send-otp - Send OTP to phone
POST /api/v1/consumer/auth/verify-otp - Verify OTP and login
POST /api/v1/consumer/auth/google - Google OAuth login (coming soon)
```

### Consumer Order Endpoints (Auth Required)
```
POST /api/v1/consumer/orders - Place new order
GET /api/v1/consumer/orders - Get user order history
```

### Consumer Reservation Endpoints (Auth Required)
```
POST /api/v1/consumer/reservations - Make new reservation
GET /api/v1/consumer/reservations - Get user reservations
```

### Consumer Loyalty Endpoints (Auth Required)
```
GET /api/v1/consumer/loyalty - Get loyalty points and tier
POST /api/v1/consumer/loyalty/redeem - Redeem loyalty reward
```

### Consumer Review Endpoints (Auth Required)
```
POST /api/v1/consumer/reviews - Submit restaurant review
```

### Consumer Favorites Endpoints (Auth Required)
```
GET /api/v1/consumer/favorites - Get user favorites
```

## Multi-Language Support

### Supported Languages
- Indonesian (ID) - Default
- English (EN)

### Implementation
- i18n.js for translation management
- `data-i18n` attributes in HTML
- Language toggle in header
- Persistent language preference

## Responsive Design

### Breakpoints
- Mobile: < 768px
- Tablet: 768px - 1024px
- Desktop: > 1024px

### Mobile-First Approach
- Optimized for mobile devices
- Touch-friendly interface
- Bottom navigation for easy thumb access
- Swipe gestures for navigation

## Security Considerations

### Guest Mode
- No sensitive data exposure
- Limited functionality
- No personal data collection
- Anonymous browsing

### Authentication
- JWT token-based
- Secure token storage (localStorage)
- Token expiration handling
- Auto-logout on token expiry

### OTP Security
- 6-digit code
- 5-minute expiration
- Rate limiting (max 3 attempts)
- Phone number verification

## Future Enhancements

### Planned Features
- [ ] Google OAuth integration
- [ ] Facebook login
- [ ] Apple Sign-In
- [ ] Biometric authentication (fingerprint/face)
- [ ] Social sharing
- [ ] Referral system
- [ ] Gift cards
- [ ] Split payments
- [ ] Real-time order tracking
- [ ] Push notifications

### Improvements
- [ ] Progressive Web App (PWA)
- [ ] Offline mode for consumers
- [ ] Voice search
- [ ] AI-powered recommendations
- [ ] Augmented reality menu viewing

## Testing

### Manual Testing Checklist
- [ ] Guest can browse menu without login
- [ ] Guest can view restaurant details
- [ ] Guest cannot place orders
- [ ] OTP login flow works correctly
- [ ] OTP auto-focus inputs work
- [ ] Resend OTP functionality
- [ ] Email/password login works
- [ ] Language toggle works
- [ ] Responsive design on mobile/tablet/desktop
- [ ] Kiosk ordering without authentication

### Automated Testing
- Playwright tests for consumer app
- E2E tests for login flows
- API tests for authentication endpoints

## Documentation Updates

### Related Files
- `CONSUMER_UI_GUIDE.md` - This file
- `API_DOCUMENTATION.md` - Backend API documentation
- `CONFIGURATION_GUIDE.md` - Configuration management
- `DATABASE_SETUP_GUIDE.md` - Database setup

## Support

### Common Issues

**Guest cannot view menu**
- Check if kiosk/mobile menu endpoints are public
- Verify CORS configuration
- Check API base URL in config.js

**OTP not received**
- Verify SMS gateway configuration
- Check phone number format
- Verify backend OTP sending logic

**Login fails**
- Check JWT secret configuration
- Verify database connection
- Check user credentials

**Language not switching**
- Verify i18n.js is loaded
- Check translation keys
- Verify language preference storage
