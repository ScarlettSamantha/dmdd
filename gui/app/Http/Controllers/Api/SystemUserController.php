<?php
declare(strict_types=1);

namespace Scarlett\DMDD\GUI\App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Scarlett\DMDD\GUI\Http\Controllers\ApiController;
use Scarlett\DMDD\GUI\Repositories\SystemUserRepository;
use Scarlett\DMDD\GUI\Services\BackendIntegrationServiceResponse;

class SystemUserController extends ApiController
{
    protected SystemUserRepository $repository;

    public function __construct(SystemUserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * GET /api/system/users
     * Retrieves all users via SystemUserRepository.
     */
    public function index(): JsonResponse
    {
        $response = $this->repository->getAll();

        return $this->formatResponse($response);
    }

    /**
     * POST /api/system/users
     * Create a new user, with minimal local validation as an example.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'username'     => 'required|string|max:150',
                'email'        => 'required|email',
                'password'     => 'required|string|min:8',
                'first_name'   => 'sometimes|string|max:100|nullable',
                'last_name'    => 'sometimes|string|max:100|nullable',
                'is_active'    => 'sometimes|boolean',
                'is_confirmed' => 'sometimes|boolean',
                'is_admin'     => 'sometimes|boolean',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'errors' => $e->errors(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $response = $this->repository->create($validatedData);

        return $this->formatResponse($response, Response::HTTP_CREATED);
    }

    /**
     * GET /api/system/users/{user_id}
     * Retrieve a single user by ID.
     */
    public function show(string $user_id): JsonResponse
    {
        $response = $this->repository->getById(userId: $user_id);

        return $this->formatResponse($response);
    }

    /**
     * PUT /api/system/users/{user_id}
     * Update an existing user.
     */
    public function update(Request $request, string $user_id): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'username'     => 'sometimes|string|max:150',
                'email'        => 'sometimes|email',
                'password'     => 'sometimes|string|min:8',
                'first_name'   => 'sometimes|string|max:100|nullable',
                'last_name'    => 'sometimes|string|max:100|nullable',
                'is_active'    => 'sometimes|boolean',
                'is_confirmed' => 'sometimes|boolean',
                'is_admin'     => 'sometimes|boolean',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'errors' => $e->errors(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $response = $this->repository->update($user_id, $validatedData);

        return $this->formatResponse($response);
    }

    /**
     * DELETE /api/system/users/{user_id}
     * Deletes a user by ID.
     */
    public function destroy(string $user_id): JsonResponse
    {
        $response = $this->repository->delete($user_id);

        return $this->formatResponse($response, Response::HTTP_NO_CONTENT);
    }

    /**
     * POST /api/system/users/{user_id}/activate
     * Activates a user (is_active = true).
     */
    public function activate(string $user_id): JsonResponse
    {
        $response = $this->repository->activate($user_id);

        return $this->formatResponse($response);
    }

    /**
     * POST /api/system/users/{user_id}/deactivate
     * Deactivates a user (is_active = false).
     */
    public function deactivate(string $user_id): JsonResponse
    {
        $response = $this->repository->deactivate($user_id);

        return $this->formatResponse($response);
    }

    /**
     * POST /api/system/users/{user_id}/confirm
     * Confirms a user (is_confirmed = true).
     */
    public function confirm(string $user_id): JsonResponse
    {
        $response = $this->repository->confirm($user_id);

        return $this->formatResponse($response);
    }

    /**
     * POST /api/system/users/{user_id}/unconfirm
     * Unconfirms a user (is_confirmed = false).
     */
    public function unconfirm(string $user_id): JsonResponse
    {
        $response = $this->repository->unconfirm($user_id);

        return $this->formatResponse($response);
    }

    /**
     * Helper function to format responses from BackendIntegrationServiceResponse.
     */
    private function formatResponse(BackendIntegrationServiceResponse $response, int $defaultStatusCode = Response::HTTP_OK): JsonResponse
    {
        return response()->json(
            $response->toArray(),
            $response->status_code ?? $defaultStatusCode
        );
    }
}