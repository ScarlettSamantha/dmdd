<?php
declare(strict_types=1);

namespace Scarlett\DMDD\GUI\Http\Controllers\Gui;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Scarlett\DMDD\GUI\Repositories\LibraryRepository;
use Scarlett\DMDD\GUI\Models\Library;

class LibraryController
{
    private const ROUTE_INDEX = 'gui.libraries.index';
    private const ROUTE_CREATE = 'gui.libraries.create';
    private const ROUTE_STORE = 'gui.libraries.store';
    private const ROUTE_EDIT = 'gui.libraries.edit';
    private const ROUTE_UPDATE = 'gui.libraries.update';
    private const ROUTE_SHOW = 'gui.libraries.show';
    private const ROUTE_DESTROY = 'gui.libraries.destroy';

    private const VIEW_INDEX = 'libraries.index';
    private const VIEW_CREATE = 'libraries.create';
    private const VIEW_SHOW = 'libraries.show';
    private const VIEW_EDIT = 'libraries.edit';

    private LibraryRepository $repository;

    public function __construct(LibraryRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Validate library data.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    private function validateLibraryData(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'isPublic' => 'required|boolean',
            'ownerId' => 'required|string|uuid',
        ]);
    }

    /**
     * Display a listing of the libraries.
     *
     * @return View
     */
    public function index(): View
    {
        /**
         * @var Library[] $libraries
         */
        $libraries = $this->repository->getAll()->data;
        return view(self::VIEW_INDEX, ['libraries' => $libraries]);
    }

    /**
     * Show the form for creating a new library.
     *
     * @return View
     */
    public function create(): View
    {
        return view(self::VIEW_CREATE);
    }

    /**
     * Store a newly created library in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateLibraryData($request);

        try {
            $this->repository->create($validated);
            return redirect()->route(self::ROUTE_INDEX)->with('success', 'Library created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified library.
     *
     * @param string $libraryId
     * @return View
     */
    public function show(string $libraryId): View
    {
        try {
            /**
             * @var Library $library
             */
            $library = $this->repository->getById($libraryId)->data;
            return view(self::VIEW_SHOW, ['library' => $library]);
        } catch (\Exception $e) {
            abort(404, $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified library.
     *
     * @param string $libraryId
     * @return View
     */
    public function edit(string $libraryId): View
    {
        try {
            /**
             * @var Library $library
             */
            $library = $this->repository->getById($libraryId)->data;
            return view(self::VIEW_EDIT, ['library' => $library]);
        } catch (\Exception $e) {
            abort(404, $e->getMessage());
        }
    }

    /**
     * Update the specified library in storage.
     *
     * @param Request $request
     * @param string $libraryId
     * @return RedirectResponse
     */
    public function update(Request $request, string $libraryId): RedirectResponse
    {
        $validated = $this->validateLibraryData($request);

        try {
            $this->repository->update($libraryId, $validated);
            return redirect()->route(self::ROUTE_INDEX)->with('success', 'Library updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified library from storage.
     *
     * @param string $libraryId
     * @return RedirectResponse
     */
    public function destroy(string $libraryId): RedirectResponse
    {
        try {
            $this->repository->delete($libraryId);
            return redirect()->route(self::ROUTE_INDEX)->with('success', 'Library deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
