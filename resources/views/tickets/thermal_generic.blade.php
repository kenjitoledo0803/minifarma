<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket #{{ $sale->id }}</title>
    <style>
        @media print { @page { margin: 0; } body { margin: 0; } }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            width: {{ $width_css }};
            margin: 0 auto;
            padding: 5px;
            color: #000;
            background: #fff;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .line { border-bottom: 1px dashed #000; margin: 5px 0; }
        .product-row { margin-bottom: 2px; }
        .totals { margin-top: 10px; font-size: 14px; }
        img.logo { max-width: 60%; height: auto; display: block; margin: 0 auto 5px; }
        .header-section { margin-bottom: 10px; line-height: 1.4; }
        .footer-section { margin-top: 15px; font-size: 11px; text-align: center; }
        .folio-box { border: 1px solid #000; padding: 5px; margin: 5px 0; text-align: center; font-weight: bold; font-size: 14px; }
    </style>
</head>
<body onload="window.print()">

    @if($logo)
        <img src="{{ $logo }}" class="logo" alt="Logo">
    @endif

    <!-- 1. ENCABEZADO FIJO (Global "Mini Farmacia") -->
    <div class="text-center bold header-section">
        {!! $header !!}
    </div>

    <!-- 2. FOLIO (Automático del Sistema) -->
    <div class="folio-box">
        FOLIO: {{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}
    </div>

    <div class="line"></div>
    <div>
        Fecha: {{ $sale->created_at->format('d/m/Y H:i') }}<br>
        Cajero: {{ $sale->user->name ?? 'N/A' }}
    </div>
    <div class="line"></div>

    <!-- 3. ITEMS -->
    @foreach($items as $item)
        <div class="product-row">
            {{ $item->quantity }} x {{ $item->product_name_snapshot }}
            <div class="text-right">${{ number_format($item->subtotal, 2) }}</div>
        </div>
    @endforeach

    <div class="line"></div>

    <div class="text-right totals bold">
        TOTAL: ${{ number_format($sale->total, 2) }}
    </div>
    <div class="text-right">
        Pago: {{ ucfirst($sale->payment_method) }}
    </div>

    <div class="line"></div>

    <!-- 4. PIE DE TICKET (Dinámico por Sucursal) -->
    <div class="footer-section">
        <div class="bold">DATOS DE LA SUCURSAL</div>
        <br>
        @if($branch->address)
            Dirección: {{ $branch->address }}<br>
        @endif
        
        @if($branch->phone)
            Teléfono: {{ $branch->phone }}<br>
        @endif
        
        @if($branch->rfc)
            RFC: {{ $branch->rfc }}<br>
        @endif
        
        @if($branch->billing_email)
            Correo Facturación:<br>{{ $branch->billing_email }}<br>
        @endif
    </div>

    <div class="text-center" style="margin-top:15px;">
        ***
    </div>

</body>
</html>
