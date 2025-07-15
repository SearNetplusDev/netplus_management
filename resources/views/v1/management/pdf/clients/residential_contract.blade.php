<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Document</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @font-face {
            font-family: 'Garamond';
            font-style: normal;
            font-weight: normal;
            src: url("{{ public_path('fonts/Garamond/EBGaramond-Regular.ttf') }}") format("truetype");
        }

        @font-face {
            font-family: 'Bodoni';
            font-style: normal;
            font-weight: normal;
            src: url("{{ public_path('fonts/Bodoni/LibreBodoni-Regular.ttf') }}") format("truetype");
        }

        @font-face {
            font-family: 'Didot';
            font-style: normal;
            font-weight: normal;
            src: url("{{ public_path('fonts/Didot/GFSDidot-Regular.ttf') }}") format("truetype");
        }

        @font-face {
            font-family: 'Lora';
            font-style: normal;
            font-weight: normal;
            src: url("{{ public_path('fonts/Lora/Lora-Regular.ttf') }}") format("truetype");
        }

        @font-face {
            font-family: 'Montserrat';
            font-style: normal;
            font-weight: normal;
            src: url("{{ public_path('fonts/Montserrat/Montserrat-Regular.ttf') }}") format("truetype");
        }

        @font-face {
            font-family: 'Raleway';
            font-style: normal;
            font-weight: normal;
            src: url("{{ public_path('fonts/Raleway/Raleway-Regular.ttf') }}") format("truetype");
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Raleway', sans-serif;
            font-size: 12px;
            line-height: 1.5em;
            color: #333;
            background: #fff;
            margin: 1.5cm 1.9cm;
            text-align: justify;
            /*border: 1px solid cornflowerblue;*/
        }

        .header {
            width: 100%;
            margin-bottom: 12px;
        }

        .logo {
            width: 130px;
            vertical-align: middle;
            text-align: left;
        }

        .header-table {
            width: 100%;
            vertical-align: middle;
            font-family: 'Bodoni', sans-serif;
            font-size: 20px;
            padding-bottom: 7px;
            border-bottom: #0a0a0a 1px solid;
        }

        .content {
            font-family: 'Raleway', sans-serif;
            font-size: 12px;
            text-align: justify;
            font-weight: 15;
        }
    </style>
</head>
<body>
<div class="header">
    <table class="header-table">
        <tr>
            <th style="width: 25%; border-right: #0a0a0a 1px solid;">
                <img src="{{ public_path('assets/img/logos/logo_color.png') }}" alt="NETPLUS LOGO" class="logo">
            </th>
            <th style="width: 75%;">
                Contato de prestación de servicios de internet
            </th>
        </tr>
    </table>
</div>

<div class="content">
    <p>
        <b>Entre: </b><br/>
        <b>NETPLUS COMPANY WORK S.A de C.V</b>, con domicilio en {{ $data['client']['branch']['address'] }},
        {{ $data['client']['branch']['district']['name'] }},
        {{ $data['client']['branch']['municipality']['name'] }},
        {{ $data['client']['branch']['state']['name'] }},
        {{ $data['client']['branch']['country']['es_name'] }}, en adelante <b>"El Proveedor"</b>.
    </p>
    <p>
        <b>Y: </b><br/>
        <b>{{ $data['client']['name'].' '.$data['client']['surname'] }}</b>, con Documento Único de Identidad número
        <b>{{ $data['client']['dui']['number'] }}</b> y domicilio en
        {{ $data['client']['address']['neighborhood'] }},
        {{ $data['client']['address']['address'] }},
        {{ $data['client']['address']['district']['name'] }},
        {{ $data['client']['address']['municipality']['name'] }},
        {{ $data['client']['address']['state']['name'] }}, en adelante <b>"El Cliente"</b>.
    </p>
    <p>
        <b>Considerando:</b><br>
        Que ambas partes acuerdan celebrar el presente <b>CONTRATO DE PRESTACIÓN DE SERVICIOS DE INTERNET</b>, el cual
        se regirá por las siguientes:
    </p>
    <p>
        <b>EL PROVEEDOR</b> se obliga a proporcionar a <b>EL CLIENTE</b> un servicio de conexión a internet de banda
        ancha, soporte técnico, instalación, configuración inicial y los servicios adicionales que se especifican en las
        condiciones particulares.
    </p>
    <p>
        <b>El PROVEEDOR</b> se compromete a brindar el servicio con los más altos estándares de calidad, utilizando
        tecnología de última generación y personal técnico especializado.
    </p>
</div>
</body>
</html>
