# RESTAURANT_ERP User Guide

**Version**: 1.0  
**Last Updated**: July 7, 2026

---

## Table of Contents

1. [Getting Started](#getting-started)
2. [Dashboard Overview](#dashboard-overview)
3. [Order Management](#order-management)
4. [Menu Management](#menu-management)
5. [Inventory Management](#inventory-management)
6. [Kitchen Operations](#kitchen-operations)
7. [Table Management](#table-management)
8. [Customer Management](#customer-management)
9. [Reports & Analytics](#reports--analytics)
10. [AI & Analytics](#ai--analytics)
11. [Settings](#settings)
12. [Troubleshooting](#troubleshooting)

---

## Getting Started

### System Requirements

- **Browser**: Chrome 90+, Firefox 88+, Safari 14+, Edge 90+
- **Internet**: Stable internet connection
- **Screen Resolution**: Minimum 1024x768 (recommended 1920x1080)

### First-Time Login

1. Open your browser and navigate to the EBP Restaurant URL
2. Enter your username and password
3. Click "Login"
4. You will be redirected to the dashboard

### Dashboard Navigation

The dashboard is organized into the following main sections:

- **Overview**: Quick view of today's performance
- **Orders**: Manage customer orders
- **Menu**: Manage menu items and categories
- **Tables**: Manage restaurant tables
- **Inventory**: Track stock levels
- **Kitchen**: Kitchen order management
- **Reservations**: Manage table reservations
- **Customers**: Customer database
- **Reports**: Business reports and analytics
- **AI & Analytics**: AI-powered insights
- **Settings**: System configuration

---

## Dashboard Overview

### Key Metrics

The Overview page displays:

- **Today's Revenue**: Total sales for the current day
- **Today's Orders**: Number of orders processed
- **Active Tables**: Currently occupied tables
- **Pending Orders**: Orders awaiting processing
- **Kitchen Queue**: Orders in preparation

### Quick Actions

- **New Order**: Create a new order
- **View Reports**: Access detailed reports
- **Manage Inventory**: Quick inventory check
- **Kitchen Status**: View kitchen queue

---

## Order Management

### Creating an Order

1. Navigate to the **Orders** section
2. Click **New Order**
3. Select a table (for dine-in) or delivery type
4. Add items to the order:
   - Search or browse menu items
   - Select quantity
   - Add special instructions
5. Review order details
6. Click **Submit Order**

### Updating Order Status

1. Go to **Orders** section
2. Find the order you want to update
3. Click on the order
4. Change status:
   - **Pending**: Order received, not yet started
   - **In Progress**: Order being prepared
   - **Ready**: Order ready for serving
   - **Served**: Order delivered to customer
   - **Completed**: Order finished
   - **Cancelled**: Order cancelled

### Modifying an Order

1. Open the order
2. Click **Edit**
3. Add or remove items
4. Update quantities
5. Save changes

### Processing Payments

1. Open the completed order
2. Click **Process Payment**
3. Select payment method:
   - Cash
   - Credit Card
   - E-Wallet
   - Bank Transfer
4. Enter payment amount
5. Calculate change (if applicable)
6. Confirm payment

---

## Menu Management

### Adding a New Menu Item

1. Navigate to **Menu** section
2. Click **Add Product**
3. Fill in product details:
   - Name
   - Category
   - Price
   - Description
   - Image (optional)
   - Status (Active/Inactive)
4. Click **Save**

### Managing Categories

1. Go to **Menu** section
2. Click **Categories** tab
3. To add a category:
   - Click **Add Category**
   - Enter category name
   - Save
4. To edit/delete:
   - Click on the category
   - Edit or delete as needed

### Updating Prices

1. Navigate to **Menu** section
2. Find the product
3. Click **Edit**
4. Update the price
5. Save changes

---

## Inventory Management

### Checking Stock Levels

1. Go to **Inventory** section
2. View current stock levels
3. Filter by category or search for items
4. Stock levels are color-coded:
   - **Green**: Sufficient stock
   - **Yellow**: Low stock (reorder soon)
   - **Red**: Critical (reorder immediately)

### Adding Stock

1. Navigate to **Inventory** section
2. Click on the item
3. Click **Add Stock**
4. Enter:
   - Quantity to add
   - Reason (restock, return, etc.)
   - Supplier (optional)
5. Save

### Removing Stock

1. Go to **Inventory** section
2. Click on the item
3. Click **Remove Stock**
4. Enter:
   - Quantity to remove
   - Reason (spoilage, damage, usage, etc.)
5. Save

### Setting Reorder Points

1. Open an inventory item
2. Set **Reorder Point** (minimum stock level)
3. Set **Reorder Quantity** (amount to order)
4. Save
5. System will alert when stock falls below reorder point

---

## Kitchen Operations

### Viewing Kitchen Queue

1. Navigate to **Kitchen** section
2. View orders by status:
   - **Pending**: Orders waiting to start
   - **In Progress**: Orders being prepared
   - **Ready**: Orders ready for serving

### Starting an Order

1. Click on a pending order
2. Click **Start Preparation**
3. Order moves to "In Progress"

### Completing an Order

1. Click on an in-progress order
2. Click **Mark as Ready**
3. Order moves to "Ready" status
4. Wait staff is notified

### Kitchen Efficiency Tips

- Prioritize orders by time received
- Check special instructions before starting
- Update status promptly to keep queue accurate
- Use AI predictions for preparation time estimates

---

## Table Management

### Viewing Table Status

1. Navigate to **Tables** section
2. View all tables with current status:
   - **Available**: Table is free
   - **Occupied**: Table has customers
   - **Reserved**: Table is reserved

### Assigning a Table

1. When creating an order, select a table
2. Table status changes to "Occupied"
3. Table shows assigned order number

### Clearing a Table

1. Go to **Tables** section
2. Click on the occupied table
3. Click **Clear Table**
4. Table status changes to "Available"

### Managing Reservations

1. Navigate to **Reservations** section
2. Click **Add Reservation**
3. Enter details:
   - Customer name
   - Date and time
   - Number of guests
   - Table preference (optional)
   - Special requests (optional)
4. Save

---

## Customer Management

### Adding a Customer

1. Navigate to **Customers** section
2. Click **Add Customer**
3. Enter customer details:
   - Name
   - Phone number
   - Email (optional)
   - Address (optional)
   - Preferences (optional)
4. Save

### Viewing Customer History

1. Go to **Customers** section
2. Click on a customer
3. View:
   - Order history
   - Total spent
   - Visit frequency
   - Preferences
   - Loyalty points

### Customer Segmentation

The system automatically segments customers based on:
- Visit frequency
- Average order value
- Total lifetime value
- Preferences

Segments include:
- **VIP**: High-value frequent customers
- **Regular**: Frequent customers
- **Occasional**: Infrequent customers
- **New**: First-time customers

---

## Reports & Analytics

### Daily Sales Report

1. Navigate to **Reports** section
2. Select **Daily Sales**
3. Choose date
4. View:
   - Total revenue
   - Number of orders
   - Average order value
   - Payment method breakdown

### Monthly Performance

1. Go to **Reports** section
2. Select **Monthly Performance**
3. Choose month
4. View:
   - Revenue trends
   - Top-selling items
   - Category performance
   - Staff performance

### Custom Reports

1. Navigate to **Reports** section
2. Click **Custom Report**
3. Select parameters:
   - Date range
   - Report type
   - Filters
4. Generate report
5. Export (PDF, Excel, CSV)

---

## AI & Analytics

### Demand Forecasting

1. Navigate to **AI & Analytics** section
2. Select **Demand Forecasting** tab
3. Click **Generate Forecast**
4. Select forecast period (7, 14, or 30 days)
5. View predicted:
   - Revenue
   - Order volume
   - Staffing needs
   - Inventory requirements

### Menu Optimization

1. Go to **AI & Analytics** section
2. Select **Menu Optimization** tab
3. Click **Analyze Menu**
4. View:
   - High-performing items
   - Underperforming items
   - Recommendations for menu changes
   - Dynamic pricing suggestions

### Customer Intelligence

1. Navigate to **AI & Analytics** section
2. Select **Customer Intelligence** tab
3. Click **Segment Customers**
4. View customer segments and insights
5. Check churn risk for at-risk customers

### Kitchen Intelligence

1. Go to **AI & Analytics** section
2. Select **Kitchen Intelligence** tab
3. Click **Analyze Kitchen**
4. View:
   - Kitchen efficiency metrics
   - Average preparation times
   - Bottlenecks
   - Improvement recommendations

### Waste Reduction

1. Navigate to **AI & Analytics** section
2. Select **Waste Reduction** tab
3. Click **Predict Waste**
4. View:
   - Predicted waste by item
   - Cost impact
   - Reduction recommendations

### Smart Procurement

1. Go to **AI & Analytics** section
2. Select **Smart Procurement** tab
3. Click **Get Recommendations**
4. View:
   - Procurement recommendations
   - Supplier performance
   - Cost optimization suggestions

---

## Settings

### Restaurant Settings

1. Navigate to **Settings** section
2. Click **Restaurant Settings**
3. Update:
   - Restaurant name
   - Currency
   - Tax rate
   - Service charge
4. Save

### User Management

1. Go to **Settings** section
2. Click **Users**
3. To add a user:
   - Click **Add User**
   - Enter user details
   - Assign role and permissions
   - Save
4. To edit/delete:
   - Click on the user
   - Edit or delete as needed

### Role Management

1. Navigate to **Settings** section
2. Click **Roles**
3. View predefined roles:
   - Administrator
   - Manager
   - Cashier
   - Kitchen Staff
   - Waiter
4. Customize permissions for each role

### Notification Settings

1. Go to **Settings** section
2. Click **Notifications**
3. Configure:
   - Email notifications
   - SMS notifications
   - Push notifications
   - Notification preferences

### Integration Settings

1. Navigate to **Settings** section
2. Click **Integrations**
3. Configure third-party integrations:
   - Payment gateways
   - Delivery platforms
   - Accounting software
   - CRM systems

---

## Troubleshooting

### Common Issues

#### Cannot Login

**Solutions:**
- Verify username and password
- Check internet connection
- Clear browser cache
- Contact administrator if account is locked

#### Orders Not Appearing

**Solutions:**
- Refresh the page
- Check filter settings
- Verify date range
- Check if order was submitted successfully

#### Inventory Not Updating

**Solutions:**
- Refresh the page
- Check if changes were saved
- Verify user has permission
- Contact support if issue persists

#### AI Predictions Not Loading

**Solutions:**
- Ensure sufficient historical data (minimum 7 days)
- Check internet connection
- Verify AI service is enabled
- Contact support if issue persists

### Getting Help

- **In-App Help**: Click the help icon (?) in the top right corner
- **Documentation**: Visit https://docs.ebp-restaurant.com
- **Support Email**: support@ebp-restaurant.com
- **Support Phone**: +62 21 1234 5678
- **Live Chat**: Available 24/7 for premium users

### Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| `Ctrl + N` | New Order |
| `Ctrl + S` | Save |
| `Ctrl + F` | Search |
| `Ctrl + P` | Print |
| `Esc` | Close modal |
| `F5` | Refresh page |

---

## Best Practices

### Order Management

- Always verify order details before submitting
- Update order status promptly
- Check special instructions carefully
- Communicate with kitchen for complex orders

### Inventory Management

- Perform regular stock counts
- Set appropriate reorder points
- Monitor expiration dates
- Document all stock adjustments

### Customer Service

- Use customer names when possible
- Note customer preferences
- Follow up on feedback
- Recognize VIP customers

### Data Security

- Never share passwords
- Log out when leaving workstation
- Report suspicious activity
- Keep software updated

---

## Updates & Maintenance

### System Updates

- Updates are deployed automatically during off-peak hours
- Users will be notified of major updates
- System may be briefly unavailable during updates
- Check status page for maintenance schedules

### Data Backup

- Automatic daily backups at 2:00 AM
- Backups retained for 30 days
- On-demand backups available for administrators
- Disaster recovery plan in place

---

## Glossary

- **POS**: Point of Sale
- **ERP**: Enterprise Resource Planning
- **CRM**: Customer Relationship Management
- **SKU**: Stock Keeping Unit
- **LTV**: Lifetime Value
- **AI**: Artificial Intelligence
- **API**: Application Programming Interface

---

## Contact Information

**EBP Restaurant ERP Support**

- **Email**: support@ebp-restaurant.com
- **Phone**: +62 21 1234 5678
- **Website**: https://ebp-restaurant.com
- **Documentation**: https://docs.ebp-restaurant.com
- **Status Page**: https://status.ebp-restaurant.com

**Business Hours**: 24/7 Support for Premium Users  
**Standard Support**: Monday-Friday, 8:00 AM - 6:00 PM (WIB)
