<?php
require_once __DIR__ . '/database.php';

class Report {
    const INPUT_DATE_FORMAT = 'Y-m-d H:i:s';
    const DATE_FORMAT = 'Y-m-d';
    const INVALID_DATE_VALUE = null; // update this value to your requirement

    private array $parameters = [];
    private array $raw = [];

    private array $errors = [];

    public function __construct( // fixed method name
        string $startDate = null,
        string $endDate = null,
        string $paymentMethod = null,
        int $serviceId = null
    ) {
        if (!empty($startDate) && strtotime($startDate) !== false) {
            // this assumes that the provided start date value includes time
            $startDate = new \DateTimeImmutable($startDate);
        } else if ($startDate !== null && strtotime($startDate) === false) {
            $startDate = null;
            $this->errors['startDate'] = 'is not a valid date';
        }

        if (!empty($endDate) && strtotime($endDate) !== false) {
            // this assumes that the provided end date value includes time
            $endDate = new \DateTimeImmutable($endDate);
        } else if ($endDate !== null && strtotime($endDate) === false) {
            $endDate = null;
            $this->errors['endDate'] = 'is not a valid date';
        }

        // stop building the query and do not proceed with execution
        if (count($this->errors) > 0) {
            return;
        }

        global $pdoConnection;

        $sql = <<<TEXT
            SELECT o.id AS orderId, o.created, oi.description, oi.quantity, oi.unitPrice, oi.tax1, oi.tax2 
            FROM orders AS o
            INNER JOIN orderItems AS oi ON o.id = oi.orderId
            %s
            ORDER BY o.created ASC
        TEXT;

        // build the query conditions
        $where = [];

        if (!empty($serviceId)) {
            $where[] = 'o.serviceId = :serviceId';
            $this->setParameter('serviceId', PDO::PARAM_INT, $serviceId);
        }

        if (!empty($startDate) && !empty($endDate)) {
            $where[] = '(o.created BETWEEN :startDate AND :endDate)';
            $this
                ->setParameter('startDate', PDO::PARAM_STR, $startDate->format(self::INPUT_DATE_FORMAT))
                ->setParameter('endDate', PDO::PARAM_STR, $endDate->format(self::INPUT_DATE_FORMAT));
        } else if (!empty($startDate)) {
            $where[] = 'o.created >= :startDate';
            $this->setParameter('startDate', PDO::PARAM_STR, $startDate->format(self::INPUT_DATE_FORMAT));
        } else if (!empty($endDate)) {
            $where[] = 'o.created <= :endDate';
            $this->setParameter('endDate', PDO::PARAM_STR, $endDate->format(self::INPUT_DATE_FORMAT));
        }

        if (!empty($paymentMethod)) {
            $where[] = 'oi.paymentMethod = :paymentMethod';
            $this->setParameter('paymentMethod', PDO::PARAM_STR, $paymentMethod);
        }

        if (count($where) > 0) {
            $where = "\n WHERE " . implode(' AND ', $where);
        } else {
            $where = '';
        }

        $statement = $pdoConnection->prepare(sprintf($sql, $where));
        
        if (!empty($this->parameters)) {
            foreach ($this->parameters as $name => $parameter) {
                $statement->bindValue($name, $parameter['value'], $parameter['type']);
            }
        }

        try {
            $statement->execute();
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $this->raw[] = $row;
            }
        } catch (PDOException $queryException) {
            // TODO: do something with the error encountered by pdo
        } catch (Throwable $exception) {
            // TODO: do something encountered error
        }
    }

    private function setParameter(string $name, int $type, $value)
    {
        $name = trim(str_replace(':', '', $name));
        $this->parameters[':' . $name] = [
            'value' => $value,
            'type' => $type,
        ];
        return $this;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function getJson() {
        if (count($this->raw) <= 0) {
            return [];
        }

        $data = [];

        foreach ($this->raw as $row) {
            $orderId = (int) $row['orderId'];
            if (!isset($data[$orderId])) {
                // properly format the creation date
                try {
                    $createdDate = new DateTimeImmutable($row['created']);
                    $createdDate = $createdDate->format(self::DATE_FORMAT);
                } catch (Throwable $exception) {
                    $createdDate = self::INVALID_DATE_VALUE;
                }

                // define the initial values for each order
                $data[$orderId] = [
                    'orderId'   => $orderId,
                    'created'   => $createdDate,
                    'items'     => [],
                ];
            }

            // stack the order items
            $data[$orderId]['items'][] = [
                'description'   => $row['description'],
                'quantity'      => (float) $row['quantity'],
                'unitPrice'     => (float)  $row['unitPrice'],
                'tax1'          => (float) $row['tax1'],
                'tax2'          => (float) $row['tax2'],
            ];
        }

        return array_values($data);
    }
}