<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingRequest;
use App\Http\Requests\UpdateBookingStatusRequest;
use App\Services\BookingService;
use App\Services\StationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Bookings",
 *     description="API Endpoints for booking management"
 * )
 */
class BookingController extends Controller
{
    protected $bookingService;
    protected $stationService;

    public function __construct(BookingService $bookingService, StationService $stationService)
    {
        $this->bookingService = $bookingService;
        $this->stationService = $stationService;
    }

    /**
     * Create a new booking request
     *
     * @OA\Post(
     *     path="/api/bookings",
     *     tags={"Bookings"},
     *     summary="Create a new booking request",
     *     description="Creates a new booking request for a charging slot",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"station_id", "port_id", "timeslot"},
     *             @OA\Property(property="station_id", type="integer", example=1),
     *             @OA\Property(property="port_id", type="integer", example=1),
     *             @OA\Property(property="timeslot", type="string", format="date-time", example="2025-07-01 14:00:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Booking request created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Booking request created successfully"),
     *             @OA\Property(property="booking", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(BookingRequest $request): JsonResponse
    {
        try {
            $booking = $this->bookingService->createBooking(auth()->id(), $request->validated());

            return response()->json([
                'message' => 'Booking request created successfully',
                'booking' => $booking,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create booking request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's bookings
     *
     * @OA\Get(
     *     path="/api/bookings/my-bookings",
     *     tags={"Bookings"},
     *     summary="Get user's bookings",
     *     description="Retrieves all bookings for the authenticated user",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"Pending", "Accepted", "Rejected"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bookings retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="bookings", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function myBookings(Request $request): JsonResponse
    {
        try {
            $bookings = $this->bookingService->getUserBookings(
                auth()->id(), 
                $request->query('status')
            );

            return response()->json([
                'bookings' => $bookings,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve bookings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all bookings
     *
     * @OA\Get(
     *     path="/api/bookings",
     *     tags={"Bookings"},
     *     summary="Get all bookings",
     *     description="Retrieves all bookings",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"Pending", "Accepted", "Rejected"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bookings retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="bookings", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $bookings = $this->bookingService->getAllBookings($request->query('status'));

            return response()->json([
                'bookings' => $bookings,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve bookings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update booking status
     *
     * @OA\Put(
     *     path="/api/bookings/{id}/status",
     *     tags={"Bookings"},
     *     summary="Update booking status",
     *     description="Updates the status of a booking request",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Booking ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"Accepted", "Rejected"}),
     *             @OA\Property(property="admin_notes", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Booking status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="booking", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Booking not found"
     *     )
     * )
     */
    public function updateStatus(UpdateBookingStatusRequest $request, $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->updateBookingStatus(
                $id,
                $request->status,
                $request->admin_notes
            );

            return response()->json([
                'message' => 'Booking status updated successfully',
                'booking' => $booking,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update booking status',
                'error' => $e->getMessage(),
            ], $e->getMessage() === 'Booking status cannot be changed. It is no longer pending.' ? 422 : 500);
        }
    }

    /**
     * Get available slots for a port
     *
     * @OA\Get(
     *     path="/api/ports/{portId}/availability",
     *     tags={"Bookings"},
     *     summary="Get available slots for a port",
     *     description="Retrieves available timeslots for a specific port",
     *     @OA\Parameter(
     *         name="portId",
     *         in="path",
     *         description="Port ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Date to check availability (YYYY-MM-DD)",
     *         required=true,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Availability retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="port", type="object"),
     *             @OA\Property(property="date", type="string"),
     *             @OA\Property(property="booked_slots", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="available_slots", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function getPortAvailability(Request $request, $portId): JsonResponse
    {
        try {
            $date = $request->query('date', now()->format('Y-m-d'));
            $availability = $this->bookingService->getPortAvailability($portId, $date);

            return response()->json($availability);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get port availability',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all stations with their ports
     *
     * @OA\Get(
     *     path="/api/stations",
     *     tags={"Stations"},
     *     summary="Get all stations",
     *     description="Retrieves all charging stations with their ports",
     *     @OA\Response(
     *         response=200,
     *         description="Stations retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="stations", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function getStations(): JsonResponse
    {
        try {
            $stations = $this->stationService->getAllActiveStations();

            return response()->json([
                'stations' => $stations,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get stations',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
} 