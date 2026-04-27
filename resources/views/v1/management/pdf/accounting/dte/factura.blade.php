<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Factura</title>
    @include('v1.management.pdf.styles.dte-style')
</head>
<body>

<p class="title-h1">Documento Tributario Electrónico</p>
<p class="title-h1 mt-xs">Factura</p>

<!--        Encabezado: Logo | Datos | QR       -->
<table class="table-full mt-xs">
    <tbody>
    <tr>
        <td class="header-logo">
            <img src="{{ public_path('assets/img/logos/color.png') }}" alt="LOGO GOES HERE" style="width: 95%">
        </td>

        <td class="header-data">
            <p>
                <span class="font-bold">Número de control: </span>{{ $data['identificacion']['numeroControl'] }}
            </p>
            <p>
                <span class="font-bold">Código de generación: </span>{{ $data['identificacion']['codigoGeneracion'] }}
            </p>
            <p>
                <span class="font-bold">Sello de recepción: </span>{{ $receptionStamp }}
            </p>
            <p>
                <span class="font-bold">Fecha y hora de emisión: </span>
                {{ $data['identificacion']['fecEmi'] }} {{ $data['identificacion']['horEmi'] }}
            </p>
            <p><span class="font-bold">Modelo de facturación: </span>Previo</p>
            <p><span class="font-bold">Tipo de transmisión: </span>Normal</p>
        </td>

        <td class="header-qr">
            <img src="data:iamge/png;base64, {{ $qrCode }}" alt="QR GOES HERE">
        </td>
    </tr>
    </tbody>
</table>
<!--        Fin Encabezado      -->

<!--    Emisor/Receptor     -->
<table class="table-full mt-xs section-container">
    <tbody>
    <tr>
        <td class="box-cell">
            <div class="box-content">
                <p class="font-bold center-text">Emisor</p>
                <div class="text-small">
                    <p><span class="font-bold">Nombre o razón social: </span>{{ $data['emisor']['nombre'] }}</p>
                    <p><span class="font-bold">NIT: </span> {{ $data['emisor']['nit'] }}</p>
                    <p><span class="font-bold">NRC: </span>{{ $data['emisor']['nrc'] }}</p>
                    <p><span class="font-bold">Actividad económica: </span>Servicio de internet N.C.P.</p>
                    <p>
                        <span class="font-bold">Dirección: </span>
                        Calle principal, Colonia San Francisco, #34, San Miguel, San Miguel Centro, San Miguel
                    </p>
                    <p><span class="font-bold">Número de teléfono: </span>{{ $data['emisor']['telefono'] }}</p>
                    <p><span class="font-bold">Correo electrónico: </span>{{ $data['emisor']['correo'] }}</p>
                </div>
            </div>
        </td>

        <td style="width: 2%;"></td>

        <td class="box-cell">
            <div class="box-content">
                <p class="font-bold center-text">Receptor</p>
                <div class="text-small">
                    <p><span class="font-bold">Nombre o razón social: </span>{{ $clientData['name'] }}</p>
                    <p><span class="font-bold">DUI: </span>{{ $clientData['dui'] }}</p>
                    <p><span class="font-bold">Dirección: </span>{{ $clientData['address'] }}</p>
                    <p><span class="font-bold">Número de teléfono: </span>{{ $clientData['phone'] }}</p>
                    <p><span class="font-bold">Correo electrónico: </span>{{ $clientData['email'] }}</p>
                </div>
            </div>
        </td>
    </tr>
    </tbody>
</table>
<!--    Fin Emisor/Receptor     -->
</body>
</html>
