<!DOCTYPE html>
<html>
<head>
    <title>{{ $kot->type }} - {{ $kot->kot_no }}</title>
    <style>
        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }
            body {
                margin: 0;
                padding: 0;
            }
        }
        
        body {
            font-family: 'Courier New', monospace;
            width: 80mm;
            margin: 0 auto;
            padding: 10px;
            font-size: 12px;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        
        .header h2 {
            margin: 5px 0;
            font-size: 20px;
        }
        
        .header h1 {
            margin: 10px 0;
            font-size: 28px;
            font-weight: bold;
        }
        
        .info {
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .items {
            margin-bottom: 10px;
        }
        
        .item {
            margin-bottom: 10px;
            border-bottom: 1px dotted #000;
            padding-bottom: 10px;
        }
        
        .item-header {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .item-qty {
            font-size: 18px;
            font-weight: bold;
        }
        
        .instructions {
            margin-top: 5px;
            font-style: italic;
            padding-left: 10px;
        }
        
        .footer {
            border-top: 2px dashed #000;
            padding-top: 10px;
            text-align: center;
        }
        
        .notes {
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #000;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $kot->branch->name ?? 'Ravon Bakers & Restaurant' }}</h2>
        <h1>{{ $kot->type }}</h1>
        <div style="font-size: 16px; font-weight: bold;">{{ $kot->kot_no }}</div>
    </div>

    <div class="info">
        <div class="info-row">
            <span>Order Type:</span>
            <strong>{{ $kot->order_type }}</strong>
        </div>
        @if($kot->table_no)
        <div class="info-row">
            <span>Table No:</span>
            <strong>{{ $kot->table_no }}</strong>
        </div>
        @endif
        <div class="info-row">
            <span>Date/Time:</span>
            <span>{{ $kot->created_at->format('d/m/Y H:i') }}</span>
        </div>
        <div class="info-row">
            <span>Waiter:</span>
            <span>{{ $kot->user_name }}</span>
        </div>
    </div>

    <div class="items">
        @foreach($kot->kotItems as $item)
        <div class="item">
            <div class="item-header">
                <span class="item-qty">{{ $item->quantity }} x</span>
                <span>{{ $item->item_name }}</span>
            </div>
            @if($item->special_instructions)
            <div class="instructions">
                → {{ $item->special_instructions }}
            </div>
            @endif
        </div>
        @endforeach
    </div>

    <div class="footer">
        <div style="margin: 10px 0; font-size: 10px;">
            Printed: {{ now()->format('d/m/Y H:i:s') }}
        </div>
        <div style="font-size: 14px; font-weight: bold;">
            Total Items: {{ $kot->kotItems->sum('quantity') }}
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
