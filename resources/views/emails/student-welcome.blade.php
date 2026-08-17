{{--
    Student welcome email.

    Table-based layout with inline styles on purpose: Gmail strips <style> blocks and
    ignores most modern CSS, so anything that has to survive the trip is set per element.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your HMS account is ready</title>
</head>
<body style="margin:0; padding:0; background-color:#f1f5f9; font-family:Arial, Helvetica, sans-serif; color:#0f172a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px; background-color:#ffffff; border-radius:12px; overflow:hidden; border:1px solid #e2e8f0;">

                    <tr>
                        <td style="background-color:#047857; padding:24px 28px;">
                            <p style="margin:0; font-size:20px; font-weight:bold; color:#ffffff;">HMS</p>
                            <p style="margin:4px 0 0; font-size:13px; color:#d1fae5;">Hotel Management Simulation</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 16px; font-size:16px;">Hi {{ $studentName }},</p>

                            <p style="margin:0 0 20px; font-size:14px; line-height:22px; color:#334155;">
                                Welcome to HMS. Your student account has been created
                                @if($className) and you have been enrolled in <strong>{{ $className }}</strong>@endif.
                                You can sign in with the details below.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; margin-bottom:20px;">
                                <tr>
                                    <td style="padding:18px 20px;">
                                        @if($studentNumber)
                                        <p style="margin:0 0 12px; font-size:12px; color:#64748b; text-transform:uppercase; letter-spacing:1px; font-weight:bold;">Student Number</p>
                                        <p style="margin:0 0 16px; font-size:15px; color:#0f172a; font-family:'Courier New', Courier, monospace;">{{ $studentNumber }}</p>
                                        @endif

                                        <p style="margin:0 0 6px; font-size:12px; color:#64748b; text-transform:uppercase; letter-spacing:1px; font-weight:bold;">Login Email</p>
                                        <p style="margin:0 0 16px; font-size:15px; color:#0f172a; font-family:'Courier New', Courier, monospace;">{{ $loginEmail }}</p>

                                        <p style="margin:0 0 6px; font-size:12px; color:#64748b; text-transform:uppercase; letter-spacing:1px; font-weight:bold;">Temporary Password</p>
                                        <p style="margin:0; font-size:18px; color:#047857; font-family:'Courier New', Courier, monospace; font-weight:bold; letter-spacing:1px;">{{ $plainPassword }}</p>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin-bottom:22px;">
                                <tr>
                                    <td style="background-color:#047857; border-radius:8px;">
                                        <a href="{{ $loginUrl }}" style="display:inline-block; padding:12px 28px; font-size:14px; font-weight:bold; color:#ffffff; text-decoration:none;">Sign in to HMS</a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 20px; font-size:12px; color:#64748b; line-height:18px;">
                                If the button does not work, copy this link into your browser:<br>
                                <a href="{{ $loginUrl }}" style="color:#047857; word-break:break-all;">{{ $loginUrl }}</a>
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#fffbeb; border:1px solid #fde68a; border-radius:8px;">
                                <tr>
                                    <td style="padding:14px 16px;">
                                        <p style="margin:0; font-size:13px; color:#92400e; line-height:19px;">
                                            <strong>Please change your password</strong> after you sign in for the first time,
                                            and do not share these details with anyone.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="background-color:#f8fafc; padding:18px 28px; border-top:1px solid #e2e8f0;">
                            <p style="margin:0; font-size:12px; color:#94a3b8; line-height:18px;">
                                This message was sent automatically by HMS because an account was created for you.
                                If you were not expecting it, please contact your instructor.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
