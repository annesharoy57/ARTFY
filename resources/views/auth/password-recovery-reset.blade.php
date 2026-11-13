@extends('auth.password-recovery-layout')

@section('title', 'Choose a new password')
@section('heading', 'Choose a new password')
@section('intro', 'Your recovery code is verified. Create a new password with at least eight characters.')

@section('recovery-form')
    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <label for="password">New password</label>
        <input id="password" name="password" type="password" minlength="8" required autocomplete="new-password" autofocus>
        <label for="password_confirmation">Confirm new password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" required autocomplete="new-password">
        <button type="submit">Save new password</button>
    </form>
@endsection
