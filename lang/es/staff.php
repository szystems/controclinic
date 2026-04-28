<?php

return [
    // Page
    'title' => 'Equipo',
    'subtitle' => 'Gestiona los miembros de tu equipo médico',
    'add_member' => 'Agregar Miembro',
    'add_doctor' => 'Agregar Doctor',
    'add_staff' => 'Agregar Personal',
    'edit_member' => 'Editar Miembro',
    'back_to_list' => 'Volver al equipo',

    // Table headers
    'name' => 'Nombre',
    'email' => 'Correo Electrónico',
    'phone' => 'Teléfono',
    'role' => 'Rol',
    'status' => 'Estado',
    'last_login' => 'Último Acceso',
    'actions' => 'Acciones',
    'joined' => 'Se unió',
    'never' => 'Nunca',

    // Roles
    'role_doctor' => 'Doctor',
    'role_assistant' => 'Asistente',
    'role_secretary' => 'Secretaria',
    'role_receptionist' => 'Recepcionista',
    'role_owner' => 'Propietario',

    // Status
    'active' => 'Activo',
    'inactive' => 'Inactivo',
    'all_statuses' => 'Todos los estados',
    'all_roles' => 'Todos los roles',

    // Form
    'personal_info' => 'Información Personal',
    'role_and_access' => 'Rol y Acceso',
    'professional_info' => 'Información Profesional',
    'select_role' => 'Seleccionar rol',
    'specialties' => 'Especialidades',
    'specialties_placeholder' => 'Ej: Cardiología, Pediatría',
    'license_number' => 'Número de Licencia',
    'license_placeholder' => 'Ej: COL-12345',
    'bio' => 'Biografía',
    'bio_placeholder' => 'Breve descripción profesional...',
    'password' => 'Contraseña',
    'password_confirmation' => 'Confirmar Contraseña',
    'password_help' => 'Mínimo 8 caracteres',
    'leave_blank' => 'Dejar en blanco para no cambiar',

    // Messages
    'created_successfully' => 'Miembro del equipo creado exitosamente',
    'updated_successfully' => 'Miembro del equipo actualizado exitosamente',
    'deleted_successfully' => 'Miembro del equipo eliminado exitosamente',
    'activated' => 'Miembro activado exitosamente',
    'deactivated' => 'Miembro desactivado exitosamente',
    'cannot_deactivate_owner' => 'No puedes desactivar al propietario',
    'cannot_delete_owner' => 'No puedes eliminar al propietario',
    'cannot_delete_self' => 'No puedes eliminarte a ti mismo',
    'email_already_exists' => 'Este correo ya está registrado en esta clínica',

    // Limits
    'doctors_usage' => 'Doctores: :current/:max',
    'staff_usage' => 'Personal: :current/:max',
    'doctors_unlimited' => 'Doctores: :current (ilimitados)',
    'staff_unlimited' => 'Personal: :current (ilimitados)',
    'doctor_limit_reached' => 'Has alcanzado el límite de doctores de tu plan',
    'staff_limit_reached' => 'Has alcanzado el límite de personal de tu plan',
    'no_staff_in_plan' => 'Tu plan no incluye personal adicional',
    'upgrade_for_doctors' => 'Mejora tu plan para agregar más doctores',
    'upgrade_for_staff' => 'Mejora tu plan para agregar más personal',

    // Empty state
    'no_members' => 'No hay miembros en tu equipo',
    'no_members_description' => 'Agrega doctores y personal para comenzar a gestionar tu clínica',
    'no_results' => 'No se encontraron resultados',
    'no_results_description' => 'Intenta con otros filtros de búsqueda',

    // Confirm
    'confirm_delete' => '¿Estás seguro de que deseas eliminar a :name?',
    'confirm_deactivate' => '¿Estás seguro de que deseas desactivar a :name?',
    'confirm_activate' => '¿Estás seguro de que deseas activar a :name?',

    // Search
    'search_placeholder' => 'Buscar por nombre o correo...',

    // Badge
    'you' => 'Tú',
    'owner_badge' => 'Propietario',

    // Permissions preview
    'permissions_title' => 'Permisos de este rol',
    'permissions_note' => 'Los permisos se asignan automáticamente según el rol seleccionado',
    'module_patients' => 'Pacientes',
    'module_appointments' => 'Citas',
    'module_records' => 'Historiales Médicos',
    'module_settings' => 'Configuración',
    'perm_view' => 'Ver',
    'perm_create' => 'Crear',
    'perm_edit' => 'Editar',
    'perm_delete' => 'Eliminar',
    'perm_view_all' => 'Ver todos',
    'perm_view_confidential' => 'Ver confidenciales',
    'perm_manage_users' => 'Gestionar equipo',
    'perm_manage_billing' => 'Gestionar facturación',
    'view_permissions' => 'Ver permisos',
];
