<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // The controller handles ownership check via query scope,
        // so we can allow here, or double check.
        // For simplicity and to match standard Laravel, we authorize here if needed,
        // but since we haven't fetched the event model yet in the route binding logic (we use ID),
        // we'll leave it true and let controller handle 404/403.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'event_date' => ['sometimes', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time'   => ['nullable', 'date_format:H:i', 'after:start_time'],
            
            'modules' => ['sometimes', 'array'],
            'modules.*' => ['boolean'],

            // Settings validation
            'settings' => ['sometimes', 'array'],
            'settings.playlist_max_songs_per_guest' => ['integer', 'min:0', 'max:10'],
            'settings.playlist_max_votes_per_guest' => ['integer', 'min:0', 'max:20'],
            'settings.guest_photos_max_per_guest' => ['integer', 'min:0', 'max:50'],
            'settings.guest_photos_auto_approve' => ['boolean'],
            'settings.gifts_hide_purchased_from_public' => ['boolean'],
        ];
    }
}
