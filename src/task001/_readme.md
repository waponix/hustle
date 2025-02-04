I’m not going to add you to the repo yet, i’ll just share a few PHP files with you. Things will be a bit messier for this task but i’m ok with that for now.

If you dont have a codesandbox account, thats fine, you dont have to edit files in CodeSandBox… you can just send me the files directly, or send me a link to wherever you edit the files.

This app doesn't have to run; it probably wont.

The first task is to create an API endpoint for running a report.
The front-end will POST these props (which are all optional) `startDate`, `endDate`, `paymentMethod`, `serviceId` to `/api-v2-report.php`.

The API query the database to fetch that data, and should return JSON data structured like this:

```json
[
  { "orderId": 111, "created": "2024-12-25", "items": [ { "description": "20 yard dumpster", "quantity": 1, "unitPrice": 299, "tax1": 0.05, "tax2": 0.00 }, { "description": "weight charge", "quantity": 1.142, "unitPrice": 99, "tax1": 0.05, "tax2": 0.00 } ] },
  { "orderId": 115, "created": "2024-12-26", "items": [ { "description": "15 yard dumpster", "quantity": 1, "unitPrice": 249, "tax1": 0.05, "tax2": 0.00 }, { "description": "extra days", "quantity": 4, "unitPrice": 20, "tax1": 0.05, "tax2": 0.00 }, { "description": "weight charge", "quantity": 0.905, "unitPrice": 99, "tax1": 0.05, "tax2": 0.00 } ] }
  ...
]
```

The tables you need to know are:

```sql
CREATE TABLE `users` (
  `id` int NOT NULL,
  `domain` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `orders` (
  `id` int NOT NULL,
  `userId` int NOT NULL,
  `customerId` int DEFAULT NULL,
  `serviceId` int NOT NULL,
  `deliveryStreet1` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deliveryCity` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deliveryPostalCode` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deliveryProvince` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deliveryCountry` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created` datetime DEFAULT NULL COMMENT 'EST',
  `createdByStaffId` int DEFAULT NULL,
  `deleted` datetime DEFAULT NULL,
  `notes` mediumtext COLLATE utf8mb4_unicode_ci,
  `poNumber` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `orderItems` (
  `id` int NOT NULL,
  `orderId` int NOT NULL,
  `description` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ie: service, weight, extension, failed drop',
  `quantity` decimal(8,4) DEFAULT NULL,
  `unitPrice` decimal(7,2) DEFAULT NULL,
  `tax1` decimal(6,5) NOT NULL,
  `tax2` decimal(6,5) NOT NULL,
  `created` datetime DEFAULT NULL,
  `paymentMethod` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `paymentConfirmationId` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'from stripe',
  `refundConfirmationId` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qbInvoiceId` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qbPaymentId` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qbRefundId` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


```
