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
     *     summary="Sube una foto para un evento (galería, portada o dress_code)",
     *     tags={"Admin - Fotos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         required=true,
     *         description="ID del evento",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"photo"},
     *                 @OA\Property(property="photo", type="string", format="binary", description="Archivo de imagen a subir"),
     *                 @OA\Property(property="type", type="string", enum={"gallery","hero","dress_code"}, description="Tipo de foto"),
     *                 @OA\Property(property="caption", type="string", maxLength=255, nullable=true, description="Texto opcional de la foto"),
     *                 @OA\Property(property="display_order", type="integer", nullable=true, description="Orden de despliegue (si no se envía, se asigna automático)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Foto subida correctamente",
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
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="Sin permisos (rol incorrecto)"),
     *     @OA\Response(response=422, description="Errores de validación")
     * )
     */
    public function store(StoreEventPhotoRequest $request, Event $event): JsonResponse
    {
        $file = $request->file('photo');

        $type = $request->input('type', EventPhoto::TYPE_GALLERY);

        $displayOrder = $request->input('display_order');

        if ($displayOrder === null) {
            $maxOrder = EventPhoto::query()
                ->where('event_id', $event->id)
                ->where('type', $type)
                ->max('display_order');

            $displayOrder = $maxOrder ? ($maxOrder + 1) : 1;
        }

        $disk = Storage::disk('public');
        $directory = "events/{$event->id}/photos/originals";

        $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs($directory, $filename, 'public');

        $thumbnailPath = null;

        $photo = EventPhoto::create([
            'event_id'       => $event->id,
            'guest_id'       => null,
            'type'           => $type,
            'file_path'      => $filePath,
            'thumbnail_path' => $thumbnailPath,
            'caption'        => $request->input('caption'),
            'status'         => EventPhoto::STATUS_APPROVED,
            'display_order'  => (int) $displayOrder,
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
