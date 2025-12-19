<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *   name="Auth",
 *   description="Autenticación JWT"
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *   path="/api/auth/login",
     *   tags={"Auth"},
     *   summary="Inicia sesión y retorna token JWT",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"email","password"},
     *       @OA\Property(property="email", type="string", format="email", example="admin.demo@camaleon.test"),
     *       @OA\Property(property="password", type="string", example="password")
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Token emitido",
     *     @OA\JsonContent(
     *       @OA\Property(property="access_token", type="string"),
     *       @OA\Property(property="token_type", type="string", example="bearer"),
     *       @OA\Property(property="expires_in", type="integer", example=3600)
     *     )
     *   ),
     *   @OA\Response(response=401, description="Credenciales inválidas"),
     *   @OA\Response(response=422, description="Errores de validación")
     * )
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Guard JWT
        $token = auth('api')->attempt($credentials);

        if (! $token) {
            return response()->json([
                'message' => 'Credenciales inválidas.',
            ], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * @OA\Get(
     *   path="/api/auth/me",
     *   tags={"Auth"},
     *   summary="Retorna el usuario autenticado",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(
     *     response=200,
     *     description="Usuario actual",
     *     @OA\JsonContent(
     *       @OA\Property(property="id", type="integer"),
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="email", type="string"),
     *       @OA\Property(property="role", type="string", example="admin")
     *     )
     *   ),
     *   @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'id' => $user?->id,
            'name' => $user?->name,
            'email' => $user?->email,
            'role' => $user?->role,
        ]);
    }

    /**
     * @OA\Post(
     *   path="/api/auth/logout",
     *   tags={"Auth"},
     *   summary="Invalida el token actual",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(response=200, description="Logout OK"),
     *   @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return response()->json([
            'message' => 'Logout OK.',
        ]);
    }

    /**
     * @OA\Post(
     *   path="/api/auth/refresh",
     *   tags={"Auth"},
     *   summary="Refresca el token JWT",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(
     *     response=200,
     *     description="Token refrescado",
     *     @OA\JsonContent(
     *       @OA\Property(property="access_token", type="string"),
     *       @OA\Property(property="token_type", type="string", example="bearer"),
     *       @OA\Property(property="expires_in", type="integer", example=3600)
     *     )
     *   ),
     *   @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function refresh(): JsonResponse
    {
        $token = auth('api')->refresh();

        return $this->respondWithToken($token);
    }

    private function respondWithToken(string $token): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ]);
    }
}
