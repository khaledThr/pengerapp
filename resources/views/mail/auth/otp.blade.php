<x-mail::message>
<p>Hi {{ $user->name }},</p>

<p>We have received a request to verify your Penger account.</p>

<p>Please use the following One-Time Password (OTP) to complete your verification process:</p>

<h2 style="text-align: center; letter-spacing: 2px;">{{ $otp->code }}</h2>

<p>This OTP should not be shared with anyone.</p>

Thank you,<br>
{{ config('app.name') }}
</x-mail::message>
