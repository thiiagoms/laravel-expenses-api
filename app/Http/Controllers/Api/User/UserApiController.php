<?php

namespace App\Http\Controllers\Api\User;

use App\DTO\User\StoreUserDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Resources\User\UserShowResource;
use App\Services\User\UserService;
use Illuminate\Http\Request;
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

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
