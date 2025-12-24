<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket #{{ $sale->id }}</title>
    <style>
        @media print {
            @page { margin: 0; }
            body { margin: 0; }
        }
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
        img.logo { max-width: 80%; height: auto; display: block; margin: 0 auto 5px; }
    </style>
</head>
<body onload="window.print()">

    @if($logo)
        <img src="{{ $logo }}" class="logo" alt="Logo">
    @endif

    <div class="text-center bold header-text">
        {!! $header !!}
    </div>

    <div class="text-center">
        @if($branch->address) {{ $branch->address }}<br> @endif
        @if($branch->phone) Tel: {{ $branch->phone }} @endif
    </div>

    <div class="line"></div>

    <div>
        Folio: {{ $sale->id }}<br>
        Fecha: {{ $sale->created_at->format('d/m/Y H:i') }}<br>
        Cajero: {{ $sale->user->name ?? 'N/A' }}
    </div>

    <div class="line"></div>

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

    <div class="text-center footer-text">
        {!! $footer !!}
    </div>

</body>
</html>
