# EV Charging Station Booking System API

This Laravel-based API provides a complete booking system for electric vehicle charging stations with admin approval workflow.

## 🏗️ Architecture

### Service Layer Pattern
The application follows a **Service Layer Pattern** where all business logic is contained in dedicated service classes:

- **`app/Services/BookingService.php`** - All booking-related business logic
- **`app/Services/StationService.php`** - All station-related business logic  
- **`app/Services/AuthService.php`** - Authentication business logic

### Controller Responsibilities
Controllers are now **thin** and only handle:
- HTTP request/response
- Input validation
- Calling appropriate service methods
- Error handling

### Business Logic Location
All business logic is now properly organized in the **`app/Services/`** folder:

#### BookingService Methods:
- `createBooking()` - Create new booking requests
- `getUserBookings()` - Get user's booking history
- `getAllBookings()` - Get all bookings for admin
- `updateBookingStatus()` - Accept/reject bookings
- `getPortAvailability()` - Check port availability
- `validateBookingData()` - Comprehensive booking validation
- `isPortAvailableForTimeslot()` - Check slot availability
- `userHasConflictingBookings()` - Prevent double bookings

#### StationService Methods:
- `getAllActiveStations()` - Get all active stations
- `getStation()` - Get specific station details
- `createStation()` - Create new stations
- `updateStation()` - Update station information
- `deactivateStation()` - Deactivate stations
- `getStationsByLocation()` - Filter by location
- `getStationStatistics()` - Get station analytics

## 🗂️ Database Schema

### Tables Created:
1. **stations** - Charging stations
2. **ports** - Charging ports within stations  
3. **bookings** - Booking requests and their status

### Key Relationships:
- Stations have many Ports
- Ports belong to Stations
- Users have many Bookings
- Bookings belong to User, Station, and Port

## 🚀 API Endpoints

### Authentication
- `POST /api/register` - Register a new user
- `POST /api/login` - Login user

### Public Endpoints
- `GET /api/stations` - Get all active stations with their ports
- `GET /api/ports/{portId}/availability?date=YYYY-MM-DD` - Check port availability for a specific date

### User Endpoints (Requires Authentication)
- `POST /api/bookings` - Create a new booking request
- `GET /api/bookings/my-bookings` - Get user's bookings (with optional status filter)

### Admin Endpoints (Requires Authentication + Admin)
- `GET /api/admin/bookings` - Get all bookings for admin review
- `PUT /api/admin/bookings/{id}/status` - Update booking status (Accept/Reject)

## 📋 Booking Workflow

### 1. User Booking Process:
1. User checks station availability: `GET /api/stations`
2. User checks port availability: `GET /api/ports/{portId}/availability`
3. User creates booking request: `POST /api/bookings`
4. **BookingService validates:**
   - Port belongs to station
   - Port is active
   - Timeslot is available
   - User doesn't have conflicting bookings
5. Booking is created with "Pending" status

### 2. Admin Review Process:
1. Admin views all pending bookings: `GET /api/admin/bookings?status=Pending`
2. Admin accepts/rejects booking: `PUT /api/admin/bookings/{id}/status`
3. **BookingService updates** booking status and prevents double-booking

### 3. Availability Checking:
- Only "Accepted" bookings block timeslots
- "Pending" and "Rejected" bookings don't block availability
- System prevents booking same timeslot multiple times

## 🔧 Request/Response Examples

### Create Booking Request
```bash
POST /api/bookings
Authorization: Bearer {token}
Content-Type: application/json

{
    "station_id": 1,
    "port_id": 1,
    "timeslot": "2025-07-01 14:00:00"
}
```

**Response:**
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
        "user": {...},
        "station": {...},
        "port": {...}
    }
}
```

### Check Port Availability
```bash
GET /api/ports/1/availability?date=2025-07-01
```

**Response:**
```json
{
    "port": {
        "id": 1,
        "station_id": 1,
        "port_number": "P1",
        "type": "Type 2",
        "power_kw": 22,
        "station": {...}
    },
    "date": "2025-07-01",
    "booked_slots": ["14:00", "15:00"],
    "available_slots": ["06:00", "06:30", "07:00", ...]
}
```

### Update Booking Status (Admin)
```bash
PUT /api/admin/bookings/1/status
Authorization: Bearer {token}
Content-Type: application/json

{
    "status": "Accepted",
    "admin_notes": "Approved - slot available"
}
```

## 🛡️ Security Features

1. **Authentication**: All user and admin endpoints require valid Sanctum tokens
2. **Validation**: Comprehensive request validation with custom rules
3. **Double-booking Prevention**: System checks availability before allowing bookings
4. **Admin Middleware**: Protects admin-only endpoints (currently allows all authenticated users)

## 🧪 Testing the System

### 1. Setup:
```bash
# Run migrations
php artisan migrate

# Seed with test data
php artisan db:seed
```

### 2. Test Flow:
1. Register a user: `POST /api/register`
2. Login: `POST /api/login`
3. Get stations: `GET /api/stations`
4. Check availability: `GET /api/ports/1/availability?date=2025-07-01`
5. Create booking: `POST /api/bookings`
6. View user bookings: `GET /api/bookings/my-bookings`
7. Admin view all bookings: `GET /api/admin/bookings`
8. Admin update status: `PUT /api/admin/bookings/1/status`

## 📁 File Structure

```
app/
├── Services/
│   ├── AuthService.php          # Authentication business logic
│   ├── BookingService.php       # Booking business logic
│   └── StationService.php       # Station business logic
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php   # Thin controller (uses AuthService)
│   │   └── BookingController.php # Thin controller (uses BookingService)
│   └── Requests/
│       ├── BookingRequest.php   # Validation rules
│       └── UpdateBookingStatusRequest.php
└── Models/
    ├── User.php
    ├── Station.php
    ├── Port.php
    └── Booking.php
```

## 📝 Notes

- **Business Logic**: All business logic is in `app/Services/` folder
- **Controllers**: Controllers are thin and only handle HTTP concerns
- **Validation**: Request classes handle input validation
- **Timeslots**: 30-minute intervals from 6 AM to 10 PM
- **Booking Statuses**: "Pending", "Accepted", "Rejected"
- **Availability**: Only "Accepted" bookings block timeslot availability
- **Admin Middleware**: Currently allows all authenticated users (modify as needed)
- **OpenAPI**: All endpoints include comprehensive documentation

## 🔮 Future Enhancements

1. Add user roles and proper admin authentication
2. Implement booking cancellation
3. Add recurring bookings
4. Email notifications for booking status changes
5. Payment integration
6. Real-time availability updates
7. Booking history and analytics
8. Add more service classes for other features 