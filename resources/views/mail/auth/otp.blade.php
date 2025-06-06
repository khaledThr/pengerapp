<x-mail::message>
Hi {{ $user->name }},

@if($otp->type === 'login')
We have received a request to log in to your Penger account.
@elseif($otp->type === 'passwordRest')
We have received a request to reset your Penger account password.
@else
We have received a request to register in Penger.
@endif

Please use the following One-Time Password (OTP) to complete your verification process:

# {{ $otp->code }}

This OTP should not be shared with anyone.

Thank you,  
{{ config('app.name') }}
</x-mail::message>
