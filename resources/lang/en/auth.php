<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',

    // Registration
    'registration' => 'Registration Completed',
    'registrationError' => 'Error while registering user. Please try again',
    'emailVerificationCode' => 'Verification code sent to :email',
    'verificationCodeExpired' => 'Verification code has expired',

    // Login
    'invalidCredentials' => 'email or password is incorrect',
    'loggedIn' => 'Logged In Successfully',
    'notLoggedIn' => 'Please login to continue',
    'deactive' => 'Account deactivated. Please contact administrator',
    'notVerified' => 'You\'re account is not verified. New Verification code sent to provided email',
    'resetPasswordMail' => 'Verification code sent to provided email',
    'invalidVerificationCode' => 'Incorrect verification code',
    'codeVerified' => "Code verified",
    'emailAreadyVerified' => 'Email already verified'
];
