<tr>
    <!-- Checkbox -->
    <th>
        <label>
            <input type="checkbox" class="checkbox" />
        </label>
    </th>
    <!-- Content -->
    <td>
        <div class="flex items-center gap-3">
            <div class="avatar">
                <div class="mask mask-squircle h-12 w-12">
                    @if(!empty($avatar))
                        <img src="{{ $avatar }}" alt="{{ $name ?? 'Avatar' }}" />
                    @else
                        <img src="/default-avatar.png" alt="Default Avatar" />
                    @endif
                </div>
            </div>
            <div>
                <div class="font-bold">{{ $name ?? 'Unknown Name' }}</div>
                <div class="text-sm opacity-50">{{ $location ?? 'Unknown Location' }}</div>
            </div>
        </div>
    </td>
    <td>
        <br />
        @if(!empty($tags) && is_iterable($tags))
            @foreach($tags as $tag)
                <span class="badge badge-sm">{{ $tag }}</span>
            @endforeach
        @elseif(!empty($tag))
            <span class="badge badge-sm table-badge">{{ $tag }}</span>
        @endif
    </td>
    <td>{{ $color ?? 'N/A' }}</td>
    <th>
        @isset($action)
            {{ $action }}
        @else
            <span>No Actions Available</span>
        @endisset
    </th>
</tr>
