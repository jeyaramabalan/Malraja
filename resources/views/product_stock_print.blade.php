<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    @include('layouts.header')
    <style>
        * { font-size: 20px; font-family: 'Calibri'; }
        td.description, th.description { width: 400px; max-width: 400px; }
        td.quantity, th.quantity { width: 80px; max-width: 80px; word-break: break-all; }
        .ticket { width: 555px; max-width: 555px; }
        img { max-width: inherit; width: inherit; }
        @media print { .hidden-print, .hidden-print * { display: none !important; } }
    </style>
    <body>
        <div class="ticket mr-1 ml-2">
            <table class="table-bordered">
                <thead>
                    <tr style="height:50px">
                        <th align="left" class="description">பொருள் பெயர்</th>
                        <th align="left" class="quantity">STOCK</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($stocks as $item)
                        <tr style="height:40px">
                            <td class="description">
                                <?php
                                    if(!empty($item['product_tamilname'])) {
                                        echo $item->product_tamilname;
                                    }
                                    else {
                                        echo $item->productName;
                                    }
                                ?>
                            </td>
                            <td align="center" class="quantity">{{$item->purchase - ($item->sale - $item->sale_return)}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table><br/>
            <button id="btnPrint" class="hidden-print">{{ __('bill.print') }}</button> {{-- Re-using key from bill.php --}}
        </div>
    </body>
    <script>
        const $btnPrint = document.querySelector("#btnPrint");
        $btnPrint.addEventListener("click", () => {
            window.print();
        });
    </script>
</html>