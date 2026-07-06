# Responsive Data Fetching Implementation

## Overview

This implementation enables the EBP Restaurant application to automatically adjust the amount and type of data fetched based on the user's screen size. This optimizes performance and user experience by:

- **Mobile devices**: Fetching limited data with fewer fields to reduce bandwidth and improve load times
- **Tablet devices**: Fetching moderate amounts of data with additional fields
- **Desktop devices**: Fetching complete data with all fields

## Architecture

### Frontend Components

#### 1. Screen Size Detector (`screen-size-detector.js`)

A JavaScript utility that detects the current screen size and provides responsive data fetching parameters.

**Screen Size Categories:**
- **Mobile**: < 768px width
- **Tablet**: 768px - 1024px width
- **Desktop**: > 1024px width

**Key Features:**
- Automatic screen size detection
- Resize event listener with debouncing
- Custom event dispatch on screen size change
- Data limits per resource type
- Field selection based on screen size

**Usage:**
```javascript
// Get current screen size
const screenSize = window.screenSizeDetector.getScreenSize();

// Get data limits for a resource type
const limits = window.screenSizeDetector.getDataLimits();
const productLimit = limits.products; // 10 for mobile, 20 for tablet, 100 for desktop

// Get fields to include
const fields = window.screenSizeDetector.getFields('products');
// Returns: ['product_id', 'product_name', 'price', 'category_name', 'image_url'] for mobile

// Get API parameters
const params = window.screenSizeDetector.getApiParams('products');
// Returns: { screen_size: 'mobile', limit: 10, fields: 'product_id,product_name,...' }
```

#### 2. API Client Modifications (`api-client.js`)

The API client now automatically includes screen size information in all requests.

**Changes:**
- Adds `X-Screen-Size` header with current screen size category
- Adds `X-Screen-Width` header with actual pixel width
- Merges screen size parameters with request parameters for key endpoints

**Modified Endpoints:**
- `getProducts()` - Includes screen size-based limits and field selection
- `getOrders()` - Includes screen size-based limits and field selection
- `getTables()` - Includes screen size-based limits and field selection

#### 3. Frontend Applications

All frontend applications now include screen size detection and automatic data reloading:

**Mobile App (`mobile.js`):**
- Initializes screen size detector
- Listens for screen size changes
- Reloads data when screen size changes

**Kiosk App (`kiosk.js`):**
- Same functionality as mobile app
- Optimized for self-service kiosk displays

**Consumer App (`consumer.js`):**
- Same functionality as mobile app
- Optimized for consumer-facing features

### Backend Components

#### 1. Screen Size Helper (`ScreenSizeHelper.php`)

A PHP utility class that handles screen size-based data filtering on the backend.

**Key Methods:**

```php
// Get screen size from headers or parameters
$screenSize = ScreenSizeHelper::getScreenSize($headers, $params);

// Get data limit for resource type
$limit = ScreenSizeHelper::getLimit($screenSize, 'products', 20);

// Get field list for resource type
$fields = ScreenSizeHelper::getFields($screenSize, 'products');

// Filter data fields
$filteredData = ScreenSizeHelper::filterFields($data, $fields);

// Filter array of items
$filteredItems = ScreenSizeHelper::filterArrayFields($items, $fields);

// Apply screen size filtering to response
$result = ScreenSizeHelper::applyScreenSizeFilter($result, $screenSize, 'products');

// Get pagination parameters with screen size defaults
$pagination = ScreenSizeHelper::getPaginationParams($params, $screenSize, 'products');
```

**Data Limits by Screen Size:**

| Resource Type | Mobile | Tablet | Desktop |
|---------------|--------|--------|---------|
| Products      | 10     | 20     | 100     |
| Orders        | 5      | 10     | 50      |
| Reservations  | 5      | 10     | 50      |
| Restaurants   | 5      | 10     | 50      |
| Reviews       | 3      | 5      | 10      |
| Categories    | 5      | 10     | 20      |
| Tables        | 6      | 12     | 24      |

**Field Selection by Screen Size:**

**Mobile (simplified fields):**
- Products: `product_id`, `product_name`, `price`, `category_name`, `image_url`
- Orders: `order_id`, `order_number`, `table_id`, `status`, `total_amount`
- Tables: `table_id`, `table_number`, `status`

**Tablet (moderate fields):**
- Products: Adds `description`
- Orders: Adds `created_at`
- Tables: Adds `capacity`

**Desktop (all fields):**
- Returns all available fields (`*`)

#### 2. Controller Updates

Controllers have been updated to use screen size information:

**OrderController.php:**
```php
public function getOrders()
{
    // Get screen size from headers
    $headers = getallheaders();
    $screenSize = \ScreenSizeHelper::getScreenSize($headers, $this->request->getAll());
    
    // Get pagination with screen size defaults
    $pagination = \ScreenSizeHelper::getPaginationParams($this->request->getAll(), $screenSize, 'orders');
    
    // Fetch data
    $result = $this->orderService->getOrders(..., $pagination['page'], $pagination['limit']);
    
    // Apply field filtering
    $result = \ScreenSizeHelper::applyScreenSizeFilter($result, $screenSize, 'orders');
    
    $this->jsonResponse($result);
}
```

