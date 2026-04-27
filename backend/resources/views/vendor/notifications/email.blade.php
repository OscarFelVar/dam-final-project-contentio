<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>{{ $subject ?? 'Contentio' }}</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { background-color: #F5F1EA; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; }
    .wrapper { width: 100%; padding: 40px 16px; background-color: #F5F1EA; }
    .card { max-width: 560px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #E8E0D8; }

    /* Header */
    .header { background-color: #4A342A; padding: 28px 40px; text-align: center; }
    .logo-text { color: #ffffff; font-size: 22px; font-weight: 600; letter-spacing: 0.08em; }
    .logo-dot { color: #D7C9B8; }

    /* Body */
    .body { padding: 36px 40px; }
    .greeting { font-size: 18px; color: #4A342A; font-weight: 600; margin-bottom: 20px; }
    .line { font-size: 14px; color: #7D5A44; line-height: 1.7; margin-bottom: 14px; }
    .line strong { color: #4A342A; font-weight: 600; }
    .divider { height: 1px; background-color: #F0EAE2; margin: 24px 0; }

    /* Button */
    .btn-wrapper { text-align: center; margin: 28px 0; }
    .btn { display: inline-block; background-color: #4A342A; color: #ffffff !important; text-decoration: none; padding: 13px 32px; border-radius: 10px; font-size: 14px; font-weight: 500; letter-spacing: 0.02em; }
    .btn:hover { background-color: #7D5A44; }

    /* URL fallback */
    .url-fallback { font-size: 11px; color: #A08A7A; text-align: center; margin-top: -12px; margin-bottom: 20px; word-break: break-all; }
    .url-fallback a { color: #A08A7A; }

    /* Footer */
    .footer { background-color: #FDFCFB; border-top: 1px solid #F0EAE2; padding: 20px 40px; text-align: center; }
    .footer-text { font-size: 11px; color: #C0AFA3; line-height: 1.6; }
    .footer-text a { color: #A08A7A; text-decoration: none; }

    @media (max-width: 600px) {
      .body { padding: 28px 24px; }
      .header { padding: 22px 24px; }
      .footer { padding: 18px 24px; }
    }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="card">

      <!-- Header -->
      <div class="header">
        <div class="logo-text">Contentio<span class="logo-dot">.</span></div>
      </div>

      <!-- Body -->
      <div class="body">

        @if (!empty($greeting))
          <p class="greeting">{{ $greeting }}</p>
        @endif

        @foreach ($introLines as $line)
          <p class="line">{!! nl2br(e($line)) !!}</p>
        @endforeach

        @isset($actionText)
          <div class="divider"></div>
          <div class="btn-wrapper">
            <a href="{{ $actionUrl }}" class="btn">{{ $actionText }}</a>
          </div>
          <p class="url-fallback">
            Si el botón no funciona, copia este enlace en tu navegador:<br>
            <a href="{{ $actionUrl }}">{{ $displayableActionUrl }}</a>
          </p>
          <div class="divider"></div>
        @endisset

        @foreach ($outroLines as $line)
          <p class="line">{!! nl2br(e($line)) !!}</p>
        @endforeach

        @if (!empty($salutation))
          <p class="line" style="margin-top: 24px; color: #A08A7A; font-size: 13px;">
            {{ $salutation }}
          </p>
        @endif

      </div>

      <!-- Footer -->
      <div class="footer">
        <p class="footer-text">
          Este email fue enviado automáticamente por <strong>Contentio</strong>.<br>
          Por favor no respondas a este mensaje.<br>
          <a href="https://contentio.tech">contentio.tech</a>
        </p>
      </div>

    </div>
  </div>
</body>
</html>
