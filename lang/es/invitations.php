<?php

return [
    // Email
    'email_subject' => 'Te han invitado a unirte a :clinic',
    'email_greeting' => '¡Hola :name!',
    'email_body' => ':inviter te ha invitado a unirte al equipo de :clinic en ControClinic.',
    'email_accept_button' => 'Aceptar Invitación',
    'email_expires' => 'Esta invitación expira el :date.',
    'email_ignore' => 'Si no esperabas esta invitación, puedes ignorar este correo.',

    // Staff form
    'invite_member' => 'Invitar Miembro',
    'invite_description' => 'Se enviará un correo de invitación al miembro del equipo',
    'invitation_sent' => 'Invitación enviada exitosamente',
    'invitation_resent' => 'Invitación reenviada exitosamente',
    'invitation_cancelled' => 'Invitación cancelada exitosamente',

    // Status
    'status_pending' => 'Pendiente',
    'status_accepted' => 'Aceptada',
    'status_expired' => 'Expirada',
    'status_cancelled' => 'Cancelada',
    'pending_invitation' => 'Invitación pendiente',
    'invited_by' => 'Invitado por :name',
    'expires' => 'Expira :date',

    // Actions
    'resend' => 'Reenviar',
    'cancel_invitation' => 'Cancelar invitación',
    'confirm_cancel' => '¿Estás seguro de que deseas cancelar la invitación de :name?',

    // Accept page
    'accept_title' => 'Aceptar Invitación',
    'accept_subtitle' => 'Configura tu contraseña para unirte a :clinic',
    'accept_welcome' => '¡Bienvenido/a! Has sido invitado/a como :role.',
    'accept_set_password' => 'Establece tu contraseña',
    'accept_button' => 'Crear cuenta y unirme',
    'accept_success' => '¡Bienvenido al equipo! Tu cuenta ha sido creada.',

    // Errors
    'invalid_token' => 'Esta invitación no es válida o ha expirado.',
    'already_accepted' => 'Esta invitación ya fue aceptada.',
    'already_cancelled' => 'Esta invitación fue cancelada.',
    'email_already_registered' => 'Este correo ya está registrado en esta clínica.',
    'duplicate_pending' => 'Ya existe una invitación pendiente para este correo.',

    // Pending invitations section
    'pending_invitations' => 'Invitaciones Pendientes',
    'no_pending' => 'No hay invitaciones pendientes',
    'sent_on' => 'Enviada el :date',
];
