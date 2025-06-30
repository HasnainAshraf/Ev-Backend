<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\BookingService;

class BookingRequest extends FormRequest
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'station_id' => 'required|exists:stations,id',
            'port_id' => 'required|exists:ports,id',
            'timeslot' => 'required|date|after:now',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            try {
                // Use the service to validate booking data
                $this->bookingService->validateBookingData($this->all());
            } catch (\Exception $e) {
                $validator->errors()->add('booking', $e->getMessage());
            }
        });
    }

    public function messages()
    {
        return [
            'station_id.required' => 'Station ID is required.',
            'station_id.exists' => 'Selected station does not exist.',
            'port_id.required' => 'Port ID is required.',
            'port_id.exists' => 'Selected port does not exist.',
            'timeslot.required' => 'Timeslot is required.',
            'timeslot.date' => 'Timeslot must be a valid date.',
            'timeslot.after' => 'Timeslot must be in the future.',
        ];
    }
} 