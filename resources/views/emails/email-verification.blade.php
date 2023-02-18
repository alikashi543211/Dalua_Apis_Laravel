@extends('admin.layouts.email')
@section('subject', 'Email')
@section('content')
	<div>
	    <h1 align="center" style="color: #2b3248;font-size:24px;font-weight:bold;margin-top: 30px;text-transform:none;font-family: sans-serif;line-height: 1.4;margin-bottom: 30px;">Email Verification</h1>
	</div>
	<p style="font-family: sans-serif;text-align:justify;color:grey;font-weight:bold;margin-bottom: 30px;">Hi, <br>You're almost ready to get started. Please copy verification code to verify your email address and enjoy our services.</p>
	<p style="font-family: sans-serif;text-align:justify;color:grey;font-size:36px;font-weight:bold;margin-bottom: 30px;text-align:center;">{{ $verificationCode }}</p>
    <p style="font-family: sans-serif;text-align:justify;color:grey;font-weight:bold;margin-bottom: 30px;">Thanks.</p>

@endsection
