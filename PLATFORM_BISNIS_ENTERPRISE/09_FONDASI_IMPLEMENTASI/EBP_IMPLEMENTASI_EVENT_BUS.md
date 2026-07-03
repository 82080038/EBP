# Enterprise Business Platform (EBP)

# Event Bus Implementation

**Document ID:** EBP-IMPLEMENTATION-FOUNDATION-EVENTBUS-001
**Version:** 1.0
**Category:** Implementation Foundation
**Status:** Official Event Architecture Standard

---

# 1. Introduction

Event Bus adalah infrastruktur komunikasi internal EBP yang memungkinkan antar modul dan engine berkomunikasi secara asynchronous tanpa ketergantungan langsung.

Event Bus menjadi penghubung antara:

* Business Module;
* Shared Engine;
* Enterprise Control Layer.

---

# 2. Event Bus Philosophy

EBP menggunakan prinsip:

> Components communicate through events, not direct dependency.

Artinya:

Modul tidak perlu mengetahui siapa yang menggunakan datanya.

---

# 3. Problem Without Event Bus

Contoh tanpa Event Bus:

```text
Order Service


 |

 +---- Inventory Service

 |

 +---- Accounting Service

 |

 +---- Notification Service

 |

 +---- Reporting Service

```

Masalah:

* coupling tinggi;
* sulit testing;
* sulit menambah fitur;
* perubahan satu modul berdampak besar.

---

# 4. Event Driven Architecture

Dengan Event Bus:

```text
                Order Service


                     |

                     v


                 EVENT BUS


        +------------+-------------+

        |            |             |

        v            v             v


 Inventory     Accounting     Notification

 Engine        Engine         Engine


```

---

# 5. Event Bus Responsibilities

Event Bus bertugas:

* menerima event;
* menyimpan event;
* mendistribusikan event;
* retry jika gagal;
* mencatat history.

---

# 6. Event Components

Struktur:

```text
Event Bus


├── Event Dispatcher

├── Event Queue

├── Event Listener

├── Event Handler

├── Retry Manager

├── Dead Letter Queue

└── Event Logger

```

---

# 7. Event Definition

Event adalah fakta yang sudah terjadi.

Contoh:

```text
ORDER_CREATED

PAYMENT_COMPLETED

STOCK_UPDATED

USER_REGISTERED

INVOICE_GENERATED

```

---

# 8. Event Naming Convention

Format:

```text
ENTITY_ACTION_STATUS
```

Contoh:

Benar:

```text
ORDER_CREATED

PAYMENT_FAILED

INVOICE_SENT

```

Salah:

```text
createOrder

sendInvoiceNow

```

---

# 9. Event Structure

Semua event memiliki format:

```json
{
"event_id":"uuid",
"event_name":"ORDER_CREATED",
"tenant_id":100,
"aggregate_id":5001,
"timestamp":"2026-01-01",
"payload":{}
}

```

---

# 10. Event Database

Core database:

```text
event_management/


├── events

├── event_handlers

├── event_logs

├── failed_events

```

---

# 11. Events Table

```sql
CREATE TABLE events (

id BIGINT AUTO_INCREMENT PRIMARY KEY,

event_id VARCHAR(100),

tenant_id BIGINT,

event_name VARCHAR(100),

aggregate_type VARCHAR(100),

aggregate_id BIGINT,

payload JSON,

status VARCHAR(20),

created_at DATETIME

);

```

---

# 12. Event Status

Status:

```text
PENDING

PROCESSING

COMPLETED

FAILED

RETRY

```

---

# 13. Event Publisher

Publisher membuat event.

Contoh:

```php
class OrderService{


public function create($data){


$order=$this->save($data);


EventBus::publish(

new OrderCreated($order)

);


}


}

```

---

# 14. Event Dispatcher

Dispatcher membaca event:

```text
Event Queue


        |

        v


Dispatcher


        |

        v


Listener

```

---

# 15. Event Listener

Listener menerima event.

Contoh:

```php
class InventoryListener{


public function handle(
OrderCreated $event
){


InventoryService::reduceStock();


}

}

```

---

