<?php

return [
    // Email
    'email_subject' => 'You have been invited to join :clinic',
    'email_greeting' => 'Hello :name!',
    'email_body' => ':inviter has invited you to join the :clinic team on ControClinic.',
    'email_accept_button' => 'Accept Invitation',
    'email_expires' => 'This invitation expires on :date.',
    'email_ignore' => 'If you were not expecting this invitation, you can ignore this email.',

    // Staff form
    'invite_member' => 'Invite Member',
    'invite_description' => 'An invitation email will be sent to the team member',
    'invitation_sent' => 'Invitation sent successfully',
    'invitation_resent' => 'Invitation resent successfully',
    'invitation_cancelled' => 'Invitation cancelled successfully',

    // Status
    'status_pending' => 'Pending',
    'status_accepted' => 'Accepted',
    'status_expired' => 'Expired',
    'status_cancelled' => 'Cancelled',
    'pending_invitation' => 'Pending invitation',
    'invited_by' => 'Invited by :name',
    'expires' => 'Expires :date',

    // Actions
    'edit' => 'Edit',
    'edit_title' => 'Edit invitation',
    'edit_resend_hint' => 'Saving will send an updated invitation email to the recipient.',
    'save_and_resend' => 'Save & Resend',
    'invitation_updated' => 'Invitation updated and resent successfully.',
    'resend' => 'Resend',
    'cancel_invitation' => 'Cancel invitation',
    'confirm_cancel' => 'Are you sure you want to cancel the invitation for :name?',

    // Accept page
    'accept_title' => 'Accept Invitation',
    'accept_subtitle' => 'Set your password to join :clinic',
    'accept_welcome' => 'Welcome! You have been invited as :role.',
    'accept_set_password' => 'Set your password',
    'accept_button' => 'Create account and join',
    'accept_success' => 'Welcome to the team! Your account has been created.',

    // Errors
    'invalid_token' => 'This invitation is not valid or has expired.',
    'already_accepted' => 'This invitation has already been accepted.',
    'already_cancelled' => 'This invitation has been cancelled.',
    'email_already_registered' => 'This email is already registered in this clinic.',
    'duplicate_pending' => 'There is already a pending invitation for this email.',

    // Pending invitations section
    'pending_invitations' => 'Pending Invitations',
    'no_pending' => 'No pending invitations',
    'sent_on' => 'Sent on :date',
];
