{{--
    Student welcome email.

    Table-based layout with inline styles on purpose: Gmail strips <style> blocks and
    ignores most modern CSS, so anything that has to survive the trip is set per element.
    Gradients are layered over a solid background-color so clients that drop
    background-image (Outlook) still land on the brand rose.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your HMS account is ready</title>
</head>
<body style="margin:0; padding:0; background-color:#fff1f2; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#fff1f2; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px; background-color:#ffffff; border-radius:14px; overflow:hidden; border:1px solid #fecdd3;">

                    <tr>
                        <td bgcolor="#e11d48" style="background-color:#e11d48; background-image:linear-gradient(135deg, #f43f5e 0%, #db2777 100%); padding:26px 28px;">
                            <p style="margin:0; font-size:22px; font-weight:bold; color:#ffffff; letter-spacing:1px;">HMS</p>
                            <p style="margin:5px 0 0; font-size:13px; color:#ffe4e6;">Hotel Management Simulation</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="height:4px; line-height:4px; font-size:0; background-color:#fda4af;">&nbsp;</td>
                    </tr>

                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 16px; font-size:17px; color:#9f1239; font-weight:bold;">Hi {{ $studentName }},</p>

                            <p style="margin:0 0 22px; font-size:14px; line-height:22px; color:#4b5563;">
                                Welcome to HMS. Your student account has been created
                                @if($className) and you have been enrolled in <strong style="color:#be123c;">{{ $className }}</strong>@endif.
                                You can sign in with the details below.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#fff1f2; border:1px solid #fecdd3; border-radius:12px; margin-bottom:22px;">
                                <tr>
                                    <td style="padding:18px 20px;">
                                        @if($studentNumber)
                                        <p style="margin:0 0 6px; font-size:11px; color:#9f1239; text-transform:uppercase; letter-spacing:1.2px; font-weight:bold;">Student Number</p>
                                        <p style="margin:0 0 16px; font-size:15px; color:#1f2937; font-family:'Courier New', Courier, monospace;">{{ $studentNumber }}</p>
                                        @endif

                                        <p style="margin:0 0 6px; font-size:11px; color:#9f1239; text-transform:uppercase; letter-spacing:1.2px; font-weight:bold;">Login Email</p>
                                        <p style="margin:0 0 16px; font-size:15px; color:#1f2937; font-family:'Courier New', Courier, monospace;">{{ $loginEmail }}</p>

                                        <p style="margin:0 0 6px; font-size:11px; color:#9f1239; text-transform:uppercase; letter-spacing:1.2px; font-weight:bold;">Temporary Password</p>
                                        <table role="presentation" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="background-color:#ffffff; border:1px dashed #fb7185; border-radius:8px; padding:10px 16px;">
                                                    <span style="font-size:18px; color:#be123c; font-family:'Courier New', Courier, monospace; font-weight:bold; letter-spacing:1.5px;">{{ $plainPassword }}</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin-bottom:22px;">
                                <tr>
                                    <td bgcolor="#e11d48" style="background-color:#e11d48; background-image:linear-gradient(135deg, #f43f5e 0%, #db2777 100%); border-radius:9px;">
                                        <a href="{{ $loginUrl }}" style="display:inline-block; padding:13px 30px; font-size:14px; font-weight:bold; color:#ffffff; text-decoration:none;">Sign in to HMS</a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 20px; font-size:12px; color:#6b7280; line-height:18px;">
                                If the button does not work, copy this link into your browser:<br>
                                <a href="{{ $loginUrl }}" style="color:#e11d48; word-break:break-all;">{{ $loginUrl }}</a>
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
                        <td style="background-color:#fff1f2; padding:18px 28px; border-top:1px solid #fecdd3;">
                            <p style="margin:0; font-size:12px; color:#9ca3af; line-height:18px;">
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
