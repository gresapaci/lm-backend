<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - Registration Invite</title>
</head>
<body style="margin:0; padding:0; background-color:#f0f5f9; font-family: Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0f5f9; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px; background-color:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 2px 6px rgba(37,83,185,0.1);">
                    <tr>
                        <td style="background-color:#0085db; padding:24px 32px;">
                            <span style="color:#ffffff; font-size:22px; font-weight:bold;">{{ config('app.name') }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <h1 style="margin:0 0 16px; font-size:20px; color:#111c2d;">You've been invited to join {{ config('app.name') }}!</h1>

                            <p style="margin:0 0 16px; font-size:15px; line-height:1.6; color:#333333;">
                                Hello,
                            </p>
                            <p style="margin:0 0 16px; font-size:15px; line-height:1.6; color:#333333;">
                                <strong>{{ $invitedBy }}</strong> has invited you to join {{ config('app.name') }} as a student.
                                Click the button below to create your account and set your password so you can log in
                                and start learning whenever you want.
                            </p>

                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:24px 0;">
                                <tr>
                                    <td align="center" style="border-radius:30px; background-color:#0085db;">
                                        <a href="{{ $registrationUrl }}"
                                           style="display:inline-block; padding:14px 32px; font-size:15px; font-weight:bold; color:#ffffff; text-decoration:none; border-radius:30px;">
                                            Accept Invite &amp; Create Account
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 8px; font-size:13px; line-height:1.6; color:#707a82;">
                                If the button above doesn't work, copy and paste this link into your browser:
                            </p>
                            <p style="margin:0 0 24px; font-size:13px; line-height:1.6; word-break:break-all;">
                                <a href="{{ $registrationUrl }}" style="color:#0085db;">{{ $registrationUrl }}</a>
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0f5f9; border-radius:12px; margin-bottom:24px;">
                                <tr>
                                    <td style="padding:16px 20px; font-size:13px; color:#333333;">
                                        <strong>Invite details</strong><br>
                                        Email: {{ $email }}<br>
                                        Sent: {{ $invitationDate }}<br>
                                        Expires: {{ $expirationDate }}
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0; font-size:13px; line-height:1.6; color:#707a82;">
                                This invite link will expire on {{ $expirationDate }}. If you weren't expecting this invite,
                                you can safely ignore this email.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 32px; background-color:#f0f5f9; text-align:center;">
                            <span style="font-size:12px; color:#707a82;">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
