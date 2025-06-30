<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Station;
use App\Models\Port;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class BookingService
{
    /**
     * Create a new booking request
     *
     * @param int $userId
     * @param array $data
     * @return Booking
     * @throws \Exception
     */
    public function createBooking(int $userId, array $data): Booking
    {
        try {
            $booking = Booking::create([
                'user_id' => $userId,
                'station_id' => $data['station_id'],
                'port_id' => $data['port_id'],
                'timeslot' => $data['timeslot'],
                'status' => 'Pending',
            ]);

            return $booking->load(['user', 'station', 'port']);
        } catch (\Exception $e) {
            throw new \Exception('Failed to create booking: ' . $e->getMessage());
        }
    }

    /**
     * Get user's bookings
     *
     * @param int $userId
     * @param string|null $status
     * @return Collection
     */
    public function getUserBookings(int $userId, ?string $status = null): Collection
    {
        $query = Booking::where('user_id', $userId)
            ->with(['station', 'port']);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get all bookings for admin review
     *
     * @param string|null $status
     * @return Collection
     */
    public function getAllBookings(?string $status = null): Collection
    {
        $query = Booking::with(['user', 'station', 'port']);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Update booking status
     *
     * @param int $bookingId
     * @param string $status
     * @param string|null $adminNotes
     * @return Booking
     * @throws \Exception
     */
    public function updateBookingStatus(int $bookingId, string $status, ?string $adminNotes = null): Booking
    {
        try {
            $booking = Booking::findOrFail($bookingId);

            // Check if the booking is still pending
            if ($booking->status !== 'Pending') {
                throw new \Exception('Booking status cannot be changed. It is no longer pending.');
            }

            $booking->update([
                'status' => $status,
                'admin_notes' => $adminNotes,
            ]);

            return $booking->load(['user', 'station', 'port']);
        } catch (\Exception $e) {
            throw new \Exception('Failed to update booking status: ' . $e->getMessage());
        }
    }

    /**
     * Get port availability for a specific date
     *
     * @param int $portId
     * @param string $date
     * @return array
     * @throws \Exception
     */
    public function getPortAvailability(int $portId, string $date): array
    {
        try {
            $port = Port::with('station')->findOrFail($portId);
            $bookedSlots = $port->getAvailabilityForDate($date);

            // Generate available slots (30-minute intervals from 6 AM to 10 PM)
            $availableSlots = $this->generateAvailableSlots($date, $bookedSlots);

            return [
                'port' => $port,
                'date' => $date,
                'booked_slots' => $bookedSlots,
                'available_slots' => $availableSlots,
            ];
        } catch (\Exception $e) {
            throw new \Exception('Failed to get port availability: ' . $e->getMessage());
        }
    }

    /**
     * Get all active stations with their ports
     *
     * @return Collection
     * @throws \Exception
     */
    public function getAllStations(): Collection
    {
        try {
            return Station::with(['activePorts'])->where('is_active', true)->get();
        } catch (\Exception $e) {
            throw new \Exception('Failed to get stations: ' . $e->getMessage());
        }
    }

    /**
     * Check if a port is available for a specific timeslot
     *
     * @param int $portId
     * @param string $timeslot
     * @return bool
     */
    public function isPortAvailableForTimeslot(int $portId, string $timeslot): bool
    {
        $port = Port::find($portId);
        
        if (!$port || !$port->is_active) {
            return false;
        }

        return $port->isAvailableForTimeslot($timeslot);
    }

    /**
     * Check if user has conflicting bookings
     *
     * @param int $userId
     * @param string $timeslot
     * @return bool
     */
    public function userHasConflictingBookings(int $userId, string $timeslot): bool
    {
        return Booking::where('user_id', $userId)
            ->where('timeslot', $timeslot)
            ->whereIn('status', ['Pending', 'Accepted'])
            ->exists();
    }

    /**
     * Validate booking data
     *
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function validateBookingData(array $data): array
    {
        $errors = [];

        // Check if port exists and belongs to station
        $port = Port::find($data['port_id']);
        if (!$port) {
            $errors[] = 'Port not found.';
        } elseif ($port->station_id != $data['station_id']) {
            $errors[] = 'Port does not belong to the specified station.';
        } elseif (!$port->is_active) {
            $errors[] = 'This port is not available.';
        }

        // Check if timeslot is available
        if (!$this->isPortAvailableForTimeslot($data['port_id'], $data['timeslot'])) {
            $errors[] = 'This timeslot is already booked.';
        }

        // Check if user has conflicting bookings
        if ($this->userHasConflictingBookings(auth()->id(), $data['timeslot'])) {
            $errors[] = 'You already have a booking for this timeslot.';
        }

        if (!empty($errors)) {
            throw new \Exception(implode(' ', $errors));
        }

        return $data;
    }

    /**
     * Generate available time slots
     *
     * @param string $date
     * @param Collection $bookedSlots
     * @return array
     */
    private function generateAvailableSlots(string $date, Collection $bookedSlots): array
    {
        $availableSlots = [];
        $startTime = Carbon::parse($date)->setTime(6, 0);
        $endTime = Carbon::parse($date)->setTime(22, 0);
        $bookedSlotsArray = $bookedSlots->toArray();

        while ($startTime <= $endTime) {
            $timeSlot = $startTime->format('H:i');
            if (!in_array($timeSlot, $bookedSlotsArray)) {
                $availableSlots[] = $timeSlot;
            }
            $startTime->addMinutes(30);
        }

        return $availableSlots;
    }
} 