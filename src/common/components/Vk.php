<?php

namespace common\components;

use common\models\Auction;
use common\models\Bid;
use yii\base\Component;
use GuzzleHttp\Client;
use yii\base\Exception;
use yii\helpers\Json;

class Vk extends Component
{

    public $groupId = 111101406;

    public $token = 'ac9c63261395d15a5b80219bbd6080f673b3df413d67150e9eed755d83620d3e0e72171bdbeede142ec9c';

    private const API_VERSION = '5.103';

    private $client;

    private function getClient()
    {
        if ($this->client === null) {
            $this->client = new Client();
        }
        return $this->client;
    }

    private static $delayLastCheck;

    private function requestDelay($requestsPerSecond)
    {
        $now = microtime(true);
        if (null !== static::$delayLastCheck) {
            $sleepTime = 1 / $requestsPerSecond;
            $lastRequest = $now - static::$delayLastCheck;
            if ($lastRequest < $sleepTime) {
                usleep((int)(($sleepTime - $lastRequest) * 1000000));
            }
        }
        static::$delayLastCheck = microtime(true);
    }

    private function callMethod($method, array $params = []): array
    {
        $this->requestDelay(2);
        $params['access_token'] = $this->token;
        $params['v'] = self::API_VERSION;
        $url = 'https://api.vk.com/method/' . $method;
        $response = $this->getClient()
            ->post($url, [
                'form_params' => $params,
            ])
            ->getBody()
            ->getContents();
        $response = Json::decode($response);

        if (!empty($response['error']['error_msg'])) {
            throw new Exception(sprintf('%s (%s)', $response['error']['error_msg'], Json::encode($response)));
        }

        return $response;
    }

    private function getLongPollServer()
    {
        return $this->callMethod('groups.getLongPollServer', [
            'group_id' => $this->groupId
        ]);
    }

    private function getUserName($id)
    {
        $response = $this->callMethod('users.get', [
            'user_ids' => $id,
        ]);

        return $response['response'][0]['first_name'] ?? null;
    }

    private function saveCommentAsBid(array $object)
    {
        $id = $object['id'];
        $postId = $object['post_id'];
        $fromId = $object['from_id'];
        $date = $object['date'];
        $text = $object['text'];

        if ($fromId == -$this->groupId) {
            return;
        }

        $auction = Auction::findOne(['post_id' => $postId]);
        if ($auction) {
            $exist = Bid::find()->where(['comment_id' => $id])->limit(1)->exists();
            if (!$exist) {
                $bid = new Bid();
                $bid->auction_id = $auction->id;
                $bid->comment_id = $id;
                $bid->user_id = $fromId;
                $bid->text = $text;
                $bid->created_at = $date;
                $bid->price = 0;
                $bid->status = Bid::STATUS_NEW;
                $bid->save();
            }
        }
    }


    private function sendCommentary($postId, $replyToComment, $message, $guid = null)
    {
        try {
            $this->callMethod('wall.createComment', [
                'owner_id' => -$this->groupId,
                'post_id' => $postId,
                'message' => $message,
                'reply_to_comment' => $replyToComment,
                'guid' => $guid,
            ]);
        } catch (\Throwable $e) {

        }
    }

    private function saveTs($value)
    {
        $file = \Yii::getAlias('@console/ts.txt');
        file_put_contents($file, $value);
    }

    private function getTs()
    {
        $file = \Yii::getAlias('@console/ts.txt');
        if (is_file($file)) {
            return file_get_contents($file);
        } else {
            return 1;
        }
    }

    public function pool()
    {
        $server = $this->getLongPollServer();

        $lastTs = $this->getTs();
        $poolUrl = sprintf('%s?act=a_check&key=%s&ts=%s&wait=3', $server['response']['server'], $server['response']['key'], $lastTs);
        $this->saveTs($server['response']['ts']);
        $poolResponse = file_get_contents($poolUrl);
        $poolResponse = Json::decode($poolResponse);

        if (!empty($poolResponse['updates'])) {
            foreach ($poolResponse['updates'] as $update) {
                switch ($update['type']) {
                    case 'wall_reply_new':
                        $this->saveCommentAsBid($update['object']);
                        break;
                }
            }
        }
    }

    private function bidConfirmed($fromId = null)
    {
        if ($fromId && $fromId > 0) {
            return sprintf('[id%d|%s], Ваша ставка принята ✌', $fromId, $this->getUserName($fromId));
        } else {
            return 'Ваша ставка принята ✌';
        }
    }

    private function outbid($fromId = null)
    {
        if ($fromId && $fromId > 0) {
            return sprintf('[id%d|%s], Ваша ставка перебита.', $fromId, $this->getUserName($fromId));
        } else {
            return 'Ваша ставка перебита.';
        }
    }

    private function winner($fromId = null)
    {
        if ($fromId && $fromId > 0) {
            return sprintf('[id%d|%s], поздравляем! Лот Ваш 💪', $fromId, $this->getUserName($fromId));
        } else {
            return 'Поздравляем! Лот ваш.';
        }
    }

