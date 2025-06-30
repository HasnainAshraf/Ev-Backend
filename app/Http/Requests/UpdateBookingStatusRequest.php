<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookingStatusRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'status' => 'required|in:Accepted,Rejected',
            'admin_notes' => 'nullable|string|max:500',
        ];
    }

    public function messages()
    {
        return [
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be either Accepted or Rejected.',
            'admin_notes.max' => 'Admin notes cannot exceed 500 characters.',
        ];
    }
} 