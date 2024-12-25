<div class="overflow-x-auto dmdd-table">
    <table {{ $attributes->merge(['class' => 'table']) }}>
        <!-- Table Head -->
        <thead>
            <tr>
                {{ $head }}
            </tr>
        </thead>

        <!-- Table Body -->
        <tbody>
            {{ $slot }}
        </tbody>

        <!-- Table Foot -->
        @isset($foot)
        <tfoot>
            <tr>
                {{ $foot }}
            </tr>
        </tfoot>
        @endisset
    </table>
</div>