**MenuController.php:**
- Updated `getProducts()` to use screen size parameters
- Applies field filtering based on screen size

**TableController.php:**
- Updated `getTables()` to use screen size parameters
- Applies field filtering based on screen size

## Implementation Details

### Frontend Integration

1. **Include the script** in HTML files:
```html
<script src="../js/screen-size-detector.js"></script>
```

2. **Initialize in application**:
```javascript
init() {
    this.bindEvents();
    this.loadInitialData();
    this.bindScreenSizeChange();
}

bindScreenSizeChange() {
    window.addEventListener('screenSizeChanged', (e) => {
        console.log('Screen size changed to:', e.detail.screenSize);
        this.loadInitialData();
    });
}
```

### Backend Integration

1. **Load the helper** in `bootstrap.php`:
```php
require_once __DIR__ . '/core/ScreenSizeHelper.php';
```

2. **Use in controllers**:
```php
// Get screen size
$screenSize = \ScreenSizeHelper::getScreenSize($headers, $params);

// Get pagination parameters
$pagination = \ScreenSizeHelper::getPaginationParams($params, $screenSize, 'resource_type');

// Apply field filtering
$result = \ScreenSizeHelper::applyScreenSizeFilter($result, $screenSize, 'resource_type');
```

## Benefits

### Performance Optimization
- **Reduced bandwidth**: Mobile devices receive less data
- **Faster load times**: Fewer fields and records to process
- **Better UX**: Optimized for device capabilities

### Automatic Adaptation
- **Responsive**: Automatically adjusts to screen size changes
- **Seamless**: No user intervention required
- **Real-time**: Debounced resize events prevent excessive reloading

### Developer-Friendly
- **Easy to extend**: Add new resource types and field sets
- **Configurable**: Adjust limits and field sets as needed
- **Consistent**: Standardized approach across all endpoints

## Testing

### Manual Testing

1. **Mobile View:**
   - Open browser DevTools
   - Set device to mobile (e.g., iPhone 12)
   - Check Network tab for API requests
   - Verify `X-Screen-Size: mobile` header
   - Verify limited data response

2. **Tablet View:**
   - Set device to tablet (e.g., iPad)
   - Verify `X-Screen-Size: tablet` header
   - Verify moderate data response

3. **Desktop View:**
   - Use normal browser window
   - Verify `X-Screen-Size: desktop` header
   - Verify full data response

4. **Resize Testing:**
   - Start in mobile view
   - Resize to desktop view
   - Verify data reloads with new parameters
   - Check console for "Screen size changed" message

### Automated Testing

Add to test suite:
```javascript
// Test screen size detection
describe('ScreenSizeDetector', () => {
    it('should detect mobile screen size', () => {
        // Mock window.innerWidth < 768
        // Verify getScreenSize() returns 'mobile'
    });
    
    it('should detect tablet screen size', () => {
        // Mock window.innerWidth between 768-1024
        // Verify getScreenSize() returns 'tablet'
    });
    
    it('should detect desktop screen size', () => {
        // Mock window.innerWidth > 1024
        // Verify getScreenSize() returns 'desktop'
    });
});
```

## Future Enhancements

1. **Custom Breakpoints**: Allow configuration of custom breakpoints
2. **User Preferences**: Remember user's preferred data density
3. **Network Awareness**: Adjust based on connection speed
4. **Caching**: Cache responses per screen size
5. **Analytics**: Track screen size usage patterns
6. **A/B Testing**: Test different data limits for optimization

## Troubleshooting

### Issue: Data not reloading on resize

**Solution:** Ensure `bindScreenSizeChange()` is called in `init()` method.

### Issue: Backend not receiving screen size headers

**Solution:** Check that `screen-size-detector.js` is loaded before `api-client.js`.

### Issue: Field filtering not working

**Solution:** Verify that `ScreenSizeHelper.php` is loaded in `bootstrap.php`.

### Issue: Too much data on mobile

**Solution:** Adjust data limits in `ScreenSizeHelper::DEFAULT_LIMITS`.

## Configuration

### Adjusting Data Limits

Edit `ScreenSizeHelper.php`:
```php
private const DEFAULT_LIMITS = [
    'mobile' => [
        'products' => 10, // Adjust this value
        // ...
    ],
    // ...
];
```

### Adjusting Field Sets

Edit `ScreenSizeHelper.php`:
```php
private const FIELD_SETS = [
    'mobile' => [
        'products' => ['product_id', 'product_name', 'price'], // Add/remove fields
        // ...
    ],
    // ...
];
```

### Adjusting Breakpoints

Edit `screen-size-detector.js`:
```javascript
this.breakpoints = {
    mobile: 768,  // Adjust this value
    tablet: 1024  // Adjust this value
};
```

## Conclusion

This responsive data fetching implementation provides a robust solution for optimizing data delivery based on screen size. It improves performance, reduces bandwidth usage, and enhances user experience across all device types while maintaining a clean, maintainable codebase.