# 16. Event Handler Registration

Contoh:

```php
EventBus::listen(

"ORDER_CREATED",

InventoryListener::class

);

```

---

# 17. Event Flow Example

Restaurant ERP:

```text
Customer Order


        |

        v


ORDER_CREATED


        |

        v


EVENT BUS


        |

+-------+-------+-------+

|       |       |       |

v       v       v       v


Kitchen Inventory Accounting Notification

```

---

# 18. Asynchronous Processing

Event tidak harus langsung selesai.

Contoh:

```text
Order Completed


        |

        v


Queue


        |

        +---- Update Report

        |

        +---- AI Analysis

        |

        +---- Send Notification

```

---

# 19. Queue Implementation

Development:

```text
Database Queue

```

Production:

```text
Redis Queue

RabbitMQ

Kafka

```

---

# 20. Retry Mechanism

Jika gagal:

```text
Event Failed


        |

        v


Retry 1


        |

        v


Retry 2


        |

        v


Retry 3


        |

        v


Dead Letter Queue

```

---

# 21. Dead Letter Queue

Event yang gagal permanen:

```text
failed_events

```

Digunakan untuk:

* investigasi;
* recovery;
* audit.

---

# 22. Event Idempotency

Event harus aman diproses ulang.

Contoh:

Jika:

```text
PAYMENT_COMPLETED

```

diproses dua kali:

Tidak boleh:

```text
Saldo bertambah dua kali

```

---

# 23. Idempotency Key

Disimpan:

```text
event_id

transaction_id

reference_number

```

---

# 24. Transaction Event Pattern

EBP menggunakan:

```text
Database Transaction


        +


Publish Event


        +


Process Event

```

---

# 25. Event Security

Setiap event memiliki:

* tenant_id;
* source;
* timestamp;
* signature.

---

# 26. Event Audit

Dicatat:

```text
Who

When

Source

Payload

Result

```

---

# 27. Event Logging Table

```sql
event_logs


id

event_id

handler

status

message

created_at

```

---

# 28. Integration With Workflow Engine

Contoh:

```text
ORDER_CREATED


        |

        v


Workflow Engine


        |

        v


Approval Process

```

---

# 29. Integration With Rule Engine

Contoh:

```text
PAYMENT_RECEIVED


        |

        v


Rule Engine


        |

        v


Apply Discount

```

---

# 30. Integration With AI Engine

Contoh:

```text
SALES_COMPLETED


        |

        v


AI Engine


        |

        v


Update Prediction Model

```

---

# 31. Integration With Reporting Engine

Contoh:

```text
TRANSACTION_COMPLETED


        |

        v


Reporting Engine


        |

        v


Update Dashboard

```

---

# 32. Testing Strategy

## Unit Test

Menguji:

* publisher;
* listener;
* handler.

---

## Integration Test

Contoh:

```text
Order Created


↓

Inventory Updated


↓

Accounting Updated

```

---

## Failure Test

Menguji:

* retry;
* duplicate event;
* failed handler.

---

# 33. Development Rules

Tidak boleh:

```text
Direct call antar engine

Business logic dalam event

Event tanpa audit

Event tanpa version

```

---

# 34. Event Versioning

Jika payload berubah:

Gunakan:

```text
ORDER_CREATED.v1

ORDER_CREATED.v2

```

---

# 35. Example Restaurant ERP Events

```text
MENU_CREATED

ORDER_CREATED

ORDER_PAID

KITCHEN_STARTED

KITCHEN_COMPLETED

STOCK_LOW

PURCHASE_CREATED

INVOICE_CREATED

```

---

# 36. Future Evolution

Event Bus dapat berkembang menjadi:

```text
Internal Event Bus


        ↓


Enterprise Message Platform


        ↓


Digital Business Event Mesh

```

---

# 37. Final Vision

Event Bus membuat EBP menjadi:

```text
Modular Application


        ↓


Enterprise Platform


        ↓


Autonomous Business Operating System

```

---

# END OF DOCUMENT

Document ID:

EBP-IMPLEMENTATION-FOUNDATION-EVENTBUS-001

Version:

1.0
