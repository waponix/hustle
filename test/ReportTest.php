<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/task001/Report.class.php';

class ReportTest extends TestCase {
    private $pdoConnection;

    const RESULT_WITH_VALID_FORMAT = [
        [
            'orderId' => 111,
            'created' => '2024-12-25',
            'items' => [
                [
                    'description' => '20 yard dumpster',
                    'quantity' => 1,
                    'unitPrice' => 299,
                    'tax1' => 0.05,
                    'tax2' => 0.
                ],
                [
                    'description' => 'weight charge',
                    'quantity' => 1.142,
                    'unitPrice' => 99,
                    'tax1' => 0.05,
                    'tax2' => 0.
                ]
            ]
        ],
        [
            'orderId' => 115,
            'created' => '2024-12-26',
            'items' => [
                [
                    'description' => '15 yard dumpster',
                    'quantity' => 1,
                    'unitPrice' => 249,
                    'tax1' => 0.05,
                    'tax2' => 0.
                ],
                [
                    'description' => 'extra days',
                    'quantity' => 4,
                    'unitPrice' => 20,
                    'tax1' => 0.05,
                    'tax2' => 0.
                ],
                [
                    'description' => 'weight charge',
                    'quantity' => 0.905,
                    'unitPrice' => 99,
                    'tax1' => 0.05,
                    'tax2' => 0.
                ]
            ]
        ]
    ];

    protected function setUp(): void {
        $this->pdoConnection = $this->createMock(PDO::class);
        $GLOBALS['pdoConnection'] = $this->pdoConnection;
    }

