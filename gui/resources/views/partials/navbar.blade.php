<div class="p-4 lg:p-8">
    <div class="navbar shadow-md rounded-lg main-nav flex justify-between items-center">
        <!-- Navbar Start -->
        <div class="navbar-start flex items-center">
            <a class="btn btn-ghost normal-case text-xl ml-4">DMDD</a>
            <div class="dropdown dropdown-start">
                <label tabindex="0" role="button" class="btn btn-ghost">
                    Test
                </label>
                <ul
                    tabindex="0"
                    class="menu menu-sm dropdown-content bg-base-100 rounded-box z-[1] mt-4 w-52 p-2 shadow">
                    <li><a>Item 1</a></li>
                    <li>
                        <a>Parent</a>
                        <ul class="p-2">
                            <li><a>Submenu 1</a></li>
                            <li><a>Submenu 2</a></li>
                        </ul>
                    </li>
                    <li><a>Item 3</a></li>
                </ul>
            </div>
        </div>

        <!-- Navbar Center -->
        <div class="navbar-center flex-1 flex justify-center">
            <input type="text" placeholder="Search" class="input input-bordered w-48 md:w-auto global-search" />
        </div>

        <!-- Navbar End -->
        <div class="navbar-end flex items-center gap-4">
            <div class="dropdown dropdown-end profile-menu">
                <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar">
                    <div class="w-10 rounded-full">
                        <img alt="User Avatar" src="https://img.daisyui.com/images/stock/photo-1534528741775-53994a69daeb.webp" />
                    </div>
                </div>
                <ul tabindex="0" class="menu menu-sm dropdown-content bg-base-100 rounded-md z-[1] mt-4 w-52 p-2 shadow">
                    <li>
                        <a class="justify-between">
                            Profile
                            <span class="badge">New</span>
                        </a>
                    </li>
                    <li><a>Settings</a></li>
                    <li><a href="{{ route('logout') }}">Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
