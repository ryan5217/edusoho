<?php

use Phpmig\Migration\Migration;

class BizPay extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec("
            CREATE TABLE IF NOT EXISTS `biz_pay_cashflow` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `title` VARCHAR(1024) NOT NULL DEFAULT '' COMMENT '标题',
              `sn` VARCHAR(64) NOT NULL COMMENT '账目流水号',
              `parent_sn` VARCHAR(64) COMMENT '本次交易的上一个账单的流水号',
              `user_id` int(10) unsigned NOT NULL COMMENT '账号ID，即用户ID',
              `user_balance` BIGINT(16) NOT NULL DEFAULT '0' COMMENT '账单生成后的对应账户的余额，若amount_type为coin，对应的是虚拟币账户，amount_type为money，对应的是现金庄户余额',
              `buyer_id` INT(10) unsigned NOT NULL DEFAULT '0' COMMENT '买家',
              `type` enum('inflow','outflow') NOT NULL COMMENT '流水类型',
              `action` VARCHAR(32) not null default '' COMMENT 'refund, purchase, recharge',
              `amount` BIGINT(16) unsigned NOT NULL DEFAULT '0' COMMENT '金额',
              `amount_type` VARCHAR(32) NOT NULL COMMENT 'ammount的类型：coin, money',
              `currency` VARCHAR(32) NOT NULL COMMENT '支付的货币: coin, CNY...',
              `order_sn` varchar(64) NOT NULL COMMENT '订单号',
              `trade_sn` varchar(64) NOT NULL COMMENT '交易号',
              `platform` VARCHAR(32) NOT NULL DEFAULT 'none' COMMENT '支付平台：none, alipay, wxpay...',
              `created_time` int(10) unsigned NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE(`sn`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='帐目流水';
        ");

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `biz_pay_user_balance` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `user_id` int(10) unsigned NOT NULL COMMENT '用户',
              `amount` BIGINT(16) NOT NULL DEFAULT '0' COMMENT '账户的虚拟币余额',
              `cash_amount` BIGINT(16) unsigned NOT NULL DEFAULT '0' COMMENT '现金余额',
              `locked_amount` BIGINT(16) unsigned NOT NULL DEFAULT '0' COMMENT '冻结虚拟币金额',
              `recharge_amount` BIGINT(16) unsigned NOT NULL DEFAULT '0' COMMENT '充值总额',
              `purchase_amount` BIGINT(16) unsigned NOT NULL DEFAULT '0' COMMENT '消费总额',
              `updated_time` int(10) unsigned NOT NULL DEFAULT '0',
              `created_time` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              UNIQUE(`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `biz_pay_trade` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `title` varchar(1024) NOT NULL COMMENT '标题',
              `trade_sn` varchar(64) NOT NULL COMMENT '交易号',
              `order_sn` varchar(64) NOT NULL COMMENT '客户订单号',
              `status` varchar(32) NOT NULL DEFAULT 'created' COMMENT '交易状态',
              `amount` BIGINT(16) unsigned NOT NULL DEFAULT '0' COMMENT '订单的需支付金额',
              `price_type` varchar(32) NOT NULL COMMENT '标价类型，现金支付or虚拟币；money, coin',
              `currency` varchar(32) NOT NULL DEFAULT '' COMMENT '支付的货币类型',
              `coin_amount` BIGINT(16) unsigned NOT NULL DEFAULT '0' COMMENT '虚拟币支付金额',
              `cash_amount` BIGINT(16) unsigned NOT NULL DEFAULT '0' COMMENT '现金支付金额',
              `rate` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '虚拟币和现金的汇率',
              `type` varchar(32) NOT NULL DEFAULT 'purchase' COMMENT '交易类型：purchase，recharge，refund',
              `seller_id` INT(10) unsigned DEFAULT '0' COMMENT '卖家id',
              `user_id` INT(10) unsigned NOT NULL COMMENT '买家id',
              `pay_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '交易时间',
              `apply_refund_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '申请退款时间',
              `refund_success_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '成功退款时间',
              `notify_data` text,
              `platform` varchar(32) NOT NULL DEFAULT '' COMMENT '第三方支付平台',
              `platform_sn` varchar(64) NOT NULL DEFAULT '' COMMENT '第三方支付平台的交易号',
              `platform_type` text COMMENT '在第三方系统中的支付方式',
              `platform_created_result` text,
              `platform_created_params` text COMMENT '在第三方系统创建支付订单时的参数信息',
              `updated_time` int(10) unsigned NOT NULL DEFAULT '0',
              `created_time` int(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              UNIQUE(`trade_sn`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `biz_pay_account` (
              `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
              `user_id` INT(10) unsigned NOT NULL COMMENT '所属用户',
              `password` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '密码',
              `salt` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '',
              `created_time` INT(10) unsigned NOT NULL DEFAULT '0',
              `updated_time` INT(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              UNIQUE(`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $connection->exec("
            CREATE TABLE IF NOT EXISTS `biz_pay_security_answer` (
              `id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
              `user_id` INT(10) unsigned NOT NULL COMMENT '所属用户',
              `question_key` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '安全问题的key',
              `answer` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '',
              `salt` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '',
              `created_time` INT(10) unsigned NOT NULL DEFAULT '0',
              `updated_time` INT(10) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              UNIQUE (`user_id`, `question_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    protected function isFieldExist($table, $filedName)
    {
        $biz = $this->getContainer();
        $db = $biz['db'];

        $sql = "DESCRIBE `{$table}` `{$filedName}`;";
        $result = $db->fetchAssoc($sql);

        return empty($result) ? false : true;
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $connection = $biz['db'];
        $connection->exec('
            DROP TABLE `biz_pay_cashflow`;
            DROP TABLE `biz_pay_user_balance`;
            DROP TABLE `biz_pay_trade`;
            DROP TABLE `biz_pay_account`;
            DROP TABLE `biz_pay_security_answer`;
        ');
    }
}
