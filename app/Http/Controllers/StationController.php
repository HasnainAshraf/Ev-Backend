<?php

namespace App\Http\Controllers;

use App\Models\Station;
use App\Models\Port;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * @OA\Tag(
 *     name="Station Management",
 *     description="API Endpoints for station and port management"
 * )
 */
class StationController extends Controller
{
    /**
     * Create a new station
     *
     * @OA\Post(
     *     path="/api/stations",
     *     tags={"Station Management"},
     *     summary="Create a new station",
     *     description="Creates a new charging station",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "location", "address"},
     *             @OA\Property(property="name", type="string", example="Downtown Charging Station"),
     *             @OA\Property(property="location", type="string", example="Downtown Mall"),
     *             @OA\Property(property="address", type="string", example="123 Main St, City, State"),
     *             @OA\Property(property="description", type="string", example="Fast charging station in downtown area"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Station created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Station created successfully"),
     *             @OA\Property(property="station", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        try {
            $station = Station::create([
                'name' => $request->name,
                'location' => $request->location,
                'address' => $request->address,
                'description' => $request->description,
                'is_active' => $request->is_active ?? true,
            ]);

            return response()->json([
                'message' => 'Station created successfully',
                'station' => $station,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create station',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a station
     *
     * @OA\Put(
     *     path="/api/stations/{id}",
     *     tags={"Station Management"},
     *     summary="Update a station",
     *     description="Updates an existing charging station",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Station ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Station Name"),
     *             @OA\Property(property="location", type="string", example="Updated Location"),
     *             @OA\Property(property="address", type="string", example="Updated Address"),
     *             @OA\Property(property="description", type="string", example="Updated description"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Station updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Station updated successfully"),
     *             @OA\Property(property="station", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Station not found"
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'location' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string|max:500',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        try {
            $station = Station::findOrFail($id);
            $station->update($request->only(['name', 'location', 'address', 'description', 'is_active']));

            return response()->json([
                'message' => 'Station updated successfully',
                'station' => $station->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update station',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a station
     *
     * @OA\Delete(
     *     path="/api/stations/{id}",
     *     tags={"Station Management"},
     *     summary="Delete a station",
     *     description="Deletes a charging station and all its ports",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Station ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Station deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Station deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Station not found"
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        try {
            $station = Station::findOrFail($id);
            
            // Delete all ports associated with this station
            $station->ports()->delete();
            
            // Delete the station
            $station->delete();

            return response()->json([
                'message' => 'Station deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete station',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add a port to a station
     *
     * @OA\Post(
     *     path="/api/stations/{id}/ports",
     *     tags={"Station Management"},
     *     summary="Add a port to a station",
     *     description="Adds a new charging port to an existing station",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Station ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"port_number", "charging_type"},
     *             @OA\Property(property="port_number", type="string", example="A1"),
     *             @OA\Property(property="charging_type", type="string", enum={"Type 1", "Type 2", "CCS", "CHAdeMO"}, example="Type 2"),
     *             @OA\Property(property="power_output", type="string", example="22kW"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Port added successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Port added successfully"),
     *             @OA\Property(property="port", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Station not found"
     *     )
     * )
     */
    public function addPort(Request $request, $id): JsonResponse
    {
        $request->validate([
            'port_number' => 'required|string|max:50',
            'charging_type' => ['required', Rule::in(['Type 1', 'Type 2', 'CCS', 'CHAdeMO'])],
            'power_output' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        try {
            $station = Station::findOrFail($id);
            
            $port = $station->ports()->create([
                'port_number' => $request->port_number,
                'charging_type' => $request->charging_type,
                'power_output' => $request->power_output,
                'is_active' => $request->is_active ?? true,
            ]);

            return response()->json([
                'message' => 'Port added successfully',
                'port' => $port,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to add port',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a port
     *
     * @OA\Put(
     *     path="/api/stations/{stationId}/ports/{portId}",
     *     tags={"Station Management"},
     *     summary="Update a port",
     *     description="Updates an existing charging port",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="stationId",
     *         in="path",
     *         description="Station ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="portId",
     *         in="path",
     *         description="Port ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="port_number", type="string", example="A1"),
     *             @OA\Property(property="charging_type", type="string", enum={"Type 1", "Type 2", "CCS", "CHAdeMO"}),
     *             @OA\Property(property="power_output", type="string", example="22kW"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Port updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Port updated successfully"),
     *             @OA\Property(property="port", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Station or port not found"
     *     )
     * )
     */
    public function updatePort(Request $request, $stationId, $portId): JsonResponse
    {
        $request->validate([
            'port_number' => 'sometimes|required|string|max:50',
            'charging_type' => ['sometimes', 'required', Rule::in(['Type 1', 'Type 2', 'CCS', 'CHAdeMO'])],
            'power_output' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        try {
            $port = Port::where('station_id', $stationId)
                       ->where('id', $portId)
                       ->firstOrFail();
            
            $port->update($request->only(['port_number', 'charging_type', 'power_output', 'is_active']));

            return response()->json([
                'message' => 'Port updated successfully',
                'port' => $port->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update port',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a port
     *
     * @OA\Delete(
     *     path="/api/stations/{stationId}/ports/{portId}",
     *     tags={"Station Management"},
     *     summary="Delete a port",
     *     description="Deletes a charging port from a station",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="stationId",
     *         in="path",
     *         description="Station ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="portId",
     *         in="string",
     *         description="Port ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Port deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Port deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Station or port not found"
     *     )
     * )
     */
    public function deletePort($stationId, $portId): JsonResponse
    {
        try {
            $port = Port::where('station_id', $stationId)
                       ->where('id', $portId)
                       ->firstOrFail();
            
            $port->delete();

            return response()->json([
                'message' => 'Port deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete port',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
} 