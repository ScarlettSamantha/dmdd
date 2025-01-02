<?php
declare(strict_types=1);

namespace Scarlett\DMDD\GUI\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Scarlett\DMDD\GUI\Http\Controllers\ApiController;
use Scarlett\DMDD\GUI\Repositories\LibraryItemRepository;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;

class LibraryItemController extends ApiController
{
    protected LibraryItemRepository $repository;
    protected ValidationFactory $validationFactory;

    public function __construct(LibraryItemRepository $repository, ValidationFactory $validationFactory)
    {
        $this->repository = $repository;
        $this->validationFactory = $validationFactory;
    }

    /**
     * GET /api/libraries/{library_id}/items
     * Retrieves all library items for a specific library via LibraryItemRepository.
     */
    public function index(string $library_id): JsonResponse
    {
        $response = $this->repository->getAll($library_id);

        return $this->formatResponse($response);
    }

    /**
     * POST /api/libraries/{library_id}/items
     * Create a new library item for a specific library.
     */
    public function store(Request $request, string $library_id): JsonResponse
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'isPublic' => 'required|boolean',
            'ownerId' => 'required|string|uuid',
            'mimeType' => 'required|string|max:150',
            'fileSize' => 'required|integer|min:1',
            'filePath' => 'required|string|max:255',
        ];

        $validatedData = $this->validateRequest($request, $rules);

        $response = $this->repository->create($library_id, $validatedData);

        return $this->formatResponse($response, Response::HTTP_CREATED);
    }

    /**
     * GET /api/libraries/{library_id}/items/{library_item_id}
     * Retrieve a single library item by ID for a specific library.
     */
    public function show(string $library_id, string $library_item_id): JsonResponse
    {
        $response = $this->repository->getById($library_id, $library_item_id);

        return $this->formatResponse($response);
    }

    /**
     * PUT /api/libraries/{library_id}/items/{library_item_id}
     * Update an existing library item for a specific library.
     */
    public function update(Request $request, string $library_id, string $library_item_id): JsonResponse
    {
        $rules = [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'isPublic' => 'sometimes|boolean',
            'ownerId' => 'sometimes|string|uuid',
            'mimeType' => 'sometimes|string|max:150',
            'fileSize' => 'sometimes|integer|min:1',
            'filePath' => 'sometimes|string|max:255',
        ];

        $validatedData = $this->validateRequest($request, $rules);

        $response = $this->repository->update($library_id, $library_item_id, $validatedData);

        return $this->formatResponse($response);
    }

    /**
     * DELETE /api/libraries/{library_id}/items/{library_item_id}
     * Deletes a library item by ID for a specific library.
     */
    public function destroy(string $library_id, string $library_item_id): JsonResponse
    {
        $response = $this->repository->delete($library_id, $library_item_id);

        return $this->formatResponse($response, Response::HTTP_NO_CONTENT);
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
