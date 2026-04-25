<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Factura</title>
    @include('v1.management.pdf.styles.dte-style')
</head>
<body>
<div class="grid">
    <div class="grid-1">
        <div class="title-h1">
            Documento Tributario Electrónico
        </div>
        <div class="title-h1 mt-xs">
            Factura
        </div>
    </div>

    <div class="grid">
        <!--        Encabezado      -->
        <div class="grid-3 mt-xs">
            <div class="col-1 align-images">
                <img src="{{ public_path('assets/img/logos/color.png') }}" alt="Logo" style="width: 95%">
            </div>

            <div class="col-2 align-header-text">
                <p>
                    <span class="font-bold">Número de control:</span>
                    {{ $data['identificacion']['numeroControl'] }}
                </p>
                <p>
                    <span class="font-bold">Código de generación:</span>
                    {{ $data['identificacion']['codigoGeneracion'] }}
                </p>

                <p>
                    <span class="font-bold">
                        Sello de recepción:
                    </span>
                    {{ $receptionStamp }}
                </p>

                <p>
                    <span class="font-bold">Fecha y hora de emisión:</span>
                    {{ $data['identificacion']['fecEmi'] }} {{ $data['identificacion']['horEmi'] }}
                </p>

                <p>
                    <span class="font-bold">Modelo de facturación:</span>
                    Previo
                </p>

                <p>
                    <span class="font-bold">Tipo de transmisión:</span>
                    Normal
                </p>
            </div>

            <div class="col-1 align-images">
                <img src="data:image/png;base64, {{ $qrCode }}" alt="QR Goes Here">
            </div>
        </div>

        <!--        Fin Encabezado      -->
    </div>
</div>
</body>
</html>
