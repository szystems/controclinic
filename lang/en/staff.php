<?php

return [
    // Page
    'title' => 'Team',
    'subtitle' => 'Manage your medical team members',
    'add_member' => 'Add Member',
    'add_doctor' => 'Add Doctor',
    'add_staff' => 'Add Staff',
    'edit_member' => 'Edit Member',
    'back_to_list' => 'Back to team',

    // Table headers
    'name' => 'Name',
    'email' => 'Email',
    'phone' => 'Phone',
    'role' => 'Role',
    'status' => 'Status',
    'last_login' => 'Last Login',
    'actions' => 'Actions',
    'joined' => 'Joined',
    'never' => 'Never',

    // Roles
    'role_doctor' => 'Doctor',
    'role_assistant' => 'Assistant',
    'role_secretary' => 'Secretary',
    'role_receptionist' => 'Receptionist',
    'role_owner' => 'Owner',

    // Status
    'active' => 'Active',
    'inactive' => 'Inactive',
    'all_statuses' => 'All statuses',
    'all_roles' => 'All roles',

    // Form
    'personal_info' => 'Personal Information',
    'role_and_access' => 'Role & Access',
    'professional_info' => 'Professional Information',
    'select_role' => 'Select role',
    'specialties' => 'Specialties',
    'specialties_placeholder' => 'E.g: Cardiology, Pediatrics',
    'license_number' => 'Registration / License Number',
    'license_placeholder' => 'E.g: MD-12345',
    'bio' => 'Bio',
    'bio_placeholder' => 'Brief professional description...',
    'password' => 'Password',
    'password_confirmation' => 'Confirm Password',
    'password_help' => 'Minimum 8 characters',
    'leave_blank' => 'Leave blank to keep current',

    // Messages
    'created_successfully' => 'Team member created successfully',
    'updated_successfully' => 'Team member updated successfully',
    'deleted_successfully' => 'Team member deleted successfully',
    'activated' => 'Member activated successfully',
    'deactivated' => 'Member deactivated successfully',
    'cannot_deactivate_owner' => 'You cannot deactivate the owner',
    'cannot_delete_owner' => 'You cannot delete the owner',
    'cannot_delete_self' => 'You cannot delete yourself',
    'email_already_exists' => 'This email is already registered in this clinic',

    // Limits
    'doctors_usage' => 'Doctors: :current/:max',
    'staff_usage' => 'Staff: :current/:max',
    'doctors_unlimited' => 'Doctors: :current (unlimited)',
    'staff_unlimited' => 'Staff: :current (unlimited)',
    'doctor_limit_reached' => 'You have reached the doctor limit for your plan',
    'staff_limit_reached' => 'You have reached the staff limit for your plan',
    'no_staff_in_plan' => 'Your plan does not include additional staff',
    'upgrade_for_doctors' => 'Upgrade your plan to add more doctors',
    'upgrade_for_staff' => 'Upgrade your plan to add more staff',

    // Empty state
    'no_members' => 'No team members yet',
    'no_members_description' => 'Add doctors and staff to start managing your clinic',
    'no_results' => 'No results found',
    'no_results_description' => 'Try different search filters',

    // Confirm
    'confirm_delete' => 'Are you sure you want to delete :name?',
    'confirm_deactivate' => 'Are you sure you want to deactivate :name?',
    'confirm_activate' => 'Are you sure you want to activate :name?',

    // Search
    'search_placeholder' => 'Search by name or email...',

    // Badge
    'you' => 'You',
    'owner_badge' => 'Owner',
    'owner_role_locked' => 'The owner role cannot be changed.',

    // Permissions preview
    'permissions_title' => 'Permissions for this role',
    'permissions_note' => 'Permissions are automatically assigned based on the selected role',
    'module_patients' => 'Patients',
    'module_appointments' => 'Appointments',
    'module_records' => 'Medical Records',
    'module_settings' => 'Settings',
    'module_users' => 'Team',
    'module_billing' => 'Billing',
    'module_reports' => 'Reports',
    'custom_permissions_title' => 'Custom permissions',
    'custom_permissions_note' => 'Toggle additional permissions beyond the role. Items marked "role" are inherited and cannot be unchecked.',
    'role_default' => 'role',
    'perm_view' => 'View',
    'perm_create' => 'Create',
    'perm_edit' => 'Edit',
    'perm_delete' => 'Delete',
    'perm_view_all' => 'View all',
    'perm_view_confidential' => 'View confidential',
    'perm_manage_users' => 'Manage team',
    'perm_manage_billing' => 'Manage billing',
    'view_permissions' => 'View permissions',

    // Fase 7: Ownership transfer & password reset
    'force_password_reset' => 'Send reset link',
    'force_reset_title' => 'Force password change',
    'force_reset_description' => 'An email will be sent to this member with a secure one-time link. When clicked, they can set a new password. You will not see or change the password directly.',
    'reset_link_sent' => 'Reset link sent',
    'reset_link_failed' => 'Could not send the reset link. Please verify the member\'s email address is valid.',
    'cannot_reset_self' => 'You cannot force reset your own password.',
    'transfer_ownership' => 'Transfer Ownership',
    'ownership_transferred' => 'Ownership transferred successfully.',
    'transfer_failed' => 'Could not transfer ownership. Please try again.',
    'transfer_confirm' => 'Are you sure you want to transfer ownership to this user? This action is irreversible.',
    'transfer_confirm_yes' => 'Yes, transfer now',
    'only_owner_can_transfer' => 'Only the current owner can transfer ownership.',
    'cannot_transfer_to_self' => 'You cannot transfer ownership to yourself.',
    'transfer_ownership_section' => 'Transfer Clinic Ownership',
    'transfer_ownership_description' => 'Select an active team member to transfer clinic ownership to. This action is permanent and irreversible.',
    'select_new_owner' => 'Select new owner',
    'select_member' => '— Select member —',
    'transfer_only_to_doctor' => 'You can only transfer ownership to a user with the Doctor role.',
    'no_transfer_candidates' => 'There are no active doctors to transfer ownership to. Invite a doctor first.',
    'transfer_select_first' => 'You must select a member before transferring.',
    'restore_role_permissions' => 'Restore role permissions',
    'restore_permissions_confirm' => 'Are you sure? All extra permissions will be removed, leaving only the role defaults.',
    'permissions_restored' => 'Permissions restored to role defaults.',
];
