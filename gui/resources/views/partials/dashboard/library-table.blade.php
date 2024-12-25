<table class="table dmdd-table">
    <thead>
        <tr>
            <th>
                <label>
                    <input type="checkbox" class="checkbox" />
                </label>
            </th>
            <th>Name</th>
            <th>Job</th>
            <th>Favorite Color</th>
            <th class="dmdd-table-actions">
              @if(!empty($actions) && is_iterable($actions))
                  @foreach($actions as $action)
                      {!! $action !!}
                  @endforeach
              @endif
            </th>
        </tr>
    </thead>
    <tbody>
        <!-- Use table-row-checkbox-action component -->
        <x-table-row-checkbox-action 
            avatar="https://img.daisyui.com/images/profile/demo/2@94.webp" 
            name="Hart Hagerty" 
            location="United States" 
            job="Zemlak, Daniel and Leannon" 
            tag="Desktop Support Technician" 
            color="Purple"
        >
            <x-slot:action>
                <button class="btn btn-primary btn-sm btn-outline p-0">
                    <span class="material-symbols-sharp">commit</span>
                </button>
            </x-slot:action>
        </x-table-row-checkbox-action>

        <x-table-row-checkbox-action 
            avatar="https://img.daisyui.com/images/profile/demo/3@94.webp" 
            name="Brice Swyre" 
            location="China" 
            job="Carroll Group" 
            tag="Tax Accountant" 
            color="Red"
        >
            <x-slot:action>
                <button class="btn btn-ghost btn-xs">details</button>
            </x-slot:action>
        </x-table-row-checkbox-action>

        <x-table-row-checkbox-action 
            avatar="https://img.daisyui.com/images/profile/demo/4@94.webp" 
            name="Marjy Ferencz" 
            location="Russia" 
            job="Rowe-Schoen" 
            tag="Office Assistant I" 
            color="Crimson"
        >
            <x-slot:action>
                <button class="btn btn-ghost btn-xs">details</button>
            </x-slot:action>
        </x-table-row-checkbox-action>

        <x-table-row-checkbox-action 
            avatar="https://img.daisyui.com/images/profile/demo/5@94.webp" 
            name="Yancy Tear" 
            location="Brazil" 
            job="Wyman-Ledner" 
            tag="Community Outreach Specialist" 
            color="Indigo"
        >
            <x-slot:action>
                <button class="btn btn-ghost btn-xs"><i class="fa fa-github"></i></button>
            </x-slot:action>
        </x-table-row-checkbox-action>
    </tbody>
    <tfoot>
        <tr>
            <th></th>
            <th>Name</th>
            <th>Job</th>
            <th>Favorite Color</th>
            <th></th>
        </tr>
    </tfoot>
</table>