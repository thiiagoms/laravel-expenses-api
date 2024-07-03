<?php

namespace App\Http\Controllers\Api\Expense;

use App\DTO\Expense\StoreExpenseDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Expense\StoreExpenseRequest;
use App\Http\Resources\Expense\ExpenseShowResource;
use App\Models\Expense;
use App\Services\Expense\ExpenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\JsonContent;

class ExpenseApiController extends Controller
{
    public function __construct(private readonly ExpenseService $expenseService) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    #[OA\Post(
        path: '/api/expense',
        tags: ['Expense'],
        summary: 'Create new expense',
        security: ['bearerAuth'],
        description: "Create a new expense and receive the expense's data upon successful creation.",
        requestBody: new OA\RequestBody(
            description: 'Expense data for creation',
            required: true,
            content: new JsonContent(
                ref: '#/components/schemas/ExpenseRequest'
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
                        ref: '#/components/schemas/ExpenseResponse'
                    )
                )
            ),
            new OA\Response(
                response: 400,
                description: 'The server could not process the request due to invalid input.'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            ),
        ]
    )]
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExpenseRequest $request): ExpenseShowResource
    {
        $expenseDTO = StoreExpenseDTO::from([
            'user_id' => $request->user('api')->id,
            ...$request->validated(),
        ]);

        $expense = $this->expenseService->create($expenseDTO);

        return ExpenseShowResource::make($expense);
    }

    #[OA\Get(
        path: '/api/expense/{id}',
        tags: ['Expense'],
        summary: 'Retrieves the detailed expense record for the authenticated user.',
        security: ['bearerAuth'],
        description: 'Retrieves the detailed expense record for the authenticated user but only expenses that the authenticated user has permission to view will be returned.',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'The id (uuid) of the expense record to be retrieved.',
                required: true,
                schema: new OA\Schema(
                    type: 'string'
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success response',
                content: new JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        ref: '#/components/schemas/ExpenseResponse'
                    )
                )
            ),
            new OA\Response(
                response: 400,
                description: 'The server could not process the request due to invalid input.'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            ),
        ]
    )]
    /**
     * Display the specified resource.
     */
    public function show(Expense $expense): ExpenseShowResource
    {
        Gate::authorize('view', $expense);

        return ExpenseShowResource::make($expense);
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
