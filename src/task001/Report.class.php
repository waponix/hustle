<?php
require_once __DIR__ . '/database.php';

class Report {
    // Constants for controlling date formats
    const INPUT_DATE_FORMAT = 'Y-m-d';
    const OUTPUT_DATE_FORMAT = 'Y-m-d';

    // Constants for controlling error message
    const MSG_INVALID_DATE = 'is not a valid date';

    // Contains the query parameter values
    private array $parameters = [];
    // Contains the raw values fetched from the executed query
    private array $raw = [];

    private ?PDOStatement $query = null;
    private array $errors = [];

    // Input parameters for the report
    private ?string $startDate = null;
    private ?string $endDate = null;
    private ?string $paymentMethod = null;
    private ?int $serviceId = null;

    // Constructor to initialize the report with optional parameters.
    public function __construct(
        string $startDate = null,
        string $endDate = null,
        string $paymentMethod = null,
        int $serviceId = null
    ) {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->paymentMethod = $paymentMethod;
        $this->serviceId = $serviceId;
    }

    // Returns the recorded errors encountered during input validation
    public function getErrors() 
    {
        return $this->errors;
    }

    // Returns the final form of the report data or false when validation fails
    public function getJson() 
    {
        if ($this->validateInputs() === false) {
            return false;
        }

        return $this
            ->buildQuery()
            ->executeQuery()
            ->getformattedQueryResult();
    }

    private function validateInputs() 
    {
        do {
            if ($this->startDate === null) {
                break;
            }

            // used createFromFormat here since date inputs are expected to be in Y-m-d format
            $startDate = DateTimeImmutable::createFromFormat(self::INPUT_DATE_FORMAT, $this->startDate);

            if ($startDate === false) {
                $this->errors['startDate'] = self::MSG_INVALID_DATE;
            }
        } while (false);

        do {
            if ($this->endDate === null) {
                break;
            }

            $endDate = DateTimeImmutable::createFromFormat(self::INPUT_DATE_FORMAT, $this->endDate);

            if ($endDate === false) {
                $this->errors['endDate'] = self::MSG_INVALID_DATE;
            }
        } while (false);

        // return false when an error is recorded
        return count($this->errors) <= 0;
    }

    private function executeQuery()
    {
        try {
            $this->query->execute();
            while ($row = $this->query->fetch(PDO::FETCH_ASSOC)) {
                $this->raw[] = $row;
            }
        } catch (PDOException $queryException) {
            // TODO: process or log the error encountered by pdo
        } catch (Throwable $exception) {
            // TODO: process or log the encountered error
        }

        return $this;
    }

    private function getformattedQueryResult()
    {
        if (count($this->raw) <= 0) {
            return [];
        }

        $data = [];

        foreach ($this->raw as $row) {
            $orderId = (int) $row['orderId'];
            if (!isset($data[$orderId])) {
                // properly format the creation date
                $createdDate = DateTimeImmutable::createFromFormat(self::OUTPUT_DATE_FORMAT . ' H:i:s', $row['created']);
                $createdDate = $createdDate->format(self::OUTPUT_DATE_FORMAT);

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

    private function buildQuery()
    {
        // define filters (query conditions)
        $filters = [
            'startDate'     => 'o.created >= :startDate',
            'endDate'       => 'o.created <= :endDate',
            'paymentMethod' => 'oi.paymentMethod = :paymentMethod',
            'serviceId'     => 'o.serviceId = :serviceId',
        ];

        // provide value to the parameters, this will dictate when a filter should be applied to the query
        if ($this->startDate !== null) {
            $this->setParameter('startDate', PDO::PARAM_STR, $this->startDate . ' 00:00:00');
        }

        if ($this->endDate !== null) {
            $this->setParameter('endDate', PDO::PARAM_STR, $this->endDate . ' 23:59:59');
        }

        if ($this->paymentMethod !== null) {
            $this->setParameter('paymentMethod', PDO::PARAM_STR, $this->paymentMethod);
        }

        if ($this->serviceId !== null) {
            $this->setParameter('serviceId', PDO::PARAM_INT, $this->serviceId);
        }

        // build the query conditions
        $where = [];

        // apply filters when it matches a parameter
        foreach ($filters as $name => $filter) {
            if (!key_exists(":$name", $this->parameters)) {
                continue;
            }

            $where[] = $filter;
        }

        if (count($where) > 0) {
            $where = 'WHERE ' . implode(' AND ', $where);
        } else {
            $where = '';
        }

        $sql = <<<SQL
            SELECT o.id AS orderId, o.created, oi.description, oi.quantity, oi.unitPrice, oi.tax1, oi.tax2 
            FROM orders AS o
            INNER JOIN orderItems AS oi ON o.id = oi.orderId
            %s
            ORDER BY o.created ASC
        SQL;

        global $pdoConnection;

        $this->query = $pdoConnection->prepare(sprintf($sql, $where));
        
        if (!empty($this->parameters)) {
            foreach ($this->parameters as $name => $parameter) {
                $this->query->bindValue($name, $parameter['value'], $parameter['type']);
            }
        }

        return $this;
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
}