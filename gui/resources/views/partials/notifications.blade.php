@if (session('status'))
    <div class="flash-message flash-success">
        {{ session('status') }}
    </div>
@endif