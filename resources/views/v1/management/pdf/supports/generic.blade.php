<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Document</title>
    @include('v1.management.pdf.fonts')
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Calibri', sans-serif;
            font-size: 11px;
            line-height: 1.15em;
            color: #333;
            /*background: #fff;*/
            margin: 0.5cm;
            text-align: justify;
        }

        .header {
            width: 100%;
            margin-bottom: 6px;
        }

        .logo {
            width: 80px;
            vertical-align: middle;
            text-align: left;
        }

        .header-table {
            width: 100%;
            vertical-align: middle;
            font-family: 'Bodoni', sans-serif;
            font-size: 14px;
            padding-bottom: 3px;
            border-bottom: #0a0a0a 1px solid;
        }

        .content {
            width: 100%;
            font-family: 'Calibri', sans-serif;
            font-size: 11px;
        }

        .row {
            width: 100%;
            margin-bottom: 5px;
            display: block;
            font-size: 0;
            white-space: nowrap;
        }

        .col-full {
            width: 100%;
            display: block;
        }

        .col-half {
            width: 48%;
            display: inline-block;
            vertical-align: top;
            font-size: 11px;
            margin-right: 2%;
        }

        .col-third {
            width: 32%;
            display: inline-block;
            vertical-align: top;
            font-size: 11px;
            margin-right: 1%;
        }

        /*.col-half:first-child {*/
        /*    margin-right: 2%;*/
        /*}*/

        .field {
            margin-bottom: 8px;
        }

        .field label {
            font-family: 'Bodoni', sans-serif;
            font-weight: bold;
            font-size: 11px;
            color: #444;
            display: block;
            margin-bottom: 2px;
        }

        .field-content {
            border: 1px solid #999;
            padding: 2px;
            min-height: 10px;
            line-height: 0.9;
            background-color: #fafafa;
            border-radius: 3px;
            font-size: 11px;
        }

        .field-content-large {
            border: 1px solid #999;
            padding: 2px;
            min-height: 60px;
            line-height: 0.9;
            background-color: #fafafa;
            border-radius: 3px;
            font-size: 11px;
            display: block;
            white-space: normal;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
    </style>
</head>
<body>
<div class="header">
    <table class="header-table">
        <tr>
            <th style="width: 25%; border-right: #0a0a0a 1px solid;">
                <img src="{{ public_path('assets/img/logos/logo_blk.png') }}" alt="NETPLUS LOGO" class="logo">
            </th>
            <th style="width: 75%;">
                {{ ucwords($data['type']) }}
            </th>
        </tr>
    </table>
</div>
<div class="content">
    <div class="row">
        <div class="col-third">
            <div class="field">
                <label># Ticket</label>
                <div class="field-content">
                    {{ ucwords($data['ticket']) }}
                </div>
            </div>
        </div>

        <div class="col-third">
            <div class="field">
                <label>Cliente</label>
                <div class="field-content">
                    {{ ucwords($data['client']) }}
                </div>
            </div>
        </div>

        <div class="col-third">
            <div class="field">
                <label>Cliente</label>
                <div class="field-content">
                    {{ ucwords($data['client']) }}
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-full">
            <div class="field">
                <label>Solución</label>
                <div class="field-content-large">
                    Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusamus ad cupiditate ex facere incidunt
                    minus perspiciatis porro provident, quod soluta. Aspernatur blanditiis doloribus eum ex hic impedit
                    inventore praesentium tempora.
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
