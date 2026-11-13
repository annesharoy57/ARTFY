@extends('auth.password-recovery-layout')

@section('title', 'Forgot password')
@section('heading', 'Forgot your password?')
@section('intro', 'Enter the email address on your Artist or Art Lover account. We’ll send you a six-digit recovery code.')

@section('recovery-form')
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <label for="email">Email address</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
        <button type="submit">Email me a recovery code</button>
    </form>
@endsection
