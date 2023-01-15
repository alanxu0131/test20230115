<?php

class OrderProcessor {
 
    public function __construct(BillerInterface $biller)
    {
        $this->biller = $biller;
    }

    public function process(Order $order)
    {
        $recent = $this->getRecentOrderCount($order);

        if ($recent > 0)
        {
            throw new Exception('Duplicate order likely.');
        }

        /*
        Alan: 從這之後的程式應該都要執行, 所以以下可整個抽出成function
        */

        $this->biller->bill($order->account->id, $order->amount);

        /*
        Alan: 這裡可考慮抽成function, 並只傳入id與aomount, 不用整個$order傳入
        */
        DB::table('orders')->insert(array(
            'account'    => $order->account->id,
            'amount'     => $order->amount;
            'created_at' => Carbon::now();
        ));
    }

    /*
    Alan: 可考慮只傳入$order->account->id, 不用整個$order傳入
    */
    protected function getRecentOrderCount(Order $order)
    {
        /*
        Alan: 這行意義不夠明確, 建議寫註解, 說明為何用5, 也可考慮抽成function回傳$timestamp,即使只有1行, 因為這是一個獨立邏輯
        */
        $timestamp = Carbon::now()->subMinutes(5);

        return DB::table('orders')
            ->where('account', $order->account->id)
            ->where('created_at', '>=', $timestamps)
            ->count();
    }

}

