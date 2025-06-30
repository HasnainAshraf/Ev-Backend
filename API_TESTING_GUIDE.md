# API Testing Guide - EV Charging Station Booking System

This guide provides complete testing payloads for all API endpoints in the booking system.

## 🔐 Authentication Endpoints

### 1. Register User
**URL:** `POST /api/register`  
**Content-Type:** `multipart/form-data`  
**Payload:**
```bash
curl -X POST http://localhost:8000/api/register \
  -F "name=John Doe" \
  -F "email=john@example.com" \
  -F "password=Password123!" \
  -F "profile_image=@/path/to/image.jpg"
```

**Alternative (JSON):**
```json
{
  "name": "John Doe",
  "email": "john@example.com", 
  "password": "Password123!",
  "profile_image": "[base64_encoded_image_or_file]"
}
```

**Expected Response:**
```json
{
  "message": "User registered successfully",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "profile_image": "/storage/profile_images/uuid.jpg"
  },
  "token": "1|abc123def456..."
}
```

### 2. Login User
**URL:** `POST /api/login`  
**Content-Type:** `application/json`  
**Payload:**
```json
{
  "email": "john@example.com",
  "password": "Password123!"
}
```

**Expected Response:**
```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "profile_image": "/storage/profile_images/uuid.jpg"
  },
  "token": "1|abc123def456..."
}
```

---

## 🌐 Public Endpoints

### 3. Get All Stations
**URL:** `GET /api/stations`  
**Headers:** None required  
**Payload:** None

**Expected Response:**
```json
{
  "stations": [
    {
      "id": 1,
      "name": "Downtown Charging Station",
      "address": "123 Main Street, Downtown",
      "location": "Downtown",
      "is_active": true,
      "active_ports": [
        {
          "id": 1,
          "station_id": 1,
          "port_number": "P1",
          "type": "Type 2",
          "power_kw": 22,
          "is_active": true
        }
      ]
    }
  ]
}
```

### 4. Check Port Availability
**URL:** `GET /api/ports/{portId}/availability?date=2025-07-01`  
**Headers:** None required  
**Payload:** None

**Example:**
```bash
curl -X GET "http://localhost:8000/api/ports/1/availability?date=2025-07-01"
```

**Expected Response:**
```json
{
  "port": {
    "id": 1,
    "station_id": 1,
    "port_number": "P1",
    "type": "Type 2",
    "power_kw": 22,
    "station": {
      "id": 1,
      "name": "Downtown Charging Station"
    }
  },
  "date": "2025-07-01",
  "booked_slots": ["14:00", "15:00"],
  "available_slots": ["06:00", "06:30", "07:00", "07:30", "08:00"]
}
```

---

## 👤 User Endpoints (Requires Authentication)

### 5. Create Booking Request
**URL:** `POST /api/bookings`  
**Headers:** 
```
Authorization: Bearer {your_token}
Content-Type: application/json
```
**Payload:**
```json
{
  "station_id": 1,
  "port_id": 1,
  "timeslot": "2025-07-01 14:00:00"
}
```

**Expected Response:**
```json
{
  "message": "Booking request created successfully",
  "booking": {
    "id": 1,
    "user_id": 1,
    "station_id": 1,
    "port_id": 1,
    "timeslot": "2025-07-01T14:00:00.000000Z",
    "status": "Pending",
    "created_at": "2025-06-28T12:00:00.000000Z",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "station": {
      "id": 1,
      "name": "Downtown Charging Station"
    },
    "port": {
      "id": 1,
      "port_number": "P1"
    }
  }
}
```

### 6. Get User's Bookings
**URL:** `GET /api/bookings/my-bookings`  
**Headers:** 
```
Authorization: Bearer {your_token}
```

**Optional Query Parameters:**
- `status=Pending` - Filter by status
- `status=Accepted` - Filter by status  
- `status=Rejected` - Filter by status

**Example:**
```bash
curl -X GET "http://localhost:8000/api/bookings/my-bookings?status=Pending" \
  -H "Authorization: Bearer {your_token}"
```

**Expected Response:**
```json
{
  "bookings": [
    {
      "id": 1,
      "user_id": 1,
      "station_id": 1,
      "port_id": 1,
      "timeslot": "2025-07-01T14:00:00.000000Z",
      "status": "Pending",
      "created_at": "2025-06-28T12:00:00.000000Z",
      "station": {
        "id": 1,
        "name": "Downtown Charging Station"
      },
      "port": {
        "id": 1,
        "port_number": "P1"
      }
    }
  ]
}
```

---

## 👨‍💼 Admin Endpoints (Requires Authentication)

