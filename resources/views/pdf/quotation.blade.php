<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>
        {{ $document['number'] }}
    </title>

    <style>
        @page {
            margin: 24px 30px 28px 30px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 10px;
            color: #1d1d1b;
            background: #ffffff;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
        }

        .brand-cell {
            width: 58%;
        }

        .document-cell {
            width: 42%;
            text-align: right;
        }

        .logo {
            max-width: 150px;
            max-height: 70px;
            margin-bottom: 5px;
        }

        .brand-name {
            margin: 0;
            font-size: 23px;
            font-weight: bold;
            letter-spacing: -0.7px;
            color: #1d1d1b;
        }

        .brand-accent {
            color: #0fa7b4;
        }

        .business-data {
            margin-top: 4px;
            color: #666666;
            line-height: 1.55;
            font-size: 8.8px;
        }

        .document-title {
            font-size: 21px;
            font-weight: bold;
            color: #0fa7b4;
            margin-bottom: 5px;
        }

        .document-number {
            display: inline-block;
            padding: 5px 10px;
            background: #1d1d1b;
            color: #ffffff;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }

        .accent-line {
            width: 100%;
            margin: 16px 0;
            border-collapse: collapse;
        }

        .accent-line .cyan {
            background: #0fa7b4;
            height: 5px;
            width: 78%;
        }

        .accent-line .red {
            background: #e84657;
            height: 5px;
            width: 22%;
        }

        .section {
            margin-top: 12px;
        }

        .section-title {
            margin-bottom: 7px;
            color: #0fa7b4;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        .client-box {
            width: 100%;
            border-collapse: collapse;
            background: #f7f8f8;
            border: 1px solid #e6e8e8;
        }

        .client-box td {
            padding: 9px 11px;
            vertical-align: top;
        }

        .label {
            color: #777777;
            font-size: 8px;
            text-transform: uppercase;
        }

        .value {
            margin-top: 2px;
            font-size: 10px;
            font-weight: bold;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .items-table thead {
            display: table-header-group;
        }

        .items-table th {
            padding: 8px 7px;
            background: #1d1d1b;
            color: #ffffff;
            text-align: left;
            font-size: 8.5px;
        }

        .items-table td {
            padding: 8px 7px;
            border-bottom: 1px solid #e7e7e7;
            vertical-align: top;
        }

        .items-table .center {
            text-align: center;
        }

        .items-table .right {
            text-align: right;
        }

        .item-name {
            font-weight: bold;
            font-size: 9.6px;
        }

        .item-description {
            margin-top: 2px;
            color: #777777;
            font-size: 8px;
            line-height: 1.4;
        }

        .totals-wrapper {
            width: 100%;
            margin-top: 13px;
            border-collapse: collapse;
        }

        .totals-wrapper td {
            vertical-align: top;
        }

        .notes-cell {
            width: 56%;
            padding-right: 18px;
        }

        .totals-cell {
            width: 44%;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 5px 7px;
            border-bottom: 1px solid #eeeeee;
        }

        .totals-table .name {
            color: #666666;
        }

        .totals-table .amount {
            text-align: right;
            font-weight: bold;
        }

        .totals-table .grand-total td {
            padding-top: 8px;
            padding-bottom: 8px;
            background: #0fa7b4;
            color: #ffffff;
            border: none;
            font-size: 13px;
            font-weight: bold;
        }

        .note-box {
            padding: 9px 10px;
            border-left: 3px solid #0fa7b4;
            background: #f7fafa;
            color: #555555;
            line-height: 1.5;
        }

        .validity {
            margin-top: 12px;
            padding: 8px 10px;
            background: #fff8f8;
            border-left: 3px solid #e84657;
            color: #555555;
        }

        .footer {
            margin-top: 22px;
            padding-top: 11px;
            border-top: 1px solid #dddddd;
            text-align: center;
            color: #777777;
            font-size: 8px;
            line-height: 1.5;
        }

        .thanks {
            margin-top: 12px;
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            color: #1d1d1b;
        }

        .small {
            font-size: 8px;
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

<table class="header-table">
    <tr>
        <td class="brand-cell">

            @if(!empty($company['logo']))
                <img
                    src="{{ $company['logo'] }}"
                    class="logo"
                    alt="ADN Publicidad"
                >
            @else
                <div class="brand-name">
                    ADN
                    <span class="brand-accent">
                        PUBLICIDAD
                    </span>
                </div>
            @endif

            <div class="business-data">

                {{ $company['location'] }}

                @if(!empty($company['phone']))
                    <br>
                    Tel. {{ $company['phone'] }}
                @endif

                @if(!empty($company['email']))
                    <br>
                    {{ $company['email'] }}
                @endif

                @if(!empty($company['website']))
                    <br>
                    {{ $company['website'] }}
                @endif

            </div>
        </td>

        <td class="document-cell">

            <div class="document-title">
                COTIZACIÓN
            </div>

            <div class="document-number">
                {{ $document['number'] }}
            </div>

            <div
                style="
                    margin-top: 9px;
                    color: #666666;
                    line-height: 1.6;
                "
            >
                Fecha:
                <strong>
                    {{ $document['date'] ?? '-' }}
                </strong>

                @if(!empty($document['valid_until']))
                    <br>

                    Válida hasta:
                    <strong>
                        {{ $document['valid_until'] }}
                    </strong>
                @endif
            </div>
        </td>
    </tr>
</table>

<table class="accent-line">
    <tr>
        <td class="cyan"></td>
        <td class="red"></td>
    </tr>
</table>

<div class="section-title">
    Información del cliente
</div>

<table class="client-box">
    <tr>
        <td width="50%">
            <div class="label">
                Cliente
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

            @if(!empty($document['client']['email']))
                <div
                    style="
                        margin-top: 4px;
                        color: #666666;
                    "
                >
                    {{ $document['client']['email'] }}
                </div>
            @endif
        </td>

        <td width="25%">
            <div class="label">
                Ubicación
            </div>

            <div class="value">
                {{ $document['client']['city'] ?? '-' }}
            </div>
        </td>
    </tr>

    @if(!empty($document['client']['address']))
        <tr>
            <td colspan="3">
                <div class="label">
                    Dirección
                </div>

                <div
                    style="
                        margin-top: 3px;
                    "
                >
                    {{ $document['client']['address'] }}
                </div>
            </td>
        </tr>
    @endif
</table>

<div class="section">

    <div class="section-title">
        Detalle de la cotización
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th width="43%">
                    Descripción
                </th>

                <th
                    width="12%"
                    class="center"
                >
                    Cant.
                </th>

                <th
                    width="15%"
                    class="center"
                >
                    Medida
                </th>

                <th
                    width="15%"
                    class="right"
                >
                    Precio
                </th>

                <th
                    width="15%"
                    class="right"
                >
                    Total
                </th>
            </tr>
        </thead>

        <tbody>

        @foreach($document['items'] as $item)

            <tr>
                <td>
                    <div class="item-name">
                        {{ $item['name'] }}
                    </div>

                    @if(
                        !empty($item['description'])
                        &&
                        $item['description']
                        !==
                        $item['name']
                    )
                        <div class="item-description">
                            {{ $item['description'] }}
                        </div>
                    @endif
                </td>

                <td class="center">
                    {{ number_format(
                        $item['quantity'],
                        2,
                        '.',
                        ''
                    ) }}
                </td>

                <td class="center">
                    {{ $item['dimensions'] ?? '-' }}
                </td>

                <td class="right">
                    {{ $money($item['unit_price']) }}
                </td>

                <td class="right">
                    <strong>
                        {{ $money($item['subtotal']) }}
                    </strong>
                </td>
            </tr>

        @endforeach

        </tbody>
    </table>
</div>

<table class="totals-wrapper">
    <tr>
        <td class="notes-cell">

            @if(!empty($document['notes']))
                <div class="section-title">
                    Observaciones
                </div>

                <div class="note-box">
                    {!! nl2br(
                        e(
                            $document['notes']
                        )
                    ) !!}
                </div>
            @endif

            @if(!empty($document['terms']))
                <div
                    class="section-title"
                    style="
                        margin-top: 12px;
                    "
                >
                    Condiciones
                </div>

                <div class="note-box">
                    {!! nl2br(
                        e(
                            $document['terms']
                        )
                    ) !!}
                </div>
            @endif

            @if(!empty($document['valid_until']))
                <div class="validity">
                    Esta cotización es válida hasta
                    <strong>
                        {{ $document['valid_until'] }}
                    </strong>.
                </div>
            @endif

        </td>

        <td class="totals-cell">

            <table class="totals-table">

                <tr>
                    <td class="name">
                        Subtotal
                    </td>

                    <td class="amount">
                        {{ $money(
                            $document['subtotal']
                        ) }}
                    </td>
                </tr>

                @if(
                    (float)
                    $document['discount']
                    > 0
                )
                    <tr>
                        <td class="name">
                            Descuento
                        </td>

                        <td class="amount">
                            -
                            {{ $money(
                                $document['discount']
                            ) }}
                        </td>
                    </tr>
                @endif

                @if(
                    (float)
                    $document['tax']
                    > 0
                )
                    <tr>
                        <td class="name">
                            Impuesto
                        </td>

                        <td class="amount">
                            {{ $money(
                                $document['tax']
                            ) }}
                        </td>
                    </tr>
                @endif

                <tr class="grand-total">
                    <td>
                        TOTAL
                    </td>

                    <td
                        style="
                            text-align: right;
                        "
                    >
                        {{ $money(
                            $document['total']
                        ) }}
                    </td>
                </tr>
            </table>

        </td>
    </tr>
</table>

<div class="thanks">
    Gracias por confiar en ADN Publicidad.
</div>

<div class="footer">

    Cotización comercial emitida por
    {{ $company['name'] }}.

    <br>

    Los precios, cantidades y condiciones
    corresponden exclusivamente a este documento.

    @if(!empty($company['website']))
        <br>
        {{ $company['website'] }}
    @endif

</div>

</body>
</html>