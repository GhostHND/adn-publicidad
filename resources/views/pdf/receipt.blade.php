<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>
        {{ $document['number'] }}
    </title>

    <style>
        @page {
            margin: 26px 32px 28px 32px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 10px;
            color: #1d1d1b;
        }

        .header {
            width: 100%;
            border-collapse: collapse;
        }

        .header td {
            vertical-align: top;
        }

        .logo {
            max-width: 145px;
            max-height: 65px;
        }

        .brand-name {
            font-size: 23px;
            font-weight: bold;
        }

        .cyan {
            color: #0fa7b4;
        }

        .document-title {
            text-align: right;
            color: #0fa7b4;
            font-size: 20px;
            font-weight: bold;
        }

        .document-subtitle {
            text-align: right;
            color: #e84657;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .number {
            margin-top: 6px;
            text-align: right;
            font-size: 11px;
            font-weight: bold;
        }

        .accent {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        .accent-a {
            width: 78%;
            height: 5px;
            background: #0fa7b4;
        }

        .accent-b {
            width: 22%;
            height: 5px;
            background: #e84657;
        }

        .info-box {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e4e6e6;
            background: #f8f9f9;
        }

        .info-box td {
            padding: 9px 11px;
            vertical-align: top;
        }

        .label {
            font-size: 8px;
            color: #777777;
            text-transform: uppercase;
        }

        .value {
            margin-top: 2px;
            font-weight: bold;
            font-size: 10px;
        }

        .payment-box {
            width: 100%;
            margin-top: 13px;
            border-collapse: collapse;
        }

        .payment-box td {
            vertical-align: middle;
        }

        .payment-amount {
            width: 44%;
            padding: 16px;
            text-align: center;
            background: #0fa7b4;
            color: #ffffff;
        }

        .payment-amount-label {
            font-size: 9px;
        }

        .payment-amount-value {
            margin-top: 4px;
            font-size: 24px;
            font-weight: bold;
        }

        .payment-details {
            width: 56%;
            padding: 13px 16px;
            border: 1px solid #e4e6e6;
            border-left: 0;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
        }

        .details-table td {
            padding: 4px 0;
        }

        .details-table td:first-child {
            width: 42%;
            color: #777777;
        }

        .items-title {
            margin-top: 17px;
            margin-bottom: 7px;
            color: #0fa7b4;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table th {
            padding: 8px 7px;
            background: #1d1d1b;
            color: white;
            font-size: 8.5px;
            text-align: left;
        }

        .items-table td {
            padding: 8px 7px;
            border-bottom: 1px solid #eeeeee;
        }

        .right {
            text-align: right;
        }

        .summary {
            width: 45%;
            margin-left: 55%;
            margin-top: 13px;
            border-collapse: collapse;
        }

        .summary td {
            padding: 5px 8px;
            border-bottom: 1px solid #eeeeee;
        }

        .summary td:last-child {
            text-align: right;
            font-weight: bold;
        }

        .balance {
            background: #1d1d1b;
            color: #ffffff;
        }

        .notes {
            margin-top: 16px;
            padding: 10px;
            background: #f7fafa;
            border-left: 3px solid #0fa7b4;
            color: #555555;
            line-height: 1.5;
        }

        .non-fiscal {
            margin-top: 18px;
            padding: 10px;
            text-align: center;
            border: 1px solid #f0c9cd;
            background: #fff7f8;
            color: #b52f3d;
            font-size: 8.5px;
            font-weight: bold;
        }

        .signature {
            margin-top: 35px;
            width: 100%;
            text-align: center;
        }

        .signature-line {
            display: inline-block;
            width: 230px;
            border-top: 1px solid #777777;
            padding-top: 5px;
        }

        .footer {
            margin-top: 20px;
            border-top: 1px solid #dddddd;
            padding-top: 10px;
            text-align: center;
            color: #777777;
            font-size: 8px;
            line-height: 1.5;
        }
    </style>
</head>

<body>

@php
    $currency = $company['currency_symbol'] ?? 'L';

    $money = function ($value) use ($currency) {
        return $currency . ' ' . number_format(
            (float) $value,
            2,
            '.',
            ','
        );
    };
@endphp

<table class="header">
    <tr>
        <td width="55%">

            @if(!empty($company['logo']))
                <img
                    src="{{ $company['logo'] }}"
                    class="logo"
                    alt="ADN Publicidad"
                >
            @else
                <div class="brand-name">
                    ADN
                    <span class="cyan">
                        PUBLICIDAD
                    </span>
                </div>
            @endif

            <div
                style="
                    margin-top: 5px;
                    color: #666666;
                    font-size: 8.7px;
                    line-height: 1.5;
                "
            >
                {{ $company['location'] }}

                @if(!empty($company['phone']))
                    <br>
                    Tel. {{ $company['phone'] }}
                @endif

                @if(!empty($company['website']))
                    <br>
                    {{ $company['website'] }}
                @endif
            </div>

        </td>

        <td width="45%">

            <div class="document-title">
                RECIBO DE VENTA
            </div>

            <div class="document-subtitle">
                Documento no fiscal
            </div>

            <div class="number">
                {{ $document['number'] }}
            </div>

            <div
                style="
                    margin-top: 5px;
                    text-align: right;
                    color: #666666;
                "
            >
                {{ $document['date'] }}
            </div>

        </td>
    </tr>
</table>

<table class="accent">
    <tr>
        <td class="accent-a"></td>
        <td class="accent-b"></td>
    </tr>
</table>

<table class="info-box">
    <tr>
        <td width="50%">
            <div class="label">
                Recibido de
            </div>

            <div class="value">
                {{ $document['client']['name'] }}
            </div>

            @if(!empty($document['client']['identity']))
                <div
                    style="
                        margin-top: 4px;
                        color: #666666;
                    "
                >
                    RTN / Identidad:
                    {{ $document['client']['identity'] }}
                </div>
            @endif
        </td>

        <td width="25%">
            <div class="label">
                Teléfono
            </div>

            <div class="value">
                {{ $document['client']['phone'] ?? '-' }}
            </div>
        </td>

        <td width="25%">
            <div class="label">
                Venta
            </div>

            <div class="value">
                {{ $document['sale_number'] ?? '-' }}
            </div>
        </td>
    </tr>
</table>

<table class="payment-box">
    <tr>
        <td class="payment-amount">

            <div class="payment-amount-label">
                PAGO RECIBIDO
            </div>

            <div class="payment-amount-value">
                {{ $money(
                    $document['payment']['amount']
                ) }}
            </div>

        </td>

        <td class="payment-details">

            <table class="details-table">
                <tr>
                    <td>
                        Forma de pago
                    </td>

                    <td>
                        <strong>
                            {{
                                $document['payment']['method_label']
                            }}
                        </strong>
                    </td>
                </tr>

                @if(
                    !empty(
                        $document['payment']['financial_account']
                    )
                )
                    <tr>
                        <td>
                            Cuenta
                        </td>

                        <td>
                            <strong>
                                {{
                                    $document['payment']['financial_account']
                                }}
                            </strong>
                        </td>
                    </tr>
                @endif

                @if(
                    !empty(
                        $document['payment']['reference']
                    )
                )
                    <tr>
                        <td>
                            Referencia
                        </td>

                        <td>
                            <strong>
                                {{
                                    $document['payment']['reference']
                                }}
                            </strong>
                        </td>
                    </tr>
                @endif
            </table>

        </td>
    </tr>
</table>

@if(count($document['items']) > 0)

    <div class="items-title">
        Detalle de la venta
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th width="60%">
                    Producto / servicio
                </th>

                <th
                    width="15%"
                    class="right"
                >
                    Cant.
                </th>

                <th
                    width="25%"
                    class="right"
                >
                    Total
                </th>
            </tr>
        </thead>

        <tbody>
        @foreach(
            $document['items']
            as $item
        )
            <tr>
                <td>
                    <strong>
                        {{ $item['name'] }}
                    </strong>
                </td>

                <td class="right">
                    {{
                        number_format(
                            $item['quantity'],
                            2,
                            '.',
                            ''
                        )
                    }}
                </td>

                <td class="right">
                    {{
                        $money(
                            $item['subtotal']
                        )
                    }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endif

<table class="summary">

    <tr>
        <td>
            Total venta
        </td>

        <td>
            {{ $money(
                $document['sale_total']
            ) }}
        </td>
    </tr>

    <tr>
        <td>
            Total pagado
        </td>

        <td>
            {{ $money(
                $document['paid_total']
            ) }}
        </td>
    </tr>

    <tr class="balance">
        <td>
            Saldo pendiente
        </td>

        <td>
            {{ $money(
                $document['balance']
            ) }}
        </td>
    </tr>

</table>

@if(!empty($document['notes']))

    <div class="notes">
        {!! nl2br(
            e(
                $document['notes']
            )
        ) !!}
    </div>

@endif

<div class="non-fiscal">

    RECIBO DE VENTA - DOCUMENTO NO FISCAL

    <br>

    Este documento acredita el pago registrado
    y no constituye factura fiscal con CAI.

</div>

<div class="signature">

    <div class="signature-line">
        ADN Publicidad
        <br>
        Recibido conforme
    </div>

</div>

<div class="footer">

    Gracias por confiar en
    {{ $company['name'] }}.

    @if(!empty($company['website']))
        <br>
        {{ $company['website'] }}
    @endif

</div>

</body>
</html>