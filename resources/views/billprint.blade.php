<!DOCTYPE html>
<html lang="en">
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
            width: 400px;
            max-width: 400px;
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

/*@media print{ */
/*    body, html, #page-container, .scrollable-page, .ps, .panel {*/
/*        height: 100% !important;*/
/*        width: 100% !important;*/
/*        display: inline-block;*/
/*    }*/
/*}*/
        @media print {
            .hidden-print,
            .hidden-print * {
                display: none !important;
            }
        }
}
    </style>
    <body>
        <button id="btnPrint" class="hidden-print">Print</button>
        <div class="ticket mr-1 ml-2">
            <div class="big"><span style="font-size: 14px">
                அருள்மிகு முருகன் துணை</span><br/>
            மால்ராஜா டிரேடர்ஸ்
        </div>
            <p class="centered"><b>3/158, பெரும்பாக்கம் மெயின் ரோடு,<br/>
                மேடவாக்கம், சென்னை - 600100<br/>
                செல்  : 9444390390, 9444858932
                <br/>
                <span style="font-size: 16px">GSTIN : 33CWTPS0135D1ZK</span></p>
                <br/>
            <div class="row mb-1">
                <div class="col-7">
                    <span>TO:</span></br>
                    <?php
                        if($orders->customerName == 'CASHBILL') {
                            echo '<span>' . $orders->customerName . '</span></br>';
                        }
                        else {
                            echo '<span>' . $orders->customerName . '</span></br>';
                            if(!empty($orders['customerAddress'])){
                                echo '<span> ✉ : ' . $orders->customerAddress . '</span><br>';
                            }
                            if(!empty($orders['customerMobile'])){
                                echo '<span> ☎ : ' . $orders->customerMobile . '</span>';
                            }
                        }
                    ?>
                    
                </div>
                <div class="col-5">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-left">பில் : </div>
                        </div>
                        <div class="col-6">
                            <div class="text-right">{{$orders->bill_id}}</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="text-left">தேதி: </div>
                        </div>
                        <div class="col-6">
                            <div class="text-right"><?php echo date('d-m-Y', strtotime($orders->date)); ?></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="text-left">நேரம்: </div>
                        </div>
                        <div class="col-6">
                            <div class="text-right"><?php echo date('h:i A', strtotime($orders->created_at)); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <table class="table-bordered">
                <thead>
                    <tr style="height:50px">
                        <th align="left" class="description">பொருள் பெயர்</th>
                        <th align="left" class="quantity">QTY</th>
                        <th align="left" class="price">விலை</th>
                        <th align="left" class="price">தொகை</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order_items as $item)
                        <tr style="height:40px">
                            <td class="description">
                                <?php
                                    if(!empty($item['product_tamilname'])) {
                                        echo $item->product_tamilname;
                                    }
                                    else {
                                        echo $item->product_name;
                                    }
                                ?>
                            </td>
                            <td align="center" class="quantity">{{$item->quantity}}</td>
                            <td align="right" class="price">{{number_format($item->quantity_price, 2)}}</td>
                            <td align="right" class="quantity">{{number_format(($item->quantity * $item->quantity_price), 2)}}</td>
                            {{-- <td align="right" class="quantity">{{number_format(($item->quantity * $item->quantity_price) - (($item->quantity * $item->quantity_price) * $item->gst)/100, 2)}}</td> --}}
                        </tr>
                    @endforeach
                </tbody>
            </table><br/>
            <div class="row">
                <div class="col-2">
                    <span style="font-size: 16px" >வகைகள்</span>
                </div>
                <div class="col-1">
                    <span style="font-size: 16px" >{{$total_items}}</span>
                </div>
                <div class="col-6 text-right">
                   <span style="font-size: 22px">மொத்தம்</span>
                </div>
                <div class="col-3 text-right">
                    <span style="font-size: 22px">₹{{number_format($orders->total, 2)}}</span>
                </div>
            </div>
            <div class="row">
                <div class="col-2">
                    <span style="font-size: 16px" >சிப்பம்</span>
                </div>
                <div class="col-1">
                    <span style="font-size: 16px" >{{$total_quantity}}</span>
                </div>
                <div class="col-6 text-right">
                    {{-- <span>வரி</span> --}}
                </div>
                <div class="col-3 text-right">
                    {{-- <span>₹{{number_format($totalGst, 2)}}</span> --}}
                </div>
            </div>
            <div class="row text-right">
                <div class="col-3 text-left">
                    <?php
                        if($orders->payment_method == 'Cash') {
                            echo '<img src="../../assets/img/rupee.png" style="max-width: fit-content;width: 64px;">';
                        } else if($orders->payment_method == 'UPI') {
                            echo '<img src="../../assets/img/paytm.png" style="max-width:none;width: 96px;height: 96px;">';
                        } else {
                            echo '<span><img src="../../assets/img/rupee.png" style="max-width: fit-content;width: 48px;"><img src="../../assets/img/paytm.png" style="max-width: fit-content;width: 48px;"></span>';
                        }
                    ?>
                </div>
                <div class="col-6">
                    {{--<span>மொத்தம்</span>--}}
                </div>
                <div class="col-3">
                    {{--<span>₹{{number_format($orders->total, 2)}}</span>--}}
                </div>
            </div></br>
            <!--<table style="font-size: 16px">-->
            <!--    <tr align="right">-->
            <!--        <td class="description1">தொகை</td>-->
            <!--        <td class="description1">₹{{number_format($orders->total - $totalGst, 2)}}</td>-->
            <!--    </tr>-->
            <!--    <tr align="right">-->
            <!--        <td class="description1">வரி</td>-->
            <!--        <td class="description1">₹{{number_format($totalGst, 2)}}</td>-->
            <!--    </tr>-->
            <!--    <tr align="right">  -->
            <!--        <td class="description1">மொத்தம்</td>-->
            <!--        <td class="description1">₹{{number_format($orders->total, 2)}}</td>-->
            <!--    </tr>-->
            <!--</table></br>-->
            <p class="centered">நன்றி! மீண்டும் வருக.
        </div>
        <!--<button id="btnPrint" class="hidden-print">Print</button>-->
        <script src="script.js"></script>
    </body>
    <script>
        const $btnPrint = document.querySelector("#btnPrint");
        $btnPrint.addEventListener("click", () => {
            window.print();
        });
    </script>
</html>
