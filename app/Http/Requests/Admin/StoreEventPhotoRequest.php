<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventPhotoRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado.
     *
     * Por ahora devolvemos true. Más adelante se puede
     * conectar con Policies o guards específicos de admin.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para subir una foto de evento.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'photo' => [
                'required',
                'file',
                'image',
                'max:5048', // KB ~ 2MB
            ],
            'type' => [
                'nullable',
                'string',
                'in:gallery,hero',
            ],
            'caption' => [
                'nullable',
                'string',
                'max:255',
            ],
            'display_order' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ];
    }

    /**
     * Mensajes personalizados (opcional, pero amigables).
     */
    public function messages(): array
    {
        return [
            'photo.required' => 'Debe seleccionar una imagen para subir.',
            'photo.image'    => 'El archivo debe ser una imagen (jpeg, png, webp, etc.).',
            'photo.max'      => 'La imagen no debe pesar más de 5MB.',
            'type.in'        => 'El tipo de foto debe ser gallery o hero.',
        ];
    }
}
