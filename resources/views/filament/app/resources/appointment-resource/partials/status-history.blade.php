<div class="space-y-2">
    @forelse ($histories as $history)
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-2 text-sm">
            <div>
                <span class="text-gray-400">{{ $history->old_status ?? '—' }}</span>
                →
                <span class="font-medium">{{ $history->new_status }}</span>
            </div>
            <div class="text-gray-400 text-xs text-right">
                {{ $history->changed_at->format('d/m/Y H:i') }}
                @if ($history->changedBy)
                    <br>{{ $history->changedBy->name }}
                @endif
            </div>
        </div>
    @empty
        <p class="text-sm text-gray-400">Sin cambios de estado registrados todavía.</p>
    @endforelse
</div>