    private function invalidStep($fromId, $step)
    {
        if ($fromId && $fromId > 0) {
            return sprintf('[id%d|%s], ставка должна быть кратной %d ✋', $fromId, $this->getUserName($fromId), $step);
        } else {
            return 'Ставка должна быть кратной ' . $step . '.';
        }
    }

    public function processBids()
    {
        $bid = Bid::find()
            ->where(['status' => Bid::STATUS_NEW])
            ->limit(1)
            ->orderBy('created_at DESC')
            ->one();

        if ($bid === null) {
            return;
        }

        $auction = $bid->auction;

        if ($bid->created_at >= $auction->open_at && $bid->created_at <= $auction->close_at) {

            if (preg_match('/^([\d\s]+)/', $bid->text, $m)) {
                $price = (int)preg_replace('/\D+/', '', $m[1]);

                if ($price % $auction->step !== 0) {
                    $bid->status = Bid::STATUS_IGNORE;
                    $bid->save();
                    $message = $this->invalidStep($bid->user_id, $auction->step);
                    $this->sendCommentary(
                        $bid->auction->post_id,
                        $bid->comment_id,
                        $message,
                        'invalid-step' . $bid->comment_id
                    );
                    return;
                }

                // todo: blitz
                // todo: min price

                $isHighestPrice = !Bid::find()
                    ->where([
                        'auction_id' => $bid->auction_id,
                        'status' => [Bid::STATUS_CONFIRMED, Bid::STATUS_NEW],
                    ])
                    ->andWhere(['<>', 'id', $bid->id])
                    //->andWhere(['<>', 'user_id', $bid->user_id])
                    ->andWhere(['>', 'price', $price])
                    ->andWhere(['<=', 'created_at', $bid->created_at])
                    ->andWhere(['between', 'created_at', $auction->open_at, $auction->close_at])
                    ->exists();

                if ($isHighestPrice) {
                    $bid->price = $price;
                    $bid->status = Bid::STATUS_CONFIRMED;
                    $bid->save();

                    $auction->bids_count += 1;
                    $auction->current_price = $price;

                    if ($auction->close_at - $bid->created_at <= $auction->anti_sniper * 60) {
                        $auction->closeAt = date('d.m.Y H:i', $auction->close_at + $auction->anti_sniper * 60);
                        $this->sendCommentary(
                            $bid->auction->post_id,
                            null,
                            sprintf('Антиснайпер: окончание аукциона через %d минут.', $auction->anti_sniper)
                        );
                    }
                    $auction->save(false);

                    $this->sendCommentary(
                        $bid->auction->post_id,
                        $bid->comment_id,
                        $this->bidConfirmed($bid->user_id),
                        'confirm' . $bid->comment_id
                    );

                    $outbids = Bid::find()
                        ->where([
                            'auction_id' => $bid->auction_id,
                            'status' => [Bid::STATUS_CONFIRMED, Bid::STATUS_NEW],
                        ])
                        ->andWhere(['<>', 'id', $bid->id])
                        //->andWhere(['<>', 'user_id', $bid->user_id])
                        ->andWhere(['<', 'price', $price])
                        ->andWhere(['between', 'created_at', $auction->open_at, $auction->close_at])
                        ->all();

                    foreach ($outbids as $outbid) {
                        $outbid->status = Bid::STATUS_OUTBID;
                        $outbid->save();
                        $message = $this->outbid($outbid->user_id);
                        $this->sendCommentary(
                            $outbid->auction->post_id,
                            $outbid->comment_id,
                            $message,
                            'outbid' . $outbid->comment_id
                        );
                    }

                } else {
                    $bid->status = Bid::STATUS_OUTBID;
                    $bid->save();
                    $message = $this->outbid($bid->user_id);
                    $this->sendCommentary(
                        $bid->auction->post_id,
                        $bid->comment_id,
                        $message,
                        'outbid' . $bid->comment_id
                    );
                }

            } else {
                $bid->status = Bid::STATUS_IGNORE;
                $bid->save();
            }

        } else {
            $bid->status = Bid::STATUS_IGNORE;
            $bid->save();
        }

    }

    public function winners()
    {
        $closedAuctions = Auction::find()->where(['<', 'close_at', time()])->all();
        foreach ($closedAuctions as $auction) {
            $winner = Bid::find()
                ->where([
                    'auction_id' => $auction->id,
                    'status' => Bid::STATUS_CONFIRMED,
                ])
                ->orderBy('price DESC')
                ->limit(1)
                ->one();

            if ($winner && $winner->status != Bid::STATUS_WINNER) {
                $winner->status = Bid::STATUS_WINNER;
                $winner->save(false);
                $message = $this->winner($winner->user_id);
                $this->sendCommentary(
                    $auction->post_id,
                    $winner->comment_id,
                    $message,
                    'outbid' . $winner->comment_id
                );
            }
        }
    }

}