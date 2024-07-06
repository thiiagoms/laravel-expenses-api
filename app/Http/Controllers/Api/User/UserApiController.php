<?php

namespace App\Http\Controllers\Api\User;

use App\DTO\User\StoreUserDTO;
use App\DTO\User\UpdateUserDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserShowResource;
use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\JsonContent;

class UserApiController extends Controller
{
    /**
     * Init constructor
     */
    public function __construct(private readonly UserService $userService) {}

    #[OA\Post(
        path: '/api/register',
        tags: ['User'],
        summary: 'Register new user',
        description: "Register a new user and receive the user's data upon successful creation.",
        requestBody: new OA\RequestBody(
            description: 'User data for registration',
            required: true,
            content: new JsonContent(
                ref: '#/components/schemas/UserRequest'
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Success response',
                content: new JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        ref: '#/components/schemas/UserResponse'
                    )
                )
            ),
            new OA\Response(
                response: 400,
                description: 'The server could not process the request due to invalid input.'
            ),
        ]
    )]
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): UserShowResource
    {
        $userDTO = StoreUserDTO::from($request->validated());

        $user = $this->userService->create($userDTO);

        return UserShowResource::make($user);
    }

    #[OA\Get(
        path: '/api/user',
        tags: ['User'],
        summary: 'Get authenticated user data',
        security: ['bearerAuth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success response',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        ref: '#/components/schemas/UserResponse'
                    )
                )
            ),
        ]
    )]
    /**
     * Return authenticated user
     */
    public function getUser(Request $request): UserShowResource
    {
        $user = $this->userService->find($request->user('api')->id);

        return UserShowResource::make($user);
    }

    #[OA\Patch(
        path: '/api/user',
        tags: ['User'],
        summary: 'Update authenticated user data',
        security: ['bearerAuth'],
        requestBody: new OA\RequestBody(
            description: 'User data for update',
            required: true,
            content: new JsonContent(
                ref: '#/components/schemas/UserRequest'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success response',
                content: new JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        ref: '#/components/schemas/UserResponse'
                    )
                )
            ),
            new OA\Response(
                response: 400,
                description: 'The server could not process the request due to invalid input.'
            ),
            new OA\Response(
                response: 404,
                description: 'The requested resource could not be found.'
            ),
        ]
    )]
    #[OA\Put(
        path: '/api/user',
        tags: ['User'],
        summary: 'Update authenticated user data',
        security: ['bearerAuth'],
        requestBody: new OA\RequestBody(
            description: 'User data for update',
            required: true,
            content: new JsonContent(
                ref: '#/components/schemas/UserRequest'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success response',
                content: new JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        ref: '#/components/schemas/UserResponse'
                    )
                )
            ),
            new OA\Response(
                response: 400,
                description: 'The server could not process the request due to invalid input.'
            ),
            new OA\Response(
                response: 404,
                description: 'The requested resource could not be found.'
            ),
        ]
    )]
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request): UserShowResource
    {
        $updateUserDTO = UpdateUserDTO::from([
            'id' => $request->user('api')->id,
            ...$request->validated(),
        ]);

        $user = $this->userService->update($updateUserDTO);

        return UserShowResource::make($user);
    }

    #[OA\Delete(
        path: '/api/user',
        tags: ['User'],
        summary: 'Delete authenticated user data',
        security: ['bearerAuth'],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Success response',
            ),
            new OA\Response(
                response: 400,
                description: 'The server could not process the request due to invalid input.'
            ),
            new OA\Response(
                response: 404,
                description: 'The requested resource could not be found.'
            ),
        ]
    )]
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request): JsonResponse
    {
        $this->userService->destroy($request->user('api')->id);

        return response()->json([], Response::HTTP_NO_CONTENT);
    }
}
