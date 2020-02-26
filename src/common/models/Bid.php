<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "bids".
 *
 * @property int $id
 * @property int $auction_id
 * @property int $comment_id
 * @property int $user_id
 * @property string $text
 * @property int $created_at
 * @property int $price
 * @property int $status
 * @property int $last_reply_status
 *
 * via getters:
 * @property Auction $auction
 */
class Bid extends \yii\db\ActiveRecord
{

    const STATUS_NEW = 1;

    const STATUS_CONFIRMED = 2;

    const STATUS_OUTBID = 3;

    const STATUS_IGNORE = 4;

    const STATUS_WRONG_PRICE = 5;

    const STATUS_WINNER = 6;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bids';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['auction_id', 'comment_id', 'user_id', 'text', 'created_at', 'price'], 'required'],
            [['auction_id', 'comment_id', 'user_id', 'created_at', 'price', 'status', 'last_reply_status'], 'integer'],
            [['text'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'auction_id' => 'Auction ID',
            'comment_id' => 'Comment ID',
            'user_id' => 'User ID',
            'text' => 'Text',
            'created_at' => 'Created At',
            'price' => 'Price',
            'status' => 'Status',
            'last_reply_status' => 'Last Reply Status',
        ];
    }

    /**
     * {@inheritdoc}
     * @return BidQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new BidQuery(get_called_class());
    }

    public function getAuction()
    {
        return $this->hasOne(Auction::class, ['id' => 'auction_id']);
    }
}
