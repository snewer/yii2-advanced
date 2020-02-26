<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "auctions".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $post_id
 * @property int $open_at
 * @property int $close_at
 * @property int $start
 * @property int $blitz
 * @property int $current_price
 * @property int $bids_count
 * @property int $step
 * @property int $anti_sniper
 */
class Auction extends \yii\db\ActiveRecord
{

    public $openAt;

    public $closeAt;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'auctions';
    }

    public function init()
    {
        $this->on(self::EVENT_AFTER_FIND, function () {
            $this->openAt = date('d.m.Y H:i', $this->open_at);
            $this->closeAt = date('d.m.Y H:i', $this->close_at);
        });
        foreach ([self::EVENT_BEFORE_INSERT, self::EVENT_BEFORE_UPDATE] as $eventName) {
            $this->on($eventName, function () {
                $this->open_at = \DateTimeImmutable::createFromFormat('d.m.Y H:i', $this->openAt)->getTimestamp();
                $this->close_at = \DateTimeImmutable::createFromFormat('d.m.Y H:i', $this->closeAt)->getTimestamp();
            });
        }
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'description', 'post_id'], 'required'],
            [['description'], 'string'],
            [['post_id', 'start', 'blitz', 'current_price', 'bids_count', 'step', 'anti_sniper'], 'integer'],
            [['openAt', 'closeAt'], 'date', 'format' => 'php:d.m.Y H:i'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
            'post_id' => 'Post ID',
            'open_at' => 'Open At',
            'close_at' => 'Close At',
            'start' => 'Start',
            'blitz' => 'Blitz',
            'current_price' => 'Current Price',
            'bids_count' => 'Bids Count',
            'step' => 'Step',
            'anti_sniper' => 'Anti Sniper',
        ];
    }

    /**
     * {@inheritdoc}
     * @return AuctionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new AuctionQuery(get_called_class());
    }
}
