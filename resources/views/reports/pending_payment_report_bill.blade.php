<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('layouts.header')
<style>
        * {
            font-size: 20px;
            font-family: 'Calibri';
        }

        /* td,
        th,
        tr,
        table {
            border-top: 1px solid black;
            border-collapse: collapse;
        } */

        td.description,
        th.description {
            width: 200px;
            max-width: 200px;
            word-break: break-all;
        }
        
        td.description1,
        th.description1 {
            width: 400px;
            max-width: 400px;
        }

        td.quantity,
        th.quantity {
            width: 80px;
            max-width: 80px;
            word-break: break-all;
        }

        td.quantity,
        th.quantity {
            width: 120px;
            max-width: 120px;
            word-break: break-all;
        }

        td.price,
        th.price {
            width: 133px;
            max-width: 133px;
            word-break: break-all;
        }

        .centered {
            text-align: center;
            align-content: center;
        }
        .big {
            font-size: 36px;
            text-align: center;
            align-content: center;
            font-weight: bold;
        }

        .ticket {
            width: 555px;
            max-width: 555px;
        }

        img {
            max-width: inherit;
            width: inherit;
        }

        @media print {
            .hidden-print,
            .hidden-print * {
                display: none !important;
            }
        }
    </style>
<div class="ticket mr-1 ml-2">
<body>
<h2 align="center">Malraja Traders</h3>
<p align="center">
<b>{{ __('reports.stock_report') }}</p>
<p align="center"><b>{{$date}}</p>
<table width="100%" border="1" cellpadding="2" cellspacing="0">
<thead>
<tr>
<th>{{ __('reports.bill_no') }}</th>
<th>{{ __('reports.customer') }}</th>
<th>{{ __('reports.amount') }}</th>
<th>{{ __('reports.pending') }}</th>
</tr>
</thead>
<body>
@foreach ($completeData as $item)
<tr style="height:40px">
<td class="quantity">{{$item->bill_id}}</td>
<td align="center" class="description">{{$item->customerName}}</td>
<td align="right" class="price">{{number_format($item->total, 2)}}</td>
<td align="right" class="quantity1">{{number_format($item->paymentPending, 2)}}</td>
</tr>
@endforeach
</body>
</table>
<button id="btnPrint" class="hidden-print">{{ __('reports.print') }}</button>
    </body>
</div>
<script>
    const $btnPrint = document.querySelector("#btnPrint");
    $btnPrint.addEventListener("click", () => {
        window.print();
    });
</script>
</html>