<?php

class OrderProcessor {
 
    public function __construct(BillerInterface $biller)
    {
        $this->biller = $biller;
    }

    public function process(Order $order)
    {
        $recent = $this->getRecentOrderCount($order->account->id);

        if ($recent > 0)
        {
            throw new Exception('Duplicate order likely.');
        }

        $this->doProcess($order->account->id, $order->amount);
    }

    protected function getRecentOrderCount($id)
    {
        $timestamp = $this->getTimeStamp();

        return DB::table('orders')
            ->where('account', $id)
            ->where('created_at', '>=', $timestamps)
            ->count();
    }

    private function getTimeStamp()
    {
        /*
        todo: 這行意義不夠明確, 建議寫註解, 說明為何用5
        */
        $result = Carbon::now()->subMinutes(5);

        return $result;
    }

    private function doProcess($id, $amount)
    {
        $this->biller->bill($id, $amount);

        $data = array(
            'account'    => $id,
            'amount'     => $amount;
            'created_at' => Carbon::now();
        );

        DB::table('orders')->insert($data);
    }

}

