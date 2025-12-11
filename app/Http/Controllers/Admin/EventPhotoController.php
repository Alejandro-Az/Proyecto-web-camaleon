<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEventPhotoRequest;
use App\Models\Event;
use App\Models\EventPhoto;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventPhotoController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/admin/events/{event}/photos",
     *     tags={"Eventos (Admin)"},
     *     summary="Subir una foto a la galería de un evento",
     *     description="Permite subir una imagen (multipart/form-data) para la galería o portada de un evento. Recomendado proteger este endpoint con autenticación (por ejemplo, auth:sanctum) para uso exclusivo del organizador o administrador.",
     *     operationId="adminUploadEventPhoto",
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         required=true,
     *         description="ID del evento al que se asociará la foto",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"photo"},
     *                 @OA\Property(
     *                     property="photo",
     *                     type="string",
     *                     format="binary",
     *                     description="Archivo de imagen a subir"
     *                 ),
     *                 @OA\Property(
     *                     property="type",
     *                     type="string",
     *                     description="Tipo de foto (galería o portada)",
     *                     enum={"gallery","hero"}
     *                 ),
     *                 @OA\Property(
     *                     property="caption",
     *                     type="string",
     *                     maxLength=255,
     *                     description="Texto descriptivo opcional de la foto"
     *                 ),
     *                 @OA\Property(
     *                     property="display_order",
     *                     type="integer",
     *                     description="Orden opcional de despliegue en la galería"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Foto creada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="event_id", type="integer"),
     *             @OA\Property(property="type", type="string"),
     *             @OA\Property(property="caption", type="string", nullable=true),
     *             @OA\Property(property="status", type="string"),
     *             @OA\Property(property="display_order", type="integer"),
     *             @OA\Property(property="file_url", type="string"),
     *             @OA\Property(property="thumbnail_url", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación en la carga de la foto"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Evento no encontrado"
     *     )
     * )
     */
    public function store(StoreEventPhotoRequest $request, Event $event): JsonResponse
    {
        $file = $request->file('photo');

        // Tipo de foto: por defecto 'gallery'
        $type = $request->input('type', EventPhoto::TYPE_GALLERY);

        // Orden: si no viene, tomamos el siguiente consecutivo
        $displayOrder = $request->input('display_order');

        if ($displayOrder === null) {
            $maxOrder = EventPhoto::query()
                ->where('event_id', $event->id)
                ->where('type', $type)
                ->max('display_order');

            $displayOrder = ($maxOrder ?? 0) + 1;
        }

        // Guardar archivo en storage/app/public/events/{id}/photos/originals
        $disk = Storage::disk('public');
        $directory = "events/{$event->id}/photos/originals";

        $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();

        $filePath = $file->storeAs($directory, $filename, 'public');

        // Por ahora no generamos thumbnail real; usamos sólo file_path.
        $thumbnailPath = null;

        $photo = EventPhoto::create([
            'event_id'      => $event->id,
            'guest_id'      => null,
            'type'          => $type,
            'file_path'     => $filePath,
            'thumbnail_path'=> $thumbnailPath,
            'caption'       => $request->input('caption'),
            'status'        => EventPhoto::STATUS_APPROVED,
            'display_order' => $displayOrder,
        ]);

        return response()->json([
            'id'            => $photo->id,
            'event_id'      => $photo->event_id,
            'type'          => $photo->type,
            'caption'       => $photo->caption,
            'status'        => $photo->status,
            'display_order' => $photo->display_order,
            'file_url'      => $disk->url($photo->file_path),
            'thumbnail_url' => $photo->thumbnail_path ? $disk->url($photo->thumbnail_path) : null,
        ], 201);
    }
}
