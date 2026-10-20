{{--
    Forgot-password verification code.

    Same table layout and inline styles as student-welcome.blade.php, for the same
    reason: Gmail strips <style> blocks.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your HMS password reset code</title>
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
                            <p style="margin:0 0 16px; font-size:17px; color:#9f1239; font-weight:bold;">Hi {{ $userName }},</p>

                            <p style="margin:0 0 22px; font-size:14px; line-height:22px; color:#4b5563;">
                                We received a request to reset the password on your HMS account.
                                Enter this code on the Forgot Password page to continue.
                            </p>

                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto 22px;">
                                <tr>
                                    <td style="background-color:#fff1f2; border:1px dashed #fb7185; border-radius:10px; padding:14px 28px;">
                                        <span style="font-size:30px; color:#be123c; font-family:'Courier New', Courier, monospace; font-weight:bold; letter-spacing:8px;">{{ $code }}</span>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 22px; font-size:13px; line-height:20px; color:#4b5563; text-align:center;">
                                The code expires in {{ $expiresInMinutes }} minutes.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#fffbeb; border:1px solid #fde68a; border-radius:8px;">
                                <tr>
                                    <td style="padding:14px 16px;">
                                        <p style="margin:0; font-size:13px; color:#92400e; line-height:19px;">
                                            <strong>Did not ask for this?</strong> Ignore this email. Your password stays
                                            the same, and nobody can change it without this code. Never share the code with anyone.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="background-color:#fff1f2; padding:18px 28px; border-top:1px solid #fecdd3;">
                            <p style="margin:0; font-size:12px; color:#9ca3af; line-height:18px;">
                                This message was sent automatically by HMS because a password reset was requested for this address.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
