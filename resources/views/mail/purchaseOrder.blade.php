ご注文ありがとうございます。
明細は以下のとおりです。

注文日：
    {{ $order->order_time }}

送り先：
    〒{{ $customer->post_no }}
    {{ $customer->address1 }}
    {{ $customer->address2 }}
    {{ $customer->address3 }}

    {{ $customer->customer_name }} 様

商品：
@foreach($details as $detail)
    商品名： {{ $detail->item->name }}
    数量： {{ $detail->item_qty }}
    金額：{{ $detail->item->price }} × {{ $detail->item_qty }} =  {{ $detail->item->price * $detail->item_qty }}

@endforeach

総額： {{ $totalPrice }}円

※これはテストです。実際には購入されていませんのでご安心ください。
