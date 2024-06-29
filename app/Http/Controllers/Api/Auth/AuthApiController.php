<?php

namespace App\Http\Controllers\Api\Auth;

use App\DTO\Auth\AuthDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\JsonContent;

class AuthApiController extends Controller
{
    /**
     * Init constructor
     */
    public function __construct(private readonly AuthService $authService) {}

    #[OA\Post(
        path: '/api/auth/login',
        tags: ['Auth'],
        summary: 'Authenticate user and return token',
        description: 'Authenticate user by providing their email and password. If the credentials are valid, a token is returned which can be used to authenticate subsequent requests.',
        requestBody: new OA\RequestBody(
            description: 'User data for login',
            required: true,
            content: new JsonContent(
                ref: '#/components/schemas/AuthRequest'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        ref: '#/components/schemas/AuthResponse'
                    )
                )
            ),
            new OA\Response(
                response: 400,
                description: 'The server could not process the request due to invalid input.'
            ),
        ],
    )]
    public function login(AuthRequest $request): JsonResponse
    {
        $authDTO = AuthDTO::from($request->validated());

        return response()->json(['data' => $this->authService->login($authDTO)], Response::HTTP_OK);
    }
}