    public function testGetJsonShouldReturnCorrectFormat() {
        $statementMock = $this->createMock(PDOStatement::class);
        $statementMock->method('execute')->willReturn(true);
        $statementMock->method('fetch')->willReturnOnConsecutiveCalls(
            [
                'orderId' => '111',
                'created' => '2024-12-25 00:00:00',
                'description' => '20 yard dumpster',
                'quantity' => '1.00000',
                'unitPrice' => '299.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '111',
                'created' => '2024-12-25 00:00:00',
                'description' => 'weight charge',
                'quantity' => '1.14200',
                'unitPrice' => '99.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '115',
                'created' => '2024-12-26 00:00:00',
                'description' => '15 yard dumpster',
                'quantity' => '1.00000',
                'unitPrice' => '249.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '115',
                'created' => '2024-12-26 00:00:00',
                'description' => 'extra days',
                'quantity' => '4.00000',
                'unitPrice' => '20.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '115',
                'created' => '2024-12-26 00:00:00',
                'description' => 'weight charge',
                'quantity' => '0.90500',
                'unitPrice' => '99.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            false
        );

        $this->pdoConnection->method('prepare')->willReturn($statementMock);

        $report = new Report('2024-12-25', '2024-12-26', 'Credit Card', 1);
        $result = $report->getJson();

        $this->assertEquals([], $report->getErrors());
        $this->assertEquals(self::RESULT_WITH_VALID_FORMAT, $result);
    }

    public function testGetJsonShouldReturnFalseWhenAParameterIsInvalid() {
        $statementMock = $this->createMock(PDOStatement::class);
        $statementMock->method('execute')->willReturn(true);
        $statementMock->method('fetch')->willReturnOnConsecutiveCalls(
            [
                'orderId' => '111',
                'created' => '2024-12-25 00:00:00',
                'description' => '20 yard dumpster',
                'quantity' => '1.00000',
                'unitPrice' => '299.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '111',
                'created' => '2024-12-25 00:00:00',
                'description' => 'weight charge',
                'quantity' => '1.14200',
                'unitPrice' => '99.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '115',
                'created' => '2024-12-26 00:00:00',
                'description' => '15 yard dumpster',
                'quantity' => '1.00000',
                'unitPrice' => '249.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '115',
                'created' => '2024-12-26 00:00:00',
                'description' => 'extra days',
                'quantity' => '4.00000',
                'unitPrice' => '20.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '115',
                'created' => '2024-12-26 00:00:00',
                'description' => 'weight charge',
                'quantity' => '0.90500',
                'unitPrice' => '99.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            false
        );

        $report = new Report('not_a_valid_date');
        $result = $report->getJson();
        $this->assertEquals(false, $result);

        $report = new Report(null, 'not_a_valid_date');
        $result = $report->getJson();
        $this->assertEquals(false, $result);

        $report = new Report('not_a_valid_date', 'not_a_valid_date');
        $result = $report->getJson();
        
        $this->assertEquals(false, $result);
    }

    public function testGetErrorsShouldHaveErrorWhenStartDateIsNotAValidDate() {
        $statementMock = $this->createMock(PDOStatement::class);
        $statementMock->method('execute')->willReturn(true);
        $statementMock->method('fetch')->willReturnOnConsecutiveCalls(
            [
                'orderId' => '111',
                'created' => '2024-12-25 00:00:00',
                'description' => '20 yard dumpster',
                'quantity' => '1.00000',
                'unitPrice' => '299.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '111',
                'created' => '2024-12-25 00:00:00',
                'description' => 'weight charge',
                'quantity' => '1.14200',
                'unitPrice' => '99.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '115',
                'created' => '2024-12-26 00:00:00',
                'description' => '15 yard dumpster',
                'quantity' => '1.00000',
                'unitPrice' => '249.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '115',
                'created' => '2024-12-26 00:00:00',
                'description' => 'extra days',
                'quantity' => '4.00000',
                'unitPrice' => '20.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '115',
                'created' => '2024-12-26 00:00:00',
                'description' => 'weight charge',
                'quantity' => '0.90500',
                'unitPrice' => '99.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            false
        );

        $this->pdoConnection->method('prepare')->willReturn($statementMock);

        $report = new Report('not_a_valid_date');
        $result = $report->getJson();

        $expectedErrors = [
            'startDate' => 'is not a valid date'
        ];

        $this->assertEquals($expectedErrors, $report->getErrors());
        $this->assertEquals(false, $result);
    }

    public function testGetErrorsShouldHaveErrorWhenEndDateIsNotAValidDate() {
        $statementMock = $this->createMock(PDOStatement::class);
        $statementMock->method('execute')->willReturn(true);
        $statementMock->method('fetch')->willReturnOnConsecutiveCalls(
            [
                'orderId' => '111',
                'created' => '2024-12-25 00:00:00',
                'description' => '20 yard dumpster',
                'quantity' => '1.00000',
                'unitPrice' => '299.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '111',
                'created' => '2024-12-25 00:00:00',
                'description' => 'weight charge',
                'quantity' => '1.14200',
                'unitPrice' => '99.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '115',
                'created' => '2024-12-26 00:00:00',
                'description' => '15 yard dumpster',
                'quantity' => '1.00000',
                'unitPrice' => '249.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '115',
                'created' => '2024-12-26 00:00:00',
                'description' => 'extra days',
                'quantity' => '4.00000',
                'unitPrice' => '20.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '115',
                'created' => '2024-12-26 00:00:00',
                'description' => 'weight charge',
                'quantity' => '0.90500',
                'unitPrice' => '99.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            false
        );

        $this->pdoConnection->method('prepare')->willReturn($statementMock);

        $report = new Report(null, 'not_a_valid_date');
        $result = $report->getJson();

        $expectedErrors = [
            'endDate' => 'is not a valid date'
        ];

        $this->assertEquals($expectedErrors, $report->getErrors());
        $this->assertEquals(false, $result);
    }

    public function testGetErrorsShouldHaveErrorWhenStartAndEndDateIsNotAValidDate() {
        $statementMock = $this->createMock(PDOStatement::class);
        $statementMock->method('execute')->willReturn(true);
        $statementMock->method('fetch')->willReturnOnConsecutiveCalls(
            [
                'orderId' => '111',
                'created' => '2024-12-25 00:00:00',
                'description' => '20 yard dumpster',
                'quantity' => '1.00000',
                'unitPrice' => '299.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '111',
                'created' => '2024-12-25 00:00:00',
                'description' => 'weight charge',
                'quantity' => '1.14200',
                'unitPrice' => '99.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '115',
                'created' => '2024-12-26 00:00:00',
                'description' => '15 yard dumpster',
                'quantity' => '1.00000',
                'unitPrice' => '249.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '115',
                'created' => '2024-12-26 00:00:00',
                'description' => 'extra days',
                'quantity' => '4.00000',
                'unitPrice' => '20.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '115',
                'created' => '2024-12-26 00:00:00',
                'description' => 'weight charge',
                'quantity' => '0.90500',
                'unitPrice' => '99.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            false
        );

        $report = new Report('not_a_valid_date', 'not_a_valid_date');
        $result = $report->getJson();

        $expectedErrors = [
            'startDate' => 'is not a valid date',
            'endDate' => 'is not a valid date'
        ];

        $this->assertEquals($expectedErrors, $report->getErrors());
        $this->assertEquals(false, $result);
    }

    public function testGetJsonShouldReturnEmptyArrayWhenNoDataMatches() {
        $statementMock = $this->createMock(PDOStatement::class);
        $statementMock->method('execute')->willReturn(true);
        $statementMock->method('fetch')->willReturn(false);

        $this->pdoConnection->method('prepare')->willReturn($statementMock);

        $report = new Report('2024-12-25', '2024-12-26', 'Credit Card', 1);
        $result = $report->getJson();

        $this->assertEquals([], $result);
    }

    public function testGetJsonShouldReturnDataWhenOnlyStartDateIsProvided() {
        $statementMock = $this->createMock(PDOStatement::class);
        $statementMock->method('execute')->willReturn(true);
        $statementMock->method('fetch')->willReturnOnConsecutiveCalls(
            [
                'orderId' => '111',
                'created' => '2024-12-25 00:00:00',
                'description' => '20 yard dumpster',
                'quantity' => '1.00000',
                'unitPrice' => '299.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            false
        );

        $this->pdoConnection->method('prepare')->willReturn($statementMock);

        $report = new Report('2024-12-25');
        $result = $report->getJson();

        $expectedResult = [
            [
                'orderId' => 111,
                'created' => '2024-12-25',
                'items' => [
                    [
                        'description' => '20 yard dumpster',
                        'quantity' => 1.0,
                        'unitPrice' => 299.0,
                        'tax1' => 0.05,
                        'tax2' => 0.0,
                    ]
                ]
            ]
        ];

        $this->assertEquals($expectedResult, $result);
    }

    public function testGetJsonShouldReturnDataWhenOnlyEndDateIsProvided() {
        $statementMock = $this->createMock(PDOStatement::class);
        $statementMock->method('execute')->willReturn(true);
        $statementMock->method('fetch')->willReturnOnConsecutiveCalls(
            [
                'orderId' => '111',
                'created' => '2024-12-25 00:00:00',
                'description' => '20 yard dumpster',
                'quantity' => '1.00000',
                'unitPrice' => '299.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            false
        );

        $this->pdoConnection->method('prepare')->willReturn($statementMock);

        $report = new Report(null, '2024-12-25');
        $result = $report->getJson();

        $expectedResult = [
            [
                'orderId' => 111,
                'created' => '2024-12-25',
                'items' => [
                    [
                        'description' => '20 yard dumpster',
                        'quantity' => 1.0,
                        'unitPrice' => 299.0,
                        'tax1' => 0.05,
                        'tax2' => 0.0,
                    ]
                ]
            ]
        ];

        $this->assertEquals($expectedResult, $result);
    }

    public function testGetJsonShouldReturnDataWhenOnlyPaymentMethodIsProvided() {
        $statementMock = $this->createMock(PDOStatement::class);
        $statementMock->method('execute')->willReturn(true);
        $statementMock->method('fetch')->willReturnOnConsecutiveCalls(
            [
                'orderId' => '111',
                'created' => '2024-12-25 00:00:00',
                'description' => '20 yard dumpster',
                'quantity' => '1.00000',
                'unitPrice' => '299.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            false
        );

        $this->pdoConnection->method('prepare')->willReturn($statementMock);

        $report = new Report(null, null, 'Credit Card');
        $result = $report->getJson();

        $expectedResult = [
            [
                'orderId' => 111,
                'created' => '2024-12-25',
                'items' => [
                    [
                        'description' => '20 yard dumpster',
                        'quantity' => 1.0,
                        'unitPrice' => 299.0,
                        'tax1' => 0.05,
                        'tax2' => 0.0,
                    ]
                ]
            ]
        ];

        $this->assertEquals($expectedResult, $result);
    }

    public function testGetJsonShouldReturnDataWhenOnlyServiceIdIsProvided() {
        $statementMock = $this->createMock(PDOStatement::class);
        $statementMock->method('execute')->willReturn(true);
        $statementMock->method('fetch')->willReturnOnConsecutiveCalls(
            [
                'orderId' => '111',
                'created' => '2024-12-25 00:00:00',
                'description' => '20 yard dumpster',
                'quantity' => '1.00000',
                'unitPrice' => '299.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            false
        );

        $this->pdoConnection->method('prepare')->willReturn($statementMock);

        $report = new Report(null, null, null, 1);
        $result = $report->getJson();

        $expectedResult = [
            [
                'orderId' => 111,
                'created' => '2024-12-25',
                'items' => [
                    [
                        'description' => '20 yard dumpster',
                        'quantity' => 1.0,
                        'unitPrice' => 299.0,
                        'tax1' => 0.05,
                        'tax2' => 0.0,
                    ]
                ]
            ]
        ];

        $this->assertEquals($expectedResult, $result);
    }
}
