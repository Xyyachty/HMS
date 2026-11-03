{{-- Logo + name for the top band of every HMS email. The logo loads from the
     live site (app.url, like the login link), so it only shows once the mail client allows images. --}}
<table role="presentation" cellpadding="0" cellspacing="0">
    <tr>
        <td valign="middle" style="padding-right:14px;">
            <table role="presentation" cellpadding="0" cellspacing="0">
                <tr>
                    <td bgcolor="#ffffff" style="background-color:#ffffff; border-radius:50%; padding:4px; line-height:0;">
                        <img src="{{ rtrim((string) config('app.url'), '/') }}/new_logo_in_chtm....png" width="48" height="48" alt="HM" style="display:block; width:48px; height:48px; border:0;">
                    </td>
                </tr>
            </table>
        </td>
        <td valign="middle">
            <p style="margin:0; font-size:22px; font-weight:bold; color:#ffffff; letter-spacing:1px;">HMS</p>
            <p style="margin:3px 0 0; font-size:13px; color:#ffe4e6;">SPC Hotel Customization and Simulation</p>
        </td>
    </tr>
</table>
