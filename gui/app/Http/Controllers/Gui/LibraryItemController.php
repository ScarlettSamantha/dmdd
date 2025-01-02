<?php
declare(strict_types=1);

namespace Scarlett\DMDD\GUI\Http\Controllers\Gui;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Scarlett\DMDD\GUI\Repositories\LibraryItemRepository;
use Scarlett\DMDD\GUI\Models\LibraryItem;

class LibraryItemController
{
    private const ROUTE_INDEX = 'gui.library_items.index';
    private const ROUTE_CREATE = 'gui.library_items.create';
    private const ROUTE_STORE = 'gui.library_items.store';
    private const ROUTE_EDIT = 'gui.library_items.edit';
    private const ROUTE_UPDATE = 'gui.library_items.update';
    private const ROUTE_SHOW = 'gui.library_items.show';
    private const ROUTE_DESTROY = 'gui.library_items.destroy';

    private const VIEW_INDEX = 'library_items.index';
    private const VIEW_CREATE = 'library_items.create';
    private const VIEW_SHOW = 'library_items.show';
    private const VIEW_EDIT = 'library_items.edit';

    private LibraryItemRepository $repository;

    public function __construct(LibraryItemRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Validate library item data.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    private function validateLibraryItemData(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'isPublic' => 'required|boolean',
            'ownerId' => 'required|string|uuid',
            'libraryId' => 'required|string|uuid',
            'mimeType' => 'required|string|max:150',
            'fileSize' => 'required|integer|min:1',
            'filePath' => 'required|string|max:255',
        ]);
    }

    /**
     * Display a listing of the library items.
     *
     * @return View
     */
    public function index(string $libraryId): View
    {
        /**
         * @var LibraryItem[] $libraryItems
         */
        $libraryItems = $this->repository->getAll($libraryId)->data;
        return view(self::VIEW_INDEX, ['libraryItems' => $libraryItems]);
    }

    /**
     * Show the form for creating a new library item.
     *
     * @return View
     */
    public function create(): View
    {
        return view(self::VIEW_CREATE);
    }

    /**
     * Store a newly created library item in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(string $libraryId, Request $request): RedirectResponse
    {
        $validated = $this->validateLibraryItemData($request);

        try {
            $this->repository->create($libraryId, $validated);
            return redirect()->route(self::ROUTE_INDEX)->with('success', 'Library item created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified library item.
     *
     * @param string $libraryItemId
     * @return View
     */
    public function show(string $libraryId, string $libraryItemId): View
    {
        try {
            /**
             * @var LibraryItem|null $libraryItem
             */
            $libraryItem = $this->repository->getById($libraryId, $libraryItemId)->data;
            return view(self::VIEW_SHOW, ['libraryItem' => $libraryItem]);
        } catch (\Exception $e) {
            abort(404, $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified library item.
     *
     * @param string $libraryItemId
     * @return View
     */
    public function edit(string $libraryId, string $libraryItemId): View
    {
        try {
            /**
             * @var LibraryItem $libraryItem
             */
            $libraryItem = $this->repository->getById($libraryId, $libraryItemId)->data;
            return view(self::VIEW_EDIT, ['libraryItem' => $libraryItem]);
        } catch (\Exception $e) {
            abort(404, $e->getMessage());
        }
    }

    /**
     * Update the specified library item in storage.
     *
     * @param Request $request
     * @param string $libraryId
     * @param string $libraryItemId
     * @return RedirectResponse
     */
    public function update(Request $request, $libraryId, string $libraryItemId): RedirectResponse
    {
        $validated = $this->validateLibraryItemData($request);

        try {
            $this->repository->update($libraryId, $libraryItemId, $validated);
            return redirect()->route(self::ROUTE_INDEX)->with('success', 'Library item updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified library item from storage.
     *
     * @param string $libraryId
     * @param string $libraryItemId
     * @return RedirectResponse
     */
    public function destroy(string $libraryId, string $libraryItemId): RedirectResponse
    {
        try {
            $this->repository->delete($libraryId, $libraryItemId);
            return redirect()->route(self::ROUTE_INDEX)->with('success', 'Library item deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
