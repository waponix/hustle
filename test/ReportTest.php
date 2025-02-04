<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/task001/Report.class.php';

class ReportTest extends TestCase {
    private $pdoConnection;

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
                'created' => '2024-12-25',
                'description' => '20 yard dumpster',
                'quantity' => '1.00000',
                'unitPrice' => '299.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '111',
                'created' => '2024-12-25',
                'description' => 'weight charge',
                'quantity' => '1.14200',
                'unitPrice' => '99.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '115',
                'created' => '2024-12-26',
                'description' => '15 yard dumpster',
                'quantity' => '1.00000',
                'unitPrice' => '249.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '115',
                'created' => '2024-12-26',
                'description' => 'extra days',
                'quantity' => '4.00000',
                'unitPrice' => '20.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '115',
                'created' => '2024-12-26',
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

        $expected = [
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

        $this->assertEquals([], $report->getErrors());
        $this->assertEquals($expected, $result);
    }

    public function testGetJsonShouldHandleInvalidCreatedValueFromDatabase() {
        $statementMock = $this->createMock(PDOStatement::class);
        $statementMock->method('execute')->willReturn(true);
        $statementMock->method('fetch')->willReturnOnConsecutiveCalls(
            [
                'orderId' => '111',
                'created' => 'not_a_valid_date',
                'description' => '20 yard dumpster',
                'quantity' => '1.00000',
                'unitPrice' => '299.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '111',
                'created' => 'not_a_valid_date',
                'description' => 'weight charge',
                'quantity' => '1.14200',
                'unitPrice' => '99.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '115',
                'created' => '2024-12-26',
                'description' => '15 yard dumpster',
                'quantity' => '1.00000',
                'unitPrice' => '249.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '115',
                'created' => '2024-12-26',
                'description' => 'extra days',
                'quantity' => '4.00000',
                'unitPrice' => '20.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '115',
                'created' => '2024-12-26',
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

        $expected = [
            [
                'orderId' => 111,
                'created' => Report::INVALID_DATE_VALUE,
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

        $this->assertEquals([], $report->getErrors());
        $this->assertEquals($expected, $result);
    }

    public function testGetErrorsShouldHaveErrorWhenStartDateIsNotAValidDate() {
        $statementMock = $this->createMock(PDOStatement::class);
        $statementMock->method('execute')->willReturn(true);
        $statementMock->method('fetch')->willReturnOnConsecutiveCalls(
            [
                'orderId' => '111',
                'created' => '2024-12-25',
                'description' => '20 yard dumpster',
                'quantity' => '1.00000',
                'unitPrice' => '299.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '111',
                'created' => '2024-12-25',
                'description' => 'weight charge',
                'quantity' => '1.14200',
                'unitPrice' => '99.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '115',
                'created' => '2024-12-26',
                'description' => '15 yard dumpster',
                'quantity' => '1.00000',
                'unitPrice' => '249.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '115',
                'created' => '2024-12-26',
                'description' => 'extra days',
                'quantity' => '4.00000',
                'unitPrice' => '20.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '115',
                'created' => '2024-12-26',
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
        $this->assertEquals([], $result);
    }

    public function testGetErrorsShouldHaveErrorWhenEndDateIsNotAValidDate() {
        $statementMock = $this->createMock(PDOStatement::class);
        $statementMock->method('execute')->willReturn(true);
        $statementMock->method('fetch')->willReturnOnConsecutiveCalls(
            [
                'orderId' => '111',
                'created' => '2024-12-25',
                'description' => '20 yard dumpster',
                'quantity' => '1.00000',
                'unitPrice' => '299.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '111',
                'created' => '2024-12-25',
                'description' => 'weight charge',
                'quantity' => '1.14200',
                'unitPrice' => '99.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '115',
                'created' => '2024-12-26',
                'description' => '15 yard dumpster',
                'quantity' => '1.00000',
                'unitPrice' => '249.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '115',
                'created' => '2024-12-26',
                'description' => 'extra days',
                'quantity' => '4.00000',
                'unitPrice' => '20.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '115',
                'created' => '2024-12-26',
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
        $this->assertEquals([], $result);
    }

    public function testGetErrorsShouldHaveErrorWhenStartAndEndDateIsNotAValidDate() {
        $statementMock = $this->createMock(PDOStatement::class);
        $statementMock->method('execute')->willReturn(true);
        $statementMock->method('fetch')->willReturnOnConsecutiveCalls(
            [
                'orderId' => '111',
                'created' => '2024-12-25',
                'description' => '20 yard dumpster',
                'quantity' => '1.00000',
                'unitPrice' => '299.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '111',
                'created' => '2024-12-25',
                'description' => 'weight charge',
                'quantity' => '1.14200',
                'unitPrice' => '99.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '115',
                'created' => '2024-12-26',
                'description' => '15 yard dumpster',
                'quantity' => '1.00000',
                'unitPrice' => '249.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '115',
                'created' => '2024-12-26',
                'description' => 'extra days',
                'quantity' => '4.00000',
                'unitPrice' => '20.00000',
                'tax1' => '0.05000',
                'tax2' => '0.00000',
            ],
            [
                'orderId' => '115',
                'created' => '2024-12-26',
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
        $this->assertEquals([], $result);
    }
}
