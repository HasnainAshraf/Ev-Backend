# Quick API Testing Guide

Your Laravel application is now running at: **http://localhost:8000**

## 🚀 Quick Test Sequence

### 1. Register User
```bash
curl -X POST http://localhost:8000/api/register \
  -F "name=Test User" \
  -F "email=test@example.com" \
  -F "password=Password123!" \
  -F "profile_image=@/path/to/image.jpg"
```

### 2. Login (Get Token)
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "Password123!"
  }'
```

### 3. Get Stations
```bash
curl -X GET http://localhost:8000/api/stations
```

### 4. Check Port Availability
```bash
curl -X GET "http://localhost:8000/api/ports/1/availability?date=2025-07-01"
```

### 5. Create Booking (Use token from step 2)
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

### 6. View User Bookings
```bash
curl -X GET http://localhost:8000/api/bookings/my-bookings \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### 7. Admin View All Bookings
```bash
curl -X GET http://localhost:8000/api/admin/bookings \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### 8. Admin Accept Booking
```bash
curl -X PUT "http://localhost:8000/api/admin/bookings/1/status" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "Accepted",
    "admin_notes": "Approved"
  }'
```

## 📋 All API Endpoints

| Method | URL | Auth Required | Description |
|--------|-----|---------------|-------------|
| POST | `/api/register` | No | Register new user |
| POST | `/api/login` | No | Login user |
| GET | `/api/stations` | No | Get all stations |
| GET | `/api/ports/{id}/availability` | No | Check port availability |
| POST | `/api/bookings` | Yes | Create booking |
| GET | `/api/bookings/my-bookings` | Yes | Get user bookings |
| GET | `/api/admin/bookings` | Yes | Get all bookings (admin) |
| PUT | `/api/admin/bookings/{id}/status` | Yes | Update booking status (admin) |

## 🔧 Testing with Postman

1. **Import these requests into Postman**
2. **Set base URL**: `http://localhost:8000`
3. **For authenticated requests**: Add header `Authorization: Bearer {token}`
4. **For JSON requests**: Set `Content-Type: application/json`

## 🎯 Sample Payloads

### Register User
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "Password123!",
  "profile_image": "[file or base64]"
}
```

### Login
```json
{
  "email": "john@example.com",
  "password": "Password123!"
}
```

### Create Booking
```json
{
  "station_id": 1,
  "port_id": 1,
  "timeslot": "2025-07-01 14:00:00"
}
```

### Update Booking Status
```json
{
  "status": "Accepted",
  "admin_notes": "Approved - slot available"
}
```

## 🚨 Common Issues

- **Token missing**: Add `Authorization: Bearer {token}` header
- **Invalid timeslot**: Use future dates and times between 6 AM - 10 PM
- **Port not found**: Check available ports with `/api/stations` first
- **Already booked**: Check availability before booking

## 📱 Alternative: Browser Testing

For GET requests, you can test directly in browser:
- `http://localhost:8000/api/stations`
- `http://localhost:8000/api/ports/1/availability?date=2025-07-01`

Your booking system is ready for testing! 🚀 