<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ResQLink Standard Message Catalog
    |--------------------------------------------------------------------------
    |
    | Centralized titles, details, and action labels for toasts, banners,
    | validation feedback, and confirmation dialogs across ResQLink.
    |
    */

    'status_updated' => [
        'title' => 'Status updated',
        'detail' => ':incident is now :status.',
        'action_label' => 'View incident',
    ],

    'status_update_failed' => [
        'title' => "Couldn't update status",
        'detail' => 'Something went wrong. Please try again.',
    ],

    'invalid_status_change' => [
        'title' => 'Action not allowed',
        'detail' => 'This incident is already completed.',
    ],

    'new_incident_notification' => [
        'title' => 'New :severity incident',
        'detail' => ':category in :barangay, :affected persons affected.',
        'action_label' => 'Open',
    ],

    'note_added' => [
        'title' => 'Note added',
        'detail' => 'Your update was saved to the activity history.',
    ],

    'note_validation' => [
        'title' => 'Validation error',
        'detail' => 'Note is required (max 300 characters).',
    ],

    'login_success' => [
        'title' => 'Welcome back, :name',
        'detail' => "You're signed in as :role.",
    ],

    'signed_out' => [
        'title' => 'You have been signed out',
        'detail' => 'See you next time.',
    ],

    'wrong_credentials' => [
        'title' => 'Incorrect username or password',
        'detail' => 'Check your details and try again.',
    ],

    'deactivated_account' => [
        'title' => 'Account deactivated',
        'detail' => 'Please contact your administrator.',
    ],

    'session_expired' => [
        'title' => 'Session expired',
        'detail' => 'Please sign in again to continue.',
    ],

    'forced_password_change' => [
        'title' => 'Change your temporary password',
        'detail' => 'For your security, set a new password before continuing.',
    ],

    'password_changed' => [
        'title' => 'Password updated',
        'detail' => 'Use your new password the next time you sign in.',
    ],

    'export_ready' => [
        'title' => 'Report ready',
        'detail' => 'Your PDF is downloading.',
    ],

    'connection_error' => [
        'title' => 'Connection problem',
        'detail' => "We couldn't reach the server. Check your connection.",
    ],

    'empty_filter' => [
        'title' => 'No incidents match your filters.',
        'detail' => 'Try adjusting your search criteria.',
        'action_label' => 'Reset filters',
    ],

    'confirmation_modal' => [
        'title' => 'Update status to :status?',
        'detail' => ':incident will change from :from to :status. This will be recorded in the activity history.',
        'cancel_label' => 'Cancel',
        'confirm_label' => 'Update',
    ],
];
