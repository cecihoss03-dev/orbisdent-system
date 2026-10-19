@if ($needsSetup)
    <div class="max-w-md mx-auto text-center py-12 space-y-4">
        <h2 class="text-lg font-semibold">Este paciente aún no tiene odontograma</h2>
        <p class="text-sm text-gray-500">Elige el tipo de dentición para crear el primero. Se sugirió automáticamente según la edad, pero puedes cambiarlo.</p>

        <div class="flex justify-center gap-3">
            <select wire:model="dentitionType" class="fi-select-input rounded-lg border-gray-300 text-sm">
                <option value="adult">Dentición permanente (adulto)</option>
                <option value="pediatric">Dentición temporal (pediátrico)</option>
            </select>

            <button
                type="button"
                wire:click="createInitialOdontogram"
                class="fi-btn fi-color-primary rounded-lg px-4 py-2 text-sm font-medium bg-primary-600 text-white"
            >
                Crear odontograma
            </button>
        </div>
    </div>
@else
    @php
        $regionPoints = [
            'V' => '0,0 30,0 20,10 10,10',
            'L' => '0,30 30,30 20,20 10,20',
            'P' => '0,30 30,30 20,20 10,20',
            'O' => '10,10 20,10 20,20 10,20',
            'I' => '10,10 20,10 20,20 10,20',
        ];
        $mesialPoints = '0,0 0,30 10,20 10,10';
        $distalPoints = '30,0 30,30 20,20 20,10';

        $upperArch = collect($teeth)->filter(fn ($t) => in_array(substr($t['fdi_code'], 0, 1), ['1', '2', '5', '6']));
        $lowerArch = collect($teeth)->filter(fn ($t) => in_array(substr($t['fdi_code'], 0, 1), ['3', '4', '7', '8']));
    @endphp

    <div class="fi-odontogram space-y-6">
        {{-- Barra superior: versión actual, selector de versiones, tipo de dentición --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold">
                    Odontograma
                    @unless ($this->isCurrentVersion)
                        <span class="fi-badge fi-color-warning inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium bg-warning-100 text-warning-700 ml-2">
                            Versión histórica — solo lectura
                        </span>
                    @endunless
                </h2>
                <p class="text-sm text-gray-500">
                    Versión del {{ $odontogram->recorded_at->format('d/m/Y H:i') }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <select wire:change="switchVersion($event.target.value)" class="fi-select-input rounded-lg border-gray-300 text-sm">
                    @foreach ($this->versions as $v)
                        <option value="{{ $v->id }}" @selected($v->id === $odontogram->id)>
                            {{ $v->recorded_at->format('d/m/Y H:i') }} {{ $v->is_current ? '(vigente)' : '' }}
                        </option>
                    @endforeach
                </select>

                @if ($this->isCurrentVersion)
                    <select wire:model="dentitionType" wire:change="updateDentitionType" class="fi-select-input rounded-lg border-gray-300 text-sm">
                        <option value="adult">Adulto</option>
                        <option value="pediatric">Pediátrico</option>
                    </select>
                @endif

                <button
                    type="button"
                    wire:click="startNewVersion"
                    wire:confirm="Esto congela la versión que estás viendo y abre una nueva editable a partir de su estado. ¿Continuar?"
                    class="fi-btn fi-color-gray rounded-lg px-3 py-2 text-sm font-medium border border-gray-300 dark:border-gray-600"
                >
                    Nueva versión
                </button>
            </div>
        </div>

        {{-- Carta dental --}}
        @foreach ([['label' => 'Arcada superior', 'teeth' => $upperArch], ['label' => 'Arcada inferior', 'teeth' => $lowerArch]] as $arch)
            <div>
                <p class="text-xs uppercase tracking-wide text-gray-400 mb-2">{{ $arch['label'] }}</p>
                <div class="flex flex-wrap gap-1 overflow-x-auto pb-2">
                    @foreach ($arch['teeth'] as $tooth)
                        <div class="flex flex-col items-center {{ $this->isCurrentVersion ? '' : 'opacity-70' }}">
                            <svg viewBox="0 0 30 30" width="42" height="42" class="border border-gray-300 dark:border-gray-600 rounded">
                                <polygon points="{{ $regionPoints['V'] }}" fill="{{ $tooth['region_colors']['V'] ?? '#ffffff' }}" stroke="#9CA3AF" stroke-width="0.5"
                                    wire:click.stop="selectRegion('{{ $tooth['fdi_code'] }}', 'V')" class="cursor-pointer hover:opacity-70" />

                                @php $bottomCode = in_array($tooth['fdi_code'][0], ['1', '2', '5', '6']) ? 'P' : 'L'; @endphp
                                <polygon points="{{ $regionPoints['L'] }}" fill="{{ $tooth['region_colors'][$bottomCode] ?? '#ffffff' }}" stroke="#9CA3AF" stroke-width="0.5"
                                    wire:click.stop="selectRegion('{{ $tooth['fdi_code'] }}', '{{ $bottomCode }}')" class="cursor-pointer hover:opacity-70" />

                                @php
                                    $posInQuadrant = (int) substr($tooth['fdi_code'], 1, 1);
                                    $isAnterior = in_array($posInQuadrant, [1, 2, 3]);
                                    $centerCode = $isAnterior ? 'I' : 'O';
                                @endphp
                                <polygon points="{{ $regionPoints['O'] }}" fill="{{ $tooth['region_colors'][$centerCode] ?? '#ffffff' }}" stroke="#9CA3AF" stroke-width="0.5"
                                    wire:click.stop="selectRegion('{{ $tooth['fdi_code'] }}', '{{ $centerCode }}')" class="cursor-pointer hover:opacity-70" />

                                <polygon points="{{ $tooth['mesial_side'] === 'left' ? $mesialPoints : $distalPoints }}" fill="{{ $tooth['region_colors']['M'] ?? '#ffffff' }}" stroke="#9CA3AF" stroke-width="0.5"
                                    wire:click.stop="selectRegion('{{ $tooth['fdi_code'] }}', 'M')" class="cursor-pointer hover:opacity-70" />

                                <polygon points="{{ $tooth['mesial_side'] === 'left' ? $distalPoints : $mesialPoints }}" fill="{{ $tooth['region_colors']['D'] ?? '#ffffff' }}" stroke="#9CA3AF" stroke-width="0.5"
                                    wire:click.stop="selectRegion('{{ $tooth['fdi_code'] }}', 'D')" class="cursor-pointer hover:opacity-70" />

                                @if ($tooth['whole_color'])
                                    <rect x="0" y="0" width="30" height="30" fill="{{ $tooth['whole_color'] }}" fill-opacity="0.55" stroke="#374151" stroke-width="1"
                                        wire:click.stop="selectRegion('{{ $tooth['fdi_code'] }}', null)" class="cursor-pointer" />
                                @endif
                            </svg>
                            <button type="button" wire:click="selectRegion('{{ $tooth['fdi_code'] }}', null)" class="text-[10px] text-gray-500 hover:text-primary-600 mt-0.5" title="{{ $tooth['name'] }}">
                                {{ $tooth['fdi_code'] }}
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        {{-- Adjuntos --}}
        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            <h3 class="text-sm font-semibold mb-2">Adjuntos de esta versión</h3>

            @if ($this->attachments->isNotEmpty())
                <ul class="text-sm space-y-1 mb-4">
                    @foreach ($this->attachments as $attachment)
                        <li class="flex items-center gap-2">
                            <span class="fi-badge inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-700">{{ $attachment->type }}</span>
                            <a href="{{ \Illuminate\Support\Facades\Storage::url($attachment->file_url) }}" target="_blank" class="text-primary-600 hover:underline">
                                Ver archivo
                            </a>
                            <span class="text-gray-400">— {{ $attachment->uploaded_at->format('d/m/Y H:i') }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-sm text-gray-400 mb-4">Sin adjuntos todavía.</p>
            @endif

            @if ($this->isCurrentVersion)
                <form wire:submit="saveAttachment" class="space-y-3 max-w-md">
                    {{ $this->attachmentForm }}
                    <x-filament::button type="submit" size="sm">Subir adjunto</x-filament::button>
                </form>
            @endif
        </div>

        {{-- Modal de condición --}}
        <x-filament::modal id="tooth-condition-modal" width="md">
            <x-slot name="heading">
                Diente {{ $selectedTooth }}
                @if ($selectedSurface)
                    — cara {{ $selectedSurface }}
                @else
                    — pieza completa
                @endif
            </x-slot>

            <form wire:submit="saveCondition" class="space-y-4">
                {{ $this->form }}
                <div class="flex justify-end gap-2">
                    <x-filament::button type="submit">Guardar</x-filament::button>
                </div>
            </form>
        </x-filament::modal>
    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('open-tooth-modal', () => {
                window.dispatchEvent(new CustomEvent('open-modal', { detail: { id: 'tooth-condition-modal' } }));
            });
            Livewire.on('close-tooth-modal', () => {
                window.dispatchEvent(new CustomEvent('close-modal', { detail: { id: 'tooth-condition-modal' } }));
            });
        });
    </script>
@endif
