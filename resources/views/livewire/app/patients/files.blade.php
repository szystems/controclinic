<div>
    {{-- Flash message --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
             class="mb-4 rounded-lg bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 px-4 py-3 text-sm text-green-800 dark:text-green-300 flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between pb-4 mb-5 border-b border-gray-100 dark:border-gray-700">
        <div class="flex items-center gap-3">
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('files.title') }}</h3>
            @if($totalFiles > 0)
                <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-0.5 rounded-full">{{ $totalFiles }}</span>
            @endif
        </div>
        <div class="flex items-center gap-2">
            {{-- Filtro por categoría --}}
            @if($totalFiles > 0)
                <select wire:model.live="filterCategory"
                        class="text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 py-1.5 pl-3 pr-8 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">{{ __('files.filter_all') }}</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}">{{ __('files.category_'.$cat) }}</option>
                    @endforeach
                </select>
                @if($filterCategory)
                    <button wire:click="$set('filterCategory', '')"
                            title="{{ __('files.filter_clear') }}"
                            class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                @endif
            @endif
            @can('create', \App\Models\PatientFile::class)
                <button wire:click="$set('showUploader', true)"
                        class="inline-flex items-center gap-1.5 text-sm font-medium bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    {{ __('files.upload_button') }}
                </button>
            @endcan
        </div>
    </div>

    {{-- Upload panel --}}
    @if($showUploader)
        <div class="mb-6 rounded-xl border border-indigo-200 dark:border-indigo-700/50 bg-indigo-50/50 dark:bg-indigo-900/10 p-5 space-y-4">
            <h4 class="text-sm font-semibold text-indigo-900 dark:text-indigo-200">{{ __('files.upload_title') }}</h4>

            {{-- Drop zone --}}
            <div x-data="{
                    dragging: false,
                    fileNames: [],
                    uploading: false,
                    progress: 0,
                    handleFiles(files) {
                        if (!files || files.length === 0) return;
                        this.fileNames = Array.from(files).map(f => f.name);
                        this.uploading = true;
                        this.progress = 0;
                        $wire.uploadMultiple(
                            'uploads',
                            Array.from(files),
                            () => { this.uploading = false; },
                            (error) => { this.uploading = false; console.error(error); },
                            (event) => { this.progress = event.detail.progress; }
                        );
                    }
                }"
                 @dragover.prevent="dragging = true"
                 @dragleave.prevent="dragging = false"
                 @drop.prevent="dragging = false; handleFiles($event.dataTransfer.files)"
                 :class="dragging ? 'border-indigo-500 bg-indigo-100 dark:bg-indigo-800/40' : 'border-gray-300 dark:border-gray-600 hover:border-indigo-400'"
                 class="rounded-lg border-2 border-dashed transition-colors p-8 text-center cursor-pointer"
                 @click="$refs.fileInput.click()">

                {{-- Input oculto — gestionado via Alpine, no wire:model directo --}}
                <input
                    type="file"
                    x-ref="fileInput"
                    multiple
                    class="hidden"
                    @change="handleFiles($event.target.files); $event.target.value = ''"
                >

                <template x-if="uploading">
                    <div class="space-y-2">
                        <svg class="mx-auto w-6 h-6 text-indigo-500 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <p class="text-sm text-indigo-600 dark:text-indigo-400">Subiendo… <span x-text="progress + '%'"></span></p>
                    </div>
                </template>

                <template x-if="!uploading && fileNames.length === 0">
                    <div>
                        <svg class="mx-auto w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('files.drop_or_click') }}</p>
                        <p class="mt-1 text-xs text-gray-400">{{ __('files.upload_hint') }}</p>
                    </div>
                </template>

                <template x-if="!uploading && fileNames.length > 0">
                    <div class="space-y-1">
                        <svg class="mx-auto w-7 h-7 text-green-500 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <template x-for="name in fileNames" :key="name">
                            <p class="text-xs text-gray-700 dark:text-gray-300 truncate" x-text="name"></p>
                        </template>
                        <p class="text-xs text-gray-400 mt-1">Haz clic para cambiar</p>
                    </div>
                </template>
            </div>

            {{-- Errors --}}
            @error('uploads.*')
                <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Nombre descriptivo --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('files.file_name') }}
                    </label>
                    <input type="text" wire:model="uploadName" maxlength="255"
                           placeholder="{{ __('files.file_name_hint') }}"
                           class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 placeholder-gray-400">
                </div>

                {{-- Categoría --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('files.category') }}
                    </label>
                    <select wire:model="uploadCategory"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}">{{ __('files.category_'.$cat) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Notas --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ __('files.notes') }}
                </label>
                <textarea wire:model="uploadNotes" rows="2" maxlength="1000"
                          placeholder="{{ __('files.notes_hint') }}"
                          class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100 px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 placeholder-gray-400 resize-none"></textarea>
            </div>

            <div class="flex gap-2 justify-end">
                <button wire:click="$set('showUploader', false)"
                        class="text-sm px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    {{ __('files.cancel') }}
                </button>
                <button wire:click="uploadFiles" wire:loading.attr="disabled"
                        class="text-sm font-medium px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white transition-colors disabled:opacity-50">
                    <span wire:loading.remove wire:target="uploadFiles">{{ __('files.save') }}</span>
                    <span wire:loading wire:target="uploadFiles">{{ __('general.saving') }}…</span>
                </button>
            </div>
        </div>
    @endif

    {{-- Delete confirm modal --}}
    @if($confirmDeleteId)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 dark:bg-black/70">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-sm w-full p-6 space-y-4">
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ __('files.delete_confirm') }}</p>
                <div class="flex gap-2 justify-end">
                    <button wire:click="cancelDelete"
                            class="text-sm px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        {{ __('general.cancel') }}
                    </button>
                    <button wire:click="deleteFile"
                            class="text-sm font-medium px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white">
                        {{ __('files.delete') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Files grid --}}
    @if($files->isEmpty())
        <div class="text-center py-12 text-gray-400 dark:text-gray-600">
            <svg class="mx-auto w-10 h-10 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            @if($filterCategory)
                <p class="text-sm">{{ __('files.empty_filtered') }}</p>
                <button wire:click="$set('filterCategory', '')" class="mt-2 text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                    {{ __('files.filter_clear') }}
                </button>
            @else
                <p class="text-sm">{{ __('files.empty') }}</p>
            @endif
        </div>
    @else
        <x-skeleton-card wire:loading :count="6" :lines="3" />
        <div wire:loading.remove class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($files as $file)
                <div class="group relative rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 hover:shadow-md transition-shadow">
                    {{-- Category badge --}}
                    <div class="flex items-start justify-between mb-3">
                        <span class="inline-flex items-center text-xs font-medium px-2 py-0.5 rounded-full
                            @switch($file->category)
                                @case('lab') bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 @break
                                @case('image') bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 @break
                                @case('report') bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 @break
                                @case('prescription') bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300 @break
                                @case('consent') bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-300 @break
                                @default bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400
                            @endswitch
                        ">{{ __('files.category_'.$file->category) }}</span>

                        @can('delete', $file)
                            <button wire:click="confirmDelete('{{ $file->id }}')"
                                    class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-red-500 transition-opacity ml-1 flex-shrink-0"
                                    title="{{ __('files.delete') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        @endcan
                    </div>

                    {{-- File type icon --}}
                    <div class="flex items-center gap-3 mb-3">
                        <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center
                            {{ $file->isImage() ? 'bg-purple-100 dark:bg-purple-900/30' : ($file->isPdf() ? 'bg-red-100 dark:bg-red-900/30' : 'bg-gray-100 dark:bg-gray-700') }}">
                            @if($file->isImage())
                                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            @elseif($file->isPdf())
                                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            @else
                                <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate" title="{{ $file->name }}">
                                {{ $file->name }}
                            </p>
                            <p class="text-xs text-gray-400">{{ $file->formattedSize() }}</p>
                        </div>
                    </div>

                    {{-- Notes --}}
                    @if($file->notes)
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3 line-clamp-2">{{ $file->notes }}</p>
                    @endif

                    {{-- Meta --}}
                    <div class="text-xs text-gray-400 dark:text-gray-500 space-y-0.5 mb-3">
                        <div>{{ $file->created_at->format('d/m/Y H:i') }}</div>
                        @if($file->uploadedBy)
                            <div>{{ $file->uploadedBy->name }}</div>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="flex gap-2">
                        @if($file->isImage() || $file->isPdf())
                            <a href="{{ route('app.patient-files.show', ['clinic' => $currentClinic->slug, 'file' => $file->id]) }}"
                               target="_blank"
                               class="flex-1 text-center text-xs font-medium px-2 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white transition-colors">
                                {{ __('files.view') }}
                            </a>
                        @endif
                        <a href="{{ route('app.patient-files.download', ['clinic' => $currentClinic->slug, 'file' => $file->id]) }}"
                           class="{{ ($file->isImage() || $file->isPdf()) ? '' : 'flex-1 text-center' }} text-xs font-medium px-2 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            {{ __('files.download') }}
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
