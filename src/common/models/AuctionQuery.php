<?php

namespace common\models;

/**
 * This is the ActiveQuery class for [[Auction]].
 *
 * @see Auction
 */
class AuctionQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Auction[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Auction|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
