<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventPhoto;
use App\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GuestPhotoController extends Controller
{
    /**
     * @OA\Post(
     *     path="/eventos/{slug}/fotos-invitados",
     *     tags={"Eventos Públicos"},
     *     summary="Subir una foto al evento por parte de un invitado",
     *     description="Permite que un invitado suba una foto asociada a un evento usando su código de invitación. Respeta el límite de fotos por invitado y la configuración de aprobación automática.",
     *     operationId="publicUploadGuestPhoto",
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug del evento (por ejemplo, boda-prueba-ana-luis)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"invitation_code", "photo"},
     *                 @OA\Property(
     *                     property="invitation_code",
     *                     type="string",
     *                     description="Código de invitación del invitado"
     *                 ),
     *                 @OA\Property(
     *                     property="caption",
     *                     type="string",
     *                     maxLength=255,
     *                     description="Descripción breve de la foto (opcional)"
     *                 ),
     *                 @OA\Property(
     *                     property="photo",
     *                     type="string",
     *                     format="binary",
     *                     description="Archivo de imagen a subir"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Foto registrada correctamente."
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación, invitación inválida o límite de fotos excedido."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Evento no encontrado o módulo desactivado."
     *     )
     * )
     */
    public function store(string $slug, Request $request)
    {
        // 1) Evento visible
        $event = Event::publicVisible()
            ->where('slug', $slug)
            ->firstOrFail();

        // 2) Módulo activo
        if (! data_get($event->modules, 'guest_photos_upload')) {
            abort(404);
        }

        // 3) Validación básica
        $data = $request->validate([
            'invitation_code' => ['required', 'string'],
            'photo'           => ['required', 'image', 'max:4096'], // ~4MB
            'caption'         => ['nullable', 'string', 'max:255'],
        ]);

        // 4) Invitado por código
        $guest = Guest::query()
            ->where('event_id', $event->id)
            ->where('invitation_code', $data['invitation_code'])
            ->first();

        if (! $guest) {
            $message = 'No pudimos identificar su invitación. Use el enlace personal que recibió.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                ], 422);
            }

            return back()
                ->withErrors(['invitation_code' => $message])
                ->withInput();
        }

        // 5) Límite por invitado
        $settings          = $event->settings ?? [];
        $maxPhotosPerGuest = (int) data_get($settings, 'guest_photos_max_per_guest', 5);

        $currentCount = EventPhoto::query()
            ->where('event_id', $event->id)
            ->where('guest_id', $guest->id)
            ->where('type', 'guest_upload')
            ->count();

        if ($currentCount >= $maxPhotosPerGuest) {
            $message = 'Ya ha subido el número máximo de fotos permitido para este evento.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message'    => $message,
                    'max_photos' => $maxPhotosPerGuest,
                ], 422);
            }

            return back()
                ->withErrors(['photo' => $message])
                ->withInput();
        }

        // 6) Guardar archivo
        $file      = $data['photo'];
        $directory = "events/{$event->id}/guest-photos/originals";
        $filename  = (string) Str::uuid().'.'.$file->getClientOriginalExtension();

        $storedPath = $file->storeAs($directory, $filename, 'public');

        // 7) Estado según auto_approve
        $autoApprove = (bool) data_get($settings, 'guest_photos_auto_approve', false);
        $status      = $autoApprove
            ? EventPhoto::STATUS_APPROVED
            : EventPhoto::STATUS_PENDING;

        // 8) display_order
        $nextDisplayOrder = (int) EventPhoto::query()
                ->where('event_id', $event->id)
                ->where('type', 'guest_upload')
                ->max('display_order') + 1;

        // 9) Registro
        $photo = EventPhoto::create([
            'event_id'       => $event->id,
            'guest_id'       => $guest->id,
            'type'           => 'guest_upload',
            'file_path'      => $storedPath,
            'thumbnail_path' => null,
            'caption'        => $data['caption'] ?? null,
            'status'         => $status,
            'display_order'  => $nextDisplayOrder,
        ]);

        // 10) Respuesta
        if ($request->expectsJson()) {
            return response()->json([
                'message'       => 'Foto subida correctamente.',
                'status'        => $photo->status,
                'photo_id'      => $photo->id,
                'file_url'      => Storage::disk('public')->url($photo->file_path),
                'auto_approved' => $autoApprove,
                'caption'       => $photo->caption,
            ], 200);
        }

        return redirect()
            ->back()
            ->with('guest_photo_success', 'Foto subida correctamente.');
    }
}
