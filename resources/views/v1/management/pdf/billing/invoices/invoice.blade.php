<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            margin: 100px 50px 80px 50px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.0;
            margin: 1.0cm 1.5cm;
        }

        .container {
            width: 100%;
            padding: 20px;
        }

        .header {
            margin-bottom: 30px;
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 15px;
        }

        .header-content {
            display: table;
            width: 100%;
        }

        .company-info {
            display: table-cell;
            width: 60%;
            vertical-align: top;
        }

        .invoice-info {
            display: table-cell;
            width: 40%;
            vertical-align: top;
            text-align: right;
        }

        .company-name {
            font-size: 15pt;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .company-details {
            font-size: 9pt;
            color: #666;
            line-height: 1.1;
        }

        .invoice-title {
            font-size: 15pt;
            font-weight: bold;
            color: #e74c3c;
            margin-bottom: 10px;
        }

        .invoice-details {
            font-size: 9pt;
        }

        .invoice-details strong {
            color: #2c3e50;
        }

        .billing-section {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .billing-to, .billing-details {
            display: table-cell;
            width: 50%;
            padding: 15px;
            background-color: #f8f9fa;
            vertical-align: top;
        }

        .billing-to {
            margin-right: 10px;
        }

        .section-title {
            font-size: 10pt;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
            text-transform: uppercase;
            border-bottom: 2px solid #e74c3c;
            padding-bottom: 5px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .items-table thead {
            background-color: #2c3e50;
            color: white;
        }

        .items-table th {
            padding: 7px;
            text-align: left;
            font-weight: bold;
        }

        .items-table td {
            padding: 7px;
            border-bottom: 1px solid #ddd;
        }

        .items-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .totals-section {
            width: 40%;
            margin-left: auto;
            margin-top: 20px;
        }

        .total-row {
            display: table;
            width: 100%;
            padding: 8px 0;
            border-bottom: 1px solid #ddd;
        }

        .total-label {
            display: table-cell;
            text-align: left;
            font-weight: bold;
        }

        .total-value {
            display: table-cell;
            text-align: right;
        }

        .grand-total {
            background-color: #2c3e50;
            color: white;
            padding: 12px;
            margin-top: 5px;
            font-size: 12pt;
        }

        .payment-info {
            margin-top: 30px;
            padding: 15px;
            background-color: #ecf0f1;
            border-left: 4px solid #e74c3c;
        }

        .payment-info-title {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 10px;
            color: #2c3e50;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            color: #7f8c8d;
            border-top: 1px solid #ddd;
            padding: 10px 20px;
            background-color: white;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 9pt;
            margin-top: 5px;
        }

        .status-paid {
            background-color: #27ae60;
            color: white;
        }

        .status-pending {
            background-color: #f39c12;
            color: white;
        }

        .status-overdue {
            background-color: #e74c3c;
            color: white;
        }

        .status-canceled {
            color: white;
            background-color: #4a4e69;
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="company-info">
                <div class="company-name">NETPLUS S.A. DE C.V.</div>
                <div class="company-details">
                    {{ $data['branch_name'] }}<br>
                    {{ $data['branch_address']  }}, {{ $data['branch_state'] }}, {{ $data['branch_district'] }}<br>
                    <b>NRC:</b> 303483-9<br>
                    <b>Tel:</b> {{ $data['branch_phone'] }}<br>
                    <b>Email:</b> facturacion.netplus@gmail.com
                </div>
            </div>
            <div class="invoice-info">
                <div class="invoice-title">FACTURA C.F.</div>
                <div class="invoice-details">
                    {{--                    <strong>No. Factura:</strong> FAC-2024-001<br>--}}
                    <strong>Fecha Emisión:</strong> {{ $data['invoice_issued'] }}<br>
                    <strong>Vencimiento:</strong> {{ $data['invoice_overdue'] }}<br>
                    <strong>Mes a cancelar:</strong> {{ $data['invoice_period'] }}<br>
                    @switch($data['invoice_status'])
                        @case(1)
                            <div class="status-badge status-issued">EMITIDA</div>
                            @break
                        @case(2)
                            <div class="status-badge status-pending">PENDIENTE</div>
                            @break
                        @case(3)
                            <div class="status-badge status-paid">PAGADA</div>
                            @break
                        @case(4)
                            <div class="status-badge status-overdue">VENCIDA</div>
                            @break
                        @case(5)
                            <div class="status-badge status-canceled">ANULADA</div>
                            @break
                    @endswitch
                </div>
            </div>
        </div>
    </div>

    <!-- Billing Information -->
    <div class="billing-section">
        <div class="billing-to">
            <div class="section-title">Datos del cliente</div>
            <strong>Cliente: {{ $data['client_name'] }}</strong><br>
            <b>Dirección:</b> {{ $data['client_address'] }}, {{ $data['client_state'] }},
            {{ $data['client_district'] }}.<br>
            <b>Tel:</b> {{ $data['client_mobile'] }}<br>
            <b>Email:</b> {{ $data['client_email'] }}
        </div>
        {{--        <div class="billing-details">--}}
        {{--            <div class="section-title">Detalles del Servicio</div>--}}
        {{--            <strong>ID Cliente:</strong> CLI-00123<br>--}}
        {{--            <strong>Plan:</strong> Internet Fibra 100 Mbps<br>--}}
        {{--            <strong>Periodo:</strong> Diciembre 2024<br>--}}
        {{--            <strong>IP Asignada:</strong> 192.168.1.100<br>--}}
        {{--            <strong>Método de Pago:</strong> Transferencia--}}
        {{--        </div>--}}
    </div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
        <tr>
            <th style="width: 8%;">#</th>
            <th style="width: 8%;">Cant.</th>
            <th style="width: 45%;">Descripción</th>
            <th style="width: 13%;" class="text-right">Precio Unit.</th>
            <th style="width: 13%;" class="text-right">Descuento</th>
            <th style="width: 13%;" class="text-right">Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach($data['items'] as $item)
            <tr>
                <td class="text-center">{{ $item['index'] }}</td>
                <td class="text-center">{{ $item['quantity'] }}</td>
                <td><strong>{{ $item['description'] }}</strong></td>
                <td class="text-right">$ {{ $item['unit_price'] }}</td>
                <td class="text-right">$ {{ number_format($item['discount'], 2) }}</td>
                <td class="text-right">$ {{ $item['total'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals-section">
        <div class="total-row">
            <div class="total-label">Subtotal:</div>
            <div class="total-value">$ {{ $data['subtotal'] }}</div>
        </div>
        <div class="total-row">
            <div class="total-label">Descuentos:</div>
            <div class="total-value">-$ {{ number_format($data['discounts'], 2) }}</div>
        </div>
        <div class="total-row">
            <div class="total-label">IVA (13%):</div>
            <div class="total-value">$ {{ $data['iva'] }}</div>
        </div>
        <div class="total-row grand-total">
            <div class="total-label">TOTAL A PAGAR:</div>
            <div class="total-value">$ {{ $data['total'] }}</div>
        </div>
    </div>

    <!-- Notes -->
    <div style="margin-top: 20px; font-size: 9pt;">
        <strong>Notas:</strong><br>
        • El pago debe realizarse antes de la fecha de vencimiento para evitar suspensión del servicio.<br>
        {{--        • Mora del 2% mensual por pagos después de la fecha de vencimiento.<br>--}}
        {{--        • Esta factura es un documento fiscal válido para efectos tributarios.<br>--}}
        • Para soporte técnico comuníquese al (503) 7626-6022, atención 24/7
    </div>

    <!-- Footer -->
    <div class="footer">
        {{--        Este documento fue generado electrónicamente y es válido sin sello ni firma.<br>--}}
        NETPLUS S.A. DE C.V. - Tel. (503) 7626-6022
    </div>
</div>
</body>
</html>
