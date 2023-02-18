@extends('admin.layouts.email')
@section('subject', 'Email')
@section('content')
	<div>
	    <h1 align="center" style="color: #2b3248;font-size:24px;font-weight:bold;margin-top: 30px;text-transform:none;font-family: sans-serif;line-height: 1.4;margin-bottom: 30px;">Reset Password</h1>
	</div>
	<p style="font-family: sans-serif;text-align:justify;color:grey;font-weight:bold;margin-bottom: 30px;">Need to reset your password?<br> Use your secret code!</p>
	<p style="font-family: sans-serif;text-align:justify;color:grey;font-size:36px;font-weight:bold;margin-bottom: 30px;text-align:center;">{{ $verification_code }}</p>
    <p style="font-family: sans-serif;text-align:justify;color:grey;font-weight:bold;margin-bottom: 30px;">If you did not forget your password, you can ignore this email.</p>
    <p style="font-family: sans-serif;text-align:justify;color:grey;font-weight:bold;margin-bottom: 30px;">Thanks.</p>

@endsection
