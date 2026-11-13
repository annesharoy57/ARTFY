@extends('auth.password-recovery-layout')

@section('title', 'Enter recovery code')
@section('heading', 'Check your email')
@section('intro', 'If this address belongs to an Artist or Art Lover account, a six-digit code has been sent. The code expires in 10 minutes.')

@section('recovery-form')
    <p class="intro">Email: <strong>{{ $email }}</strong></p>
    <form method="POST" action="{{ route('password.code.verify') }}">
        @csrf
        <label for="code">Six-digit recovery code</label>
        <input id="code" name="code" type="text" inputmode="numeric" pattern="[0-9]{6}" minlength="6" maxlength="6" autocomplete="one-time-code" required autofocus>
        <button type="submit">Verify code</button>
    </form>
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <input type="hidden" name="email" value="{{ $email }}">
        <button type="submit">Send a new code</button>
    </form>
@endsection
