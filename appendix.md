# APPENDIX

## A. Technical Specifications

### A.1 System Requirements

#### Server Requirements
- PHP Version: 7.4 or higher
- MySQL Version: 5.7 or higher
- Apache Server: 2.4 or higher
- Minimum RAM: 4GB
- Storage: 50GB minimum
- Operating System: Linux/Windows Server

#### Client Requirements
- Modern Web Browser (Chrome, Firefox, Safari, Edge)
- Internet Connection: 5Mbps minimum
- Screen Resolution: 1366x768 minimum
- JavaScript Enabled
- Cookies Enabled

### A.2 Database Schema

#### Users Table
```sql
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'agent', 'user') NOT NULL,
    status ENUM('active', 'inactive', 'suspended') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP,
    profile_info JSON
);
```

#### Properties Table
```sql
CREATE TABLE properties (
    property_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    type ENUM('apartment', 'house', 'office', 'land') NOT NULL,
    status ENUM('available', 'sold', 'rented') NOT NULL,
    location JSON,
    features JSON,
    images JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Bookings Table
```sql
CREATE TABLE bookings (
    booking_id INT PRIMARY KEY AUTO_INCREMENT,
    property_id INT,
    user_id INT,
    visit_date DATETIME NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(property_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);
```

#### Payments Table
```sql
CREATE TABLE payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('credit_card', 'bank_transfer', 'mobile_money') NOT NULL,
    status ENUM('pending', 'completed', 'failed') NOT NULL,
    transaction_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id)
);
```

## B. System Architecture Diagrams

### B.1 High-Level Architecture
```
[Client Layer]
    Web Browser
    Mobile Browser
        ↓
[Application Layer]
    PHP Application
    Web Server
        ↓
[Database Layer]
    MySQL Database
    File Storage
```

### B.2 Data Flow Diagram
```
[User] → [Authentication] → [Property Search] → [Booking] → [Payment] → [Confirmation]
   ↑                                                              ↓
   └────────────────── [Notification] ←──────────────────────────┘
```

### B.3 Component Interaction
```
[Frontend] ←→ [API Layer] ←→ [Business Logic] ←→ [Database]
   ↑              ↑              ↑
   └──────────────┴──────────────┘
```

## C. API Documentation

### C.1 Authentication Endpoints

#### Login
```
POST /api/auth/login
Request:
{
    "email": "user@example.com",
    "password": "password"
}
Response:
{
    "status": "success",
    "token": "jwt_token",
    "user": {
        "id": 1,
        "name": "User Name",
        "role": "user"
    }
}
```

#### Register
```
POST /api/auth/register
Request:
{
    "name": "User Name",
    "email": "user@example.com",
    "password": "password"
}
Response:
{
    "status": "success",
    "message": "Registration successful"
}
```

### C.2 Property Endpoints

#### List Properties
```
GET /api/properties
Parameters:
- page: int
- limit: int
- type: string
- status: string
Response:
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "title": "Property Title",
            "price": 100000,
            "type": "apartment"
        }
    ],
    "pagination": {
        "total": 100,
        "page": 1,
        "limit": 10
    }
}
```

## D. Security Implementation

### D.1 Authentication Security
- JWT (JSON Web Tokens) for session management
- Password hashing using bcrypt
- CSRF protection
- Rate limiting
- Input validation

### D.2 Data Security
- SSL/TLS encryption
- Database encryption
- Secure file upload
- XSS protection
- SQL injection prevention

## E. Testing Documentation

### E.1 Unit Tests
```php
class PropertyTest extends TestCase
{
    public function testPropertyCreation()
    {
        $property = new Property([
            'title' => 'Test Property',
            'price' => 100000
        ]);
        $this->assertEquals('Test Property', $property->title);
    }
}
```

### E.2 Integration Tests
```php
class BookingTest extends TestCase
{
    public function testBookingProcess()
    {
        $response = $this->post('/api/bookings', [
            'property_id' => 1,
            'visit_date' => '2024-03-20'
        ]);
        $this->assertEquals(200, $response->status());
    }
}
```

## F. Deployment Guide

### F.1 Server Setup
1. Install required software
2. Configure web server
3. Set up database
4. Configure environment variables
5. Deploy application files

### F.2 Configuration
```env
DB_HOST=localhost
DB_NAME=real_estate
DB_USER=user
DB_PASS=password
APP_ENV=production
APP_DEBUG=false
```

## G. Maintenance Procedures

### G.1 Regular Maintenance
- Database backup
- Log rotation
- Cache clearing
- Security updates
- Performance monitoring

### G.2 Emergency Procedures
- System recovery
- Data restoration
- Security incident response
- Service restoration

## H. User Manual

### H.1 User Roles and Permissions
- Admin: Full system access
- Agent: Property management
- User: Basic access

### H.2 Common Operations
1. Property Search
2. Booking Process
3. Payment Processing
4. Profile Management

## I. Performance Metrics

### I.1 System Performance
- Response Time: < 2 seconds
- Uptime: 99.9%
- Error Rate: < 0.1%
- Concurrent Users: 1000+

### I.2 Database Performance
- Query Response: < 100ms
- Connection Pool: 100
- Cache Hit Rate: 80%
- Index Usage: 95%

## J. Compliance Documentation

### J.1 Data Protection
- GDPR Compliance
- Data Retention Policy
- Privacy Policy
- User Consent Management

### J.2 Security Standards
- OWASP Guidelines
- PCI DSS Compliance
- ISO 27001 Standards
- Security Best Practices 