@props(['role' => ''])

@php
    $rolePermissions = [
        'doctor' => [
            'patients' => ['view', 'create', 'edit'],
            'appointments' => ['view', 'create', 'edit'],
            'records' => ['view', 'create', 'edit'],
            'settings' => ['view'],
        ],
        'assistant' => [
            'patients' => ['view', 'create', 'edit'],
            'appointments' => ['view', 'create', 'edit'],
        ],
        'secretary' => [
            'patients' => ['view', 'create'],
            'appointments' => ['view', 'create', 'edit', 'view_all'],
        ],
        'receptionist' => [
            'patients' => ['view'],
            'appointments' => ['view', 'edit'],
        ],
    ];

    $moduleLabels = [
        'patients' => __('staff.module_patients'),
        'appointments' => __('staff.module_appointments'),
        'records' => __('staff.module_records'),
        'settings' => __('staff.module_settings'),
    ];

    $permLabels = [
        'view' => __('staff.perm_view'),
        'create' => __('staff.perm_create'),
        'edit' => __('staff.perm_edit'),
        'delete' => __('staff.perm_delete'),
        'view_all' => __('staff.perm_view_all'),
        'view_confidential' => __('staff.perm_view_confidential'),
    ];

    $allModules = ['patients', 'appointments', 'records', 'settings'];
    $allPerms = ['view', 'create', 'edit', 'delete', 'view_all'];

    $permissions = $rolePermissions[$role] ?? [];
@endphp

@if($role && isset($rolePermissions[$role]))
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
    <div class="flex items-center gap-2 mb-3">
        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
        </svg>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('staff.permissions_title') }}</h2>
    </div>
    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">{{ __('staff.permissions_note') }}</p>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700">
                    <th class="text-left py-2 pr-4 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                        {{ __('staff.module_patients') !== __('staff.module_patients') ? '' : '' }}
                    </th>
                    @foreach($allPerms as $perm)
                        <th class="text-center px-2 py-2 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                            {{ $permLabels[$perm] }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                @foreach($allModules as $module)
                    <tr>
                        <td class="py-2 pr-4 font-medium text-gray-700 dark:text-gray-300">
                            {{ $moduleLabels[$module] }}
                        </td>
                        @foreach($allPerms as $perm)
                            <td class="text-center px-2 py-2">
                                @if(isset($permissions[$module]) && in_array($perm, $permissions[$module]))
                                    <svg class="w-5 h-5 text-green-500 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-gray-300 dark:text-gray-600 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
