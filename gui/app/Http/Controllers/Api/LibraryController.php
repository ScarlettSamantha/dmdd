<?php

declare(strict_types=1);

namespace Scarlett\DMDD\GUI\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Scarlett\DMDD\GUI\Http\Controllers\ApiController;
use Scarlett\DMDD\GUI\Repositories\LibraryRepository;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;

class LibraryController extends ApiController
{
    protected LibraryRepository $repository;
    protected ValidationFactory $validationFactory;

    public function __construct(LibraryRepository $repository, ValidationFactory $validationFactory)
    {
        $this->repository = $repository;
        $this->validationFactory = $validationFactory;
    }

    /**
     * GET /api/libraries
     * Retrieve all libraries.
     */
    public function index(): JsonResponse
    {
        $libraries = $this->repository->getAll();

        return $this->formatResponse($libraries);
    }

    /**
     * POST /api/libraries
     * Create a new library.
     */
    public function store(Request $request): JsonResponse
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'isPublic' => 'required|boolean',
            'ownerId' => 'required|string|uuid',
        ];

        $validatedData = $this->validateRequest($request, $rules);

        $library = $this->repository->create($validatedData);

        return $this->formatResponse($library, Response::HTTP_CREATED);
    }

    /**
     * GET /api/libraries/{library_id}
     * Retrieve a library by ID.
     */
    public function show(string $library_id): JsonResponse
    {
        $library = $this->repository->getById($library_id);

        return $this->formatResponse($library);
    }

    /**
     * PUT /api/libraries/{library_id}
     * Update an existing library.
     */
    public function update(Request $request, string $library_id): JsonResponse
    {
        $rules = [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'isPublic' => 'sometimes|boolean',
            'ownerId' => 'sometimes|string|uuid',
        ];

        $validatedData = $this->validateRequest($request, $rules);

        $library = $this->repository->update($library_id, $validatedData);

        return $this->formatResponse($library);
    }

    /**
     * DELETE /api/libraries/{library_id}
     * Delete a library by ID.
     */
    public function destroy(string $library_id): JsonResponse
    {
        $this->repository->delete($library_id);

        return $this->formatResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Validate a request with the given rules.
     */
    protected function validateRequest(Request $request, array $rules): array
    {
        try {
            return $this->validationFactory->make($request->all(), $rules)->validate();
        } catch (ValidationException $e) {
            throw new ValidationException($e, new JsonResponse([
                'status' => 'error',
                'errors' => $e->errors(),
            ], Response::HTTP_BAD_REQUEST));
        }
    }
}
