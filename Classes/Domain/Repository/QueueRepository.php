<?php
namespace Fab\Messenger\Domain\Repository;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Mail\MailMessage;
use Fab\Vidi\Tca\Tca;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A repository for the Queue.
 */
class QueueRepository
{

    /**
     * @var string
     */
    protected $tableName = 'tx_messenger_domain_model_queue';

    /**
     * @param array $message
     * @return int
     * @throws \RuntimeException
     */
    public function add(array $message): int
    {
        if (!$message['mail_message'] instanceof MailMessage) {
            throw new \RuntimeException('Please, make sure key "mail_message" is a valid mail message object', 1469694987);
        }

        $values = [];
        $values['crdate'] = time(); // default values
        $values['message_serialized'] = serialize($message['mail_message']);

        // Make sure fields are allowed for this table.
        $fields = Tca::table($this->tableName)->getFields();
        foreach ($message as $fieldName => $value) {
            if (in_array($fieldName, $fields, true)) {
                $values[$fieldName] = $value;
            }
        }

        $query = $this->getQueryBuilder();
        $query->insert($this->tableName)
            ->values($values);

        $result = $query->execute();
        if (!$result) {
            throw new \RuntimeException('I could not queue the message.', 1389721932);
        }
        return $result;
    }

    /**
     * @param integer $limit
     * @return array
     */
    public function findPendingMessages(int $limit): array
    {
        $query = $this->getQueryBuilder();
        $query->select('*')
            ->from($this->tableName)
            ->where(
                'scheduled_distribution_time < ' . time()
            )
            ->setMaxResults($limit);

        $messages = $query->execute()->fetchAll();

        return is_array($messages) ? $messages : [];
    }

    /**
     * @param array $message
     * @return int
     */
    public function remove(array $message): int
    {
        $query = $this->getQueryBuilder();
        $query->delete($this->tableName)
            ->where('uid = ' . $message['uid']);

        return $query->execute();
    }

    /**
     * @param array $message
     * @return int
     */
    public function update(array $message): int
    {
        $query = $this->getQueryBuilder();
        $query->update($this->tableName)
            ->where('uid = ' . $message['uid']);

        foreach ($message as $field => $value) {
            $query->set($field, $value);
        }
        return $query->execute();
    }

    /**
     * @return object|QueryBuilder
     */
    protected function getQueryBuilder(): QueryBuilder
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        return $connectionPool->getQueryBuilderForTable($this->tableName);
    }

}
