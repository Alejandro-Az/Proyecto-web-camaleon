<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="API Eventos Camaleón",
 *     description="Documentación de los endpoints de la plataforma de eventos tipo camaleón (boda, XV, cumpleaños, etc.)."
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Servidor principal de la API"
 * )
 *
 * @OA\Tag(
 *     name="Eventos Públicos",
 *     description="Endpoints públicos relacionados con la visualización de eventos."
 * )
 *
 * @OA\Tag(
 *     name="RSVP",
 *     description="Endpoints para la confirmación de asistencia de invitados (RSVP)."
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
