@php
    $ticketLinker = config('app.url') . '/' . 'tickets/' . $ticketLink;
@endphp

<div style="background-color:#EAFFEAFF;color:#171717;margin:0;padding:0;font-family:Inter,Arial,sans-serif;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0;padding:0;">
    <tr>
      <td align="center" valign="top" style="padding:32px 0;">

        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="576" style="background:#fff;border-radius:8px;padding:32px;box-sizing:border-box;text-align:left;">
          <tr>
            <td style="text-align:center;">
              <h2 style="margin:0 0 24px 0;font-weight:600;font-size:32px;text-align:center;">Tiket Selesai</h2>
            </td>
          </tr>
          <tr>
            <td>
              <h4>Halo, <b>{{ $name }}.</b></h4>
              <p>Kami memberi notifikasi bahwa masalah pada tiket {{ $ticketName }} telah diselesaikan.</p>
              <p>Klik <a href="{{$ticketLinker}}" style="word-break: break-word; hyphens: none; font-weight: 700; color: #0b234a;">disini</a> untuk mengakses tiket tersebut.</p>
            </td>
          </tr>
        </table>

        <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin-top:24px;">
          <tr>
            <td align="center">
              <img src="{{ $message->embed(public_path('email-logo.png')) }}"
                   alt="Seventhsoft Logo"
                   style="display:block;width:80px;height:auto;margin:auto;">
            </td>
          </tr>
        </table>

      </td>
    </tr>
  </table>
</div>
