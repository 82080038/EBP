<?php

class AccountSuggestionService
{
    private $db;

    public function __construct()
    {
        $host = 'localhost';
        $dbname = 'ebp_restaurant_db';
        $username = 'ebp_app';
        $password = 'ebp_secure_password_2026';
        $socket = '/opt/lampp/var/mysql/mysql.sock';

        $dsn = "mysql:host=$host;dbname=$dbname;unix_socket=$socket;charset=utf8mb4";
        $this->db = new PDO($dsn, $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Rule-based account suggestions based on transaction type
    public function suggestAccounts($transactionType, $description = null, $amount = null)
    {
        $suggestions = [];

        switch ($transactionType) {
            case 'SALES':
                $suggestions = [
                    'debit' => [
                        'account_type' => 'ASSET',
                        'account_name' => 'Cash',
                        'account_code' => '1000'
                    ],
                    'credit' => [
                        'account_type' => 'REVENUE',
                        'account_name' => 'Sales Revenue',
                        'account_code' => '4000'
                    ]
                ];
                break;

            case 'PURCHASE':
                $suggestions = [
                    'debit' => [
                        'account_type' => 'ASSET',
                        'account_name' => 'Inventory',
                        'account_code' => '1200'
                    ],
                    'credit' => [
                        'account_type' => 'LIABILITY',
                        'account_name' => 'Accounts Payable',
                        'account_code' => '2000'
                    ]
                ];
                break;

            case 'PAYMENT_RECEIVED':
                $suggestions = [
                    'debit' => [
                        'account_type' => 'ASSET',
                        'account_name' => 'Cash',
                        'account_code' => '1000'
                    ],
                    'credit' => [
                        'account_type' => 'ASSET',
                        'account_name' => 'Accounts Receivable',
                        'account_code' => '1100'
                    ]
                ];
                break;

            case 'PAYMENT_MADE':
                $suggestions = [
                    'debit' => [
                        'account_type' => 'LIABILITY',
                        'account_name' => 'Accounts Payable',
                        'account_code' => '2000'
                    ],
                    'credit' => [
                        'account_type' => 'ASSET',
                        'account_name' => 'Cash',
                        'account_code' => '1000'
                    ]
                ];
                break;

            case 'EXPENSE':
                $suggestions = [
                    'debit' => [
                        'account_type' => 'EXPENSE',
                        'account_name' => 'Operating Expenses',
                        'account_code' => '6000'
                    ],
                    'credit' => [
                        'account_type' => 'ASSET',
                        'account_name' => 'Cash',
                        'account_code' => '1000'
                    ]
                ];
                break;

            case 'SALARY':
                $suggestions = [
                    'debit' => [
                        'account_type' => 'EXPENSE',
                        'account_name' => 'Salary Expense',
                        'account_code' => '6100'
                    ],
                    'credit' => [
                        'account_type' => 'LIABILITY',
                        'account_name' => 'Salaries Payable',
                        'account_code' => '2100'
                    ]
                ];
                break;

            case 'RENT':
                $suggestions = [
                    'debit' => [
                        'account_type' => 'EXPENSE',
                        'account_name' => 'Rent Expense',
                        'account_code' => '6200'
                    ],
                    'credit' => [
                        'account_type' => 'ASSET',
                        'account_name' => 'Cash',
                        'account_code' => '1000'
                    ]
                ];
                break;

            case 'UTILITY':
                $suggestions = [
                    'debit' => [
                        'account_type' => 'EXPENSE',
                        'account_name' => 'Utility Expense',
                        'account_code' => '6300'
                    ],
                    'credit' => [
                        'account_type' => 'ASSET',
                        'account_name' => 'Cash',
                        'account_code' => '1000'
                    ]
                ];
                break;

            default:
                // Try to infer from description
                if ($description) {
                    $suggestions = $this->inferFromDescription($description);
                } else {
                    $suggestions = [
                        'debit' => null,
                        'credit' => null
                    ];
                }
        }

        return [
            'success' => true,
            'suggestions' => $suggestions,
            'message' => 'Account suggestions retrieved successfully'
        ];
    }

    // Infer accounts from description using keyword matching
    private function inferFromDescription($description)
    {
        $description = strtolower($description);
        $suggestions = [
            'debit' => null,
            'credit' => null
        ];

        // Keywords for different account types
        $keywords = [
            'cash' => ['cash', 'payment', 'paid', 'received'],
            'revenue' => ['sales', 'revenue', 'income', 'earned'],
            'expense' => ['expense', 'cost', 'spent', 'purchase'],
            'inventory' => ['inventory', 'stock', 'goods'],
            'receivable' => ['receivable', 'ar', 'credit sale'],
            'payable' => ['payable', 'ap', 'credit purchase'],
            'salary' => ['salary', 'wage', 'payroll'],
            'rent' => ['rent', 'lease'],
            'utility' => ['utility', 'electricity', 'water', 'gas']
        ];

        foreach ($keywords as $accountType => $keywordList) {
            foreach ($keywordList as $keyword) {
                if (strpos($description, $keyword) !== false) {
                    $suggestions = $this->getAccountByType($accountType);
                    break 2;
                }
            }
        }

        return $suggestions;
    }

    private function getAccountByType($accountType)
    {
        $sql = "SELECT account_id, account_code, account_name, account_type 
                FROM chart_of_accounts 
                WHERE account_type = ? 
                AND is_active = TRUE 
                AND deleted_at IS NULL 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$accountType]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($account) {
            return [
                'account_id' => $account['account_id'],
                'account_code' => $account['account_code'],
                'account_name' => $account['account_name'],
                'account_type' => $account['account_type']
            ];
        }

        return null;
    }

    // Get all accounts for autocomplete
    public function searchAccounts($tenantId, $searchTerm = null, $accountType = null)
    {
        $sql = "SELECT account_id, account_code, account_name, account_type 
                FROM chart_of_accounts 
                WHERE tenant_id = ? 
                AND is_active = TRUE 
                AND deleted_at IS NULL";
        
        $params = [$tenantId];

        if ($searchTerm) {
            $sql .= " AND (account_code LIKE ? OR account_name LIKE ?)";
            $searchParam = "%{$searchTerm}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if ($accountType) {
            $sql .= " AND account_type = ?";
            $params[] = $accountType;
        }

        $sql .= " ORDER BY account_code LIMIT 50";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get journal entry templates
    public function getJournalTemplates()
    {
        return [
            'SALES' => [
                'name' => 'Sales Transaction',
                'description' => 'Record a sales transaction',
                'debit_account' => 'Cash',
                'credit_account' => 'Sales Revenue'
            ],
            'PURCHASE' => [
                'name' => 'Purchase Transaction',
                'description' => 'Record a purchase transaction',
                'debit_account' => 'Inventory',
                'credit_account' => 'Accounts Payable'
            ],
            'PAYMENT_RECEIVED' => [
                'name' => 'Payment Received',
                'description' => 'Record payment from customer',
                'debit_account' => 'Cash',
                'credit_account' => 'Accounts Receivable'
            ],
            'PAYMENT_MADE' => [
                'name' => 'Payment Made',
                'description' => 'Record payment to supplier',
                'debit_account' => 'Accounts Payable',
                'credit_account' => 'Cash'
            ],
            'EXPENSE' => [
                'name' => 'Expense Payment',
                'description' => 'Record expense payment',
                'debit_account' => 'Operating Expenses',
                'credit_account' => 'Cash'
            ]
        ];
    }
}
