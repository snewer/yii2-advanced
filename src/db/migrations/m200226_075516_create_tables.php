<?php

use yii\db\Migration;

/**
 * Class m200226_075516_create_tables
 */
class m200226_075516_create_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{auctions}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'description' => $this->text()->notNull(),
            'post_id' => $this->integer()->notNull(),
            'open_at' => $this->integer()->notNull(),
            'close_at' => $this->integer()->notNull(),
            'start' => $this->integer()->defaultValue(0),
            'blitz' => $this->integer(),
            'current_price' => $this->integer()->defaultValue(0)->notNull(),
            'bids_count' => $this->integer()->defaultValue(0)->notNull(),
            'step' => $this->integer()->defaultValue(50)->notNull(),
            'anti_sniper' => $this->integer()->defaultValue(15)->notNull(),
        ], $tableOptions);

        $this->createTable('{{bids}}', [
            'id' => $this->primaryKey(),
            'auction_id' => $this->integer()->notNull(),
            'comment_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'text' => $this->text()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'price' => $this->integer()->notNull(),
            'status' => $this->integer(),
            'last_reply_status' => $this->integer(),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{auctions}}');
        $this->dropTable('{{bids}}');
    }
}