### 7. Get All Bookings (Admin)
**URL:** `GET /api/admin/bookings`  
**Headers:** 
```
Authorization: Bearer {your_token}
```

**Optional Query Parameters:**
- `status=Pending` - Filter by status
- `status=Accepted` - Filter by status
- `status=Rejected` - Filter by status

**Example:**
```bash
curl -X GET "http://localhost:8000/api/admin/bookings?status=Pending" \
  -H "Authorization: Bearer {your_token}"
```

**Expected Response:**
```json
{
  "bookings": [
    {
      "id": 1,
      "user_id": 1,
      "station_id": 1,
      "port_id": 1,
      "timeslot": "2025-07-01T14:00:00.000000Z",
      "status": "Pending",
      "created_at": "2025-06-28T12:00:00.000000Z",
      "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
      },
      "station": {
        "id": 1,
        "name": "Downtown Charging Station"
      },
      "port": {
        "id": 1,
        "port_number": "P1"
      }
    }
  ]
}
```

### 8. Update Booking Status (Admin)
**URL:** `PUT /api/admin/bookings/{bookingId}/status`  
**Headers:** 
```
Authorization: Bearer {your_token}
Content-Type: application/json
```
**Payload:**
```json
{
  "status": "Accepted",
  "admin_notes": "Approved - slot available"
}
```

**Alternative (Reject):**
```json
{
  "status": "Rejected", 
  "admin_notes": "Port maintenance scheduled"
}
```

**Example:**
```bash
curl -X PUT "http://localhost:8000/api/admin/bookings/1/status" \
  -H "Authorization: Bearer {your_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "Accepted",
    "admin_notes": "Approved - slot available"
  }'
```

**Expected Response:**
```json
{
  "message": "Booking status updated successfully",
  "booking": {
    "id": 1,
    "user_id": 1,
    "station_id": 1,
    "port_id": 1,
    "timeslot": "2025-07-01T14:00:00.000000Z",
    "status": "Accepted",
    "admin_notes": "Approved - slot available",
    "updated_at": "2025-06-28T13:00:00.000000Z",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "station": {
      "id": 1,
      "name": "Downtown Charging Station"
    },
    "port": {
      "id": 1,
      "port_number": "P1"
    }
  }
}
```

---

## 🧪 Complete Testing Flow

### Step-by-Step Testing Sequence:

1. **Register a new user:**
```bash
curl -X POST http://localhost:8000/api/register \
  -F "name=Test User" \
  -F "email=test@example.com" \
  -F "password=Password123!" \
  -F "profile_image=@/path/to/image.jpg"
```

2. **Login to get token:**
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "Password123!"
  }'
```

3. **Get all stations:**
```bash
curl -X GET http://localhost:8000/api/stations
```

4. **Check port availability:**
```bash
curl -X GET "http://localhost:8000/api/ports/1/availability?date=2025-07-01"
```

5. **Create a booking (use token from step 2):**
```bash
curl -X POST http://localhost:8000/api/bookings \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "station_id": 1,
    "port_id": 1,
    "timeslot": "2025-07-01 14:00:00"
  }'
```

6. **View user's bookings:**
```bash
curl -X GET http://localhost:8000/api/bookings/my-bookings \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

7. **Admin view all bookings:**
```bash
curl -X GET http://localhost:8000/api/admin/bookings \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

8. **Admin accept booking:**
```bash
curl -X PUT "http://localhost:8000/api/admin/bookings/1/status" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "Accepted",
    "admin_notes": "Approved"
  }'
```

---

## 🚨 Error Response Examples

### Validation Error (422):
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "timeslot": [
      "This timeslot is already booked."
    ],
    "port_id": [
      "This port is not available."
    ]
  }
}
```

### Authentication Error (401):
```json
{
  "message": "Unauthenticated."
}
```

### Not Found Error (404):
```json
{
  "message": "No query results for model [App\\Models\\Booking] 999"
}
```

### Server Error (500):
```json
{
  "message": "Failed to create booking request",
  "error": "Database connection failed"
}
```

---

## 📝 Testing Notes

- **Base URL**: Replace `http://localhost:8000` with your actual server URL
- **Token**: Use the token received from login in the `Authorization: Bearer {token}` header
- **Dates**: Use future dates for timeslot testing (e.g., `2025-07-01`)
- **Timeslots**: Available in 30-minute intervals from 6 AM to 10 PM
- **File Upload**: For profile images, use actual image files or base64 encoding
- **Admin Access**: Currently all authenticated users can access admin endpoints

## 🔧 Testing Tools

- **Postman**: Import these requests into Postman for easier testing
- **cURL**: Use the provided cURL commands
- **Insomnia**: Similar to Postman
- **Browser**: For GET requests, you can test directly in browser 