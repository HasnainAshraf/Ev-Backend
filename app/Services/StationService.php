<?php

namespace App\Services;

use App\Models\Station;
use App\Models\Port;
use Illuminate\Support\Collection;

class StationService
{
    /**
     * Get all active stations with their ports
     *
     * @return Collection
     * @throws \Exception
     */
    public function getAllActiveStations(): Collection
    {
        try {
            return Station::with(['activePorts'])->where('is_active', true)->get();
        } catch (\Exception $e) {
            throw new \Exception('Failed to get stations: ' . $e->getMessage());
        }
    }

    /**
     * Get a specific station with its ports
     *
     * @param int $stationId
     * @return Station
     * @throws \Exception
     */
    public function getStation(int $stationId): Station
    {
        try {
            $station = Station::with(['activePorts'])->findOrFail($stationId);
            
            if (!$station->is_active) {
                throw new \Exception('Station is not active.');
            }

            return $station;
        } catch (\Exception $e) {
            throw new \Exception('Failed to get station: ' . $e->getMessage());
        }
    }

    /**
     * Create a new station
     *
     * @param array $data
     * @return Station
     * @throws \Exception
     */
    public function createStation(array $data): Station
    {
        try {
            $station = Station::create($data);

            // Create default ports for the station
            $this->createDefaultPorts($station->id);

            return $station->load('ports');
        } catch (\Exception $e) {
            throw new \Exception('Failed to create station: ' . $e->getMessage());
        }
    }

    /**
     * Update station information
     *
     * @param int $stationId
     * @param array $data
     * @return Station
     * @throws \Exception
     */
    public function updateStation(int $stationId, array $data): Station
    {
        try {
            $station = Station::findOrFail($stationId);
            $station->update($data);

            return $station->load('ports');
        } catch (\Exception $e) {
            throw new \Exception('Failed to update station: ' . $e->getMessage());
        }
    }

    /**
     * Deactivate a station
     *
     * @param int $stationId
     * @return bool
     * @throws \Exception
     */
    public function deactivateStation(int $stationId): bool
    {
        try {
            $station = Station::findOrFail($stationId);
            $station->update(['is_active' => false]);

            // Also deactivate all ports
            $station->ports()->update(['is_active' => false]);

            return true;
        } catch (\Exception $e) {
            throw new \Exception('Failed to deactivate station: ' . $e->getMessage());
        }
    }

    /**
     * Create default ports for a station
     *
     * @param int $stationId
     * @return void
     */
    private function createDefaultPorts(int $stationId): void
    {
        for ($i = 1; $i <= 4; $i++) {
            Port::create([
                'station_id' => $stationId,
                'port_number' => "P{$i}",
                'type' => 'Type 2',
                'power_kw' => 22,
                'is_active' => true,
            ]);
        }
    }

    /**
     * Get stations by location
     *
     * @param string $location
     * @return Collection
     * @throws \Exception
     */
    public function getStationsByLocation(string $location): Collection
    {
        try {
            return Station::with(['activePorts'])
                ->where('location', $location)
                ->where('is_active', true)
                ->get();
        } catch (\Exception $e) {
            throw new \Exception('Failed to get stations by location: ' . $e->getMessage());
        }
    }

    /**
     * Get station statistics
     *
     * @param int $stationId
     * @return array
     * @throws \Exception
     */
    public function getStationStatistics(int $stationId): array
    {
        try {
            $station = Station::with(['ports', 'bookings'])->findOrFail($stationId);

            $totalPorts = $station->ports->count();
            $activePorts = $station->ports->where('is_active', true)->count();
            $totalBookings = $station->bookings->count();
            $pendingBookings = $station->bookings->where('status', 'Pending')->count();
            $acceptedBookings = $station->bookings->where('status', 'Accepted')->count();

            return [
                'station' => $station,
                'statistics' => [
                    'total_ports' => $totalPorts,
                    'active_ports' => $activePorts,
                    'total_bookings' => $totalBookings,
                    'pending_bookings' => $pendingBookings,
                    'accepted_bookings' => $acceptedBookings,
                ]
            ];
        } catch (\Exception $e) {
            throw new \Exception('Failed to get station statistics: ' . $e->getMessage());
        }
    }
} 