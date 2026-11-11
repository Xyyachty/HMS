{{--
    Account set to active or inactive by faculty.

    Same table layout and inline styles as student-welcome.blade.php, for the same
    reason: Gmail strips <style> blocks.
--}}
@php
    $isActive = $status === 'active';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your HMS account status</title>
</head>
<body style="margin:0; padding:0; background-color:#fff1f2; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#fff1f2; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px; background-color:#ffffff; border-radius:14px; overflow:hidden; border:1px solid #fecdd3;">

                    <tr>
                        <td bgcolor="#e11d48" style="background-color:#e11d48; background-image:linear-gradient(135deg, #f43f5e 0%, #db2777 100%); padding:22px 28px;">
                            @include('emails.partials.brand-header')
                        </td>
                    </tr>

                    <tr>
                        <td style="height:4px; line-height:4px; font-size:0; background-color:#fda4af;">&nbsp;</td>
                    </tr>

                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 16px; font-size:17px; color:#9f1239; font-weight:bold;">Hi {{ $userName }},</p>

                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto 22px;">
                                <tr>
                                    <td style="background-color:{{ $isActive ? '#f0fdf4' : '#fef2f2' }}; border:1px solid {{ $isActive ? '#86efac' : '#fecaca' }}; border-radius:10px; padding:12px 28px;">
                                        <span style="font-size:18px; font-weight:bold; color:{{ $isActive ? '#15803d' : '#b91c1c' }};">
                                            Account {{ $isActive ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                            </table>

                            @if ($isActive)
                                <p style="margin:0 0 22px; font-size:14px; line-height:22px; color:#4b5563;">
                                    {{ $changedBy }} set your HMS account to <strong>active</strong>.
                                    You can sign in again.
                                </p>

                                <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto 8px;">
                                    <tr>
                                        <td bgcolor="#e11d48" style="background-color:#e11d48; border-radius:8px;">
                                            <a href="{{ $loginUrl }}" style="display:inline-block; padding:12px 26px; font-size:14px; font-weight:bold; color:#ffffff; text-decoration:none;">Sign in to HMS</a>
                                        </td>
                                    </tr>
                                </table>
                            @else
                                <p style="margin:0 0 12px; font-size:14px; line-height:22px; color:#4b5563;">
                                    {{ $changedBy }} set your HMS account to <strong>inactive</strong>.
                                    You cannot sign in until your account is set back to active.
                                </p>
                                <p style="margin:0; font-size:14px; line-height:22px; color:#4b5563;">
                                    Questions? Ask your faculty.
                                </p>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td style="background-color:#fff1f2; padding:18px 28px; border-top:1px solid #fecdd3;">
                            <p style="margin:0; font-size:12px; color:#9ca3af; line-height:18px;">
                                This message was sent automatically by HMS because your account status changed.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
