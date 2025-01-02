<?php
declare(strict_types=1);

namespace Scarlett\DMDD\GUI\Http\Controllers\Gui;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Scarlett\DMDD\GUI\Repositories\SystemUserRepository;
use Scarlett\DMDD\GUI\Models\SystemUser;

class SystemUserController
{
    private const ROUTE_INDEX = 'gui.users.index';
    private const ROUTE_CREATE = 'gui.users.create';
    private const ROUTE_STORE = 'gui.users.store';
    private const ROUTE_EDIT = 'gui.users.edit';
    private const ROUTE_UPDATE = 'gui.users.update';
    private const ROUTE_SHOW = 'gui.users.show';
    private const ROUTE_DESTROY = 'gui.users.destroy';

    private const VIEW_INDEX = 'users.index';
    private const VIEW_CREATE = 'users.create';
    private const VIEW_SHOW = 'users.show';
    private const VIEW_EDIT = 'users.edit';

    private SystemUserRepository $repository;

    public function __construct(SystemUserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Validate user data.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    private function validateUserData(Request $request): array
    {
        return $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'firstName' => 'nullable|string|max:255',
            'lastName' => 'nullable|string|max:255',
            'isActive' => 'required|boolean',
            'isConfirmed' => 'required|boolean',
            'isAdmin' => 'required|boolean',
        ]);
    }

    /**
     * Display a listing of the users.
     *
     * @return View
     */
    public function index(): View
    {
        /**
         * @var SystemUser[] $users
         */
        $users = $this->repository->getAll();
        return view(self::VIEW_INDEX, ['users' => $users]);
    }

    /**
     * Show the form for creating a new user.
     *
     * @return View
     */
    public function create(): View
    {
        return view(self::VIEW_CREATE);
    }

    /**
     * Store a newly created user in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateUserData($request);

        try {
            $this->repository->create($validated);
            return redirect()->route(self::ROUTE_INDEX)->with('success', 'User created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified user.
     *
     * @param string $userId
     * @return View
     */
    public function show(string $userId): View
    {
        try {
            /**
             * @var SystemUser $user
             */
            $user = $this->repository->getById($userId);
            return view(self::VIEW_SHOW, ['user' => $user]);
        } catch (\Exception $e) {
            abort(404, $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified user.
     *
     * @param string $userId
     * @return View
     */
    public function edit(string $userId): View
    {
        try {
            /**
             * @var SystemUser $user
             */
            $user = $this->repository->getById($userId);
            return view(self::VIEW_EDIT, ['user' => $user]);
        } catch (\Exception $e) {
            abort(404, $e->getMessage());
        }
    }

    /**
     * Update the specified user in storage.
     *
     * @param Request $request
     * @param string $userId
     * @return RedirectResponse
     */
    public function update(Request $request, string $userId): RedirectResponse
    {
        $validated = $this->validateUserData($request);

        try {
            $this->repository->update($userId, $validated);
            return redirect()->route(self::ROUTE_INDEX)->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified user from storage.
     *
     * @param string $userId
     * @return RedirectResponse
     */
    public function destroy(string $userId): RedirectResponse
    {
        try {
            /**
             * @var SystemUser $user
             */
            $this->repository->delete($userId);
            return redirect()->route(self::ROUTE_INDEX)->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
