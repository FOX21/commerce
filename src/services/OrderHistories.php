<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\services;

use Craft;
use craft\commerce\elements\Order;
use craft\commerce\events\OrderStatusEvent;
use craft\commerce\models\OrderHistory;
use craft\commerce\Plugin;
use craft\commerce\records\OrderHistory as OrderHistoryRecord;
use craft\db\Query;
use yii\base\Component;
use yii\base\Exception;

/**
 * Order history service.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 2.0
 */
class OrderHistories extends Component
{
    // Constants
    // =========================================================================

    /**
     * @event OrderStatusEvent The event that is triggered when order status is changed
     *
     * Plugins can get notified when an order status is changed
     *
     * ```php
     * use craft\commerce\events\OrderStatusEvent;
     * use craft\commerce\services\OrderHistories;
     * use yii\base\Event;
     *
     * Event::on(OrderHistories::class, OrderHistories::EVENT_ORDER_STATUS_CHANGE, function(OrderStatusEvent $e) {
     *      // Perhaps, let the delivery department know that the order is ready to be delivered.
     * });
     * ```
     */
    const EVENT_ORDER_STATUS_CHANGE = 'orderStatusChange';

    // Public Methods
    // =========================================================================

    /**
     * @param int $id
     * @return OrderHistory|null
     */
    public function getOrderHistoryById($id)
    {
        $result = $this->_createOrderHistoryQuery()
            ->where(['id' => $id])
            ->one();

        return $result ? new OrderHistory($result) : null;
    }

    /**
     * @param int $id orderId
     * @return OrderHistory[]
     */
    public function getAllOrderHistoriesByOrderId($id): array
    {
        $rows = $this->_createOrderHistoryQuery()
            ->where(['orderId' => $id])
            ->orderBy('dateCreated desc')
            ->all();

        $histories = [];

        foreach ($rows as $row) {
            $histories[] = new OrderHistory($row);
        }

        return $histories;
    }

    /**
     * @param Order $order
     * @param int $oldStatusId
     * @return bool
     * @throws Exception
     */
    public function createOrderHistoryFromOrder(Order $order, $oldStatusId): bool
    {
        $orderHistoryModel = new OrderHistory();
        $orderHistoryModel->orderId = $order->id;
        $orderHistoryModel->prevStatusId = $oldStatusId;
        $orderHistoryModel->newStatusId = $order->orderStatusId;
        $orderHistoryModel->customerId = Craft::$app->request->isConsoleRequest ? $order->customerId : Plugin::getInstance()->getCustomers()->getCustomerId();
        $orderHistoryModel->message = $order->message;

        if (!$this->saveOrderHistory($orderHistoryModel)) {
            return false;
        }

        Plugin::getInstance()->getOrderStatuses()->statusChangeHandler($order, $orderHistoryModel);

        // Raising 'orderStatusChange' event
        if ($this->hasEventHandlers(self::EVENT_ORDER_STATUS_CHANGE)) {
            $this->trigger(self::EVENT_ORDER_STATUS_CHANGE, new OrderStatusEvent([
                'orderHistory' => $orderHistoryModel,
                'order' => $order
            ]));
        }

        return true;
    }

    /**
     * @param OrderHistory $model
     * @return bool
     * @throws Exception
     */
    public function saveOrderHistory(OrderHistory $model): bool
    {
        if ($model->id) {
            $record = OrderHistoryRecord::findOne($model->id);

            if (!$record) {
                throw new Exception(Craft::t('commerce', 'No order history exists with the ID “{id}”',
                    ['id' => $model->id]));
            }
        } else {
            $record = new OrderHistoryRecord();
        }

        $record->message = $model->message;
        $record->newStatusId = $model->newStatusId;
        $record->prevStatusId = $model->prevStatusId;
        $record->customerId = $model->customerId;
        $record->orderId = $model->orderId;

        $record->validate();
        $model->addErrors($record->getErrors());

        if (!$model->hasErrors()) {
            // Save it!
            $record->save(false);

            // Now that we have a record ID, save it on the model
            $model->id = $record->id;
            $model->dateCreated = $record->dateCreated;

            return true;
        }
        return false;
    }

    /**
     * @param $id
     * @return bool
     */
    public function deleteOrderHistoryById($id): bool
    {
        $orderHistory = OrderHistoryRecord::findOne($id);

        if ($orderHistory) {
            return (bool)$orderHistory->delete();
        }

        return false;
    }

    // Private methods
    // =========================================================================

    /**
     * Returns a Query object prepped for retrieving Transactions.
     *
     * @return Query The query object.
     */
    private function _createOrderHistoryQuery(): Query
    {
        return (new Query())
            ->select([
                'id',
                'message',
                'orderId',
                'prevStatusId',
                'newStatusId',
                'customerId',
                'dateCreated'
            ])
            ->from(['{{%commerce_orderhistories}}']);
    }
}
