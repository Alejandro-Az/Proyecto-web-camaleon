<?php

namespace App\Swagger;

/**
 * @OA\Info(
 *   title="Camaleón API",
 *   version="1.0.0"
 * )
 *
 * @OA\SecurityScheme(
 *   securityScheme="bearerAuth",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="JWT",
 *   description="Use: Authorization: Bearer {token}"
 * )
 */
final class OpenApi
{
    // Solo annotations.
}
